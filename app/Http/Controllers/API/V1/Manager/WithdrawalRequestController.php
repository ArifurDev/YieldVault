<?php

namespace App\Http\Controllers\Api\V1\Manager;

use App\Enum\Wallet\TransactionSource;
use App\Enum\Wallet\TransactionStatus;
use App\Enum\Wallet\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Manager\Withdrawal\getWithdrawalRequest;
use App\Http\Requests\Api\V1\Manager\Withdrawal\RejectRequest;
use App\Http\Resources\Api\V1\Manager\Withdrawal\getRequestDetailsResource;
use App\Http\Resources\Api\V1\Manager\Withdrawal\getRequestResource;
use App\Models\WalletTransaction;
use App\Models\WithdrawRequest;
use App\Notifications\UniversalNotification;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WithdrawalRequestController extends Controller
{
    use ApiResponse;
    /**
     * list of withdrawal request
     */
    public function index(getWithdrawalRequest $request)
    {
        try {
            $validated = $request->validated();
            $page = $validated['page'] ?? 1;
            $perPage = $validated['per_page'] ?? 10;

            // get current user
            $manager = auth()->user();

            if ($manager->role->value !== 'manager') {
                return $this->sendError("You don't have permission to access this page.",[], 403);
            }

            $withdrawal = WithdrawRequest::with('user')
                ->where('manager_id', $manager->id)
                ->latest()
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->sendResponse(getRequestResource::collection($withdrawal), 'Withdrawal request list retrieved successfully.',200,[],$withdrawal);
        }catch (\Exception $e){
            Log::error('Failed to retrieve withdrawal requests', [
                'manager_id' => auth()->id(),
                'error'      => $e->getMessage(),
            ]);

            return $this->sendError('Failed to retrieve withdrawal request list.', [],500);
        }
    }

    /**
     * View withdrawal Request
     */
    public function show($id)
    {
        try {
            // get current user
            $user = auth()->user();

            // Role check
            if ($user->role->value !== 'manager') {
                return $this->sendError("You don't have permission to access this resource.", [],403);
            }
            $withdraw = WithdrawRequest::with('user')->where('manager_id', $user->id)->find($id);
            if (!$withdraw) {
                return $this->sendError("Withdraw request not found.",[], 404);
            }
            if($withdraw->manager_id !== $user->id){
                return $this->sendError("You don't have permission to access this page.",[], 403);
            }
            return $this->sendResponse( new getRequestDetailsResource($withdraw), 'Withdraw request details retrieved');
        }catch (\Exception $e){
            Log::error("Failed to get DepositRequest : ".$e->getMessage());
            return $this->sendError("Failed to get DepositRequest.",[], 500);
        }
    }


    /**
     * Manager – Approve Withdrawal Request
     */
    public function approve($id)
    {
        try {
            $manager = auth()->user();

            $withdrawal = WithdrawRequest::with('user')
                ->where('manager_id', $manager->id)
                ->find($id);

            if (!$withdrawal) {
                return $this->sendError('Withdrawal request not found.', [], 404);
            }

            if($withdrawal->manager_id !== $manager->id){
                return $this->sendError("You don't have permission to access this page.",[], 403);
            }

            if ($withdrawal->status !== TransactionStatus::PENDING->value) {
                return $this->sendError('Withdrawal already processed.', [], 422);
            }

            $withdrawal->update([
                'status' => TransactionStatus::APPROVED->value,
            ]);

            // Notify user
            $withdrawal->user->notify(
                new UniversalNotification(
                    'Withdrawal Approved',
                    "Your withdrawal request of {$withdrawal->amount} has been approved.",
                    'success',
                    ['withdrawal_id' => $withdrawal->id]
                )
            );

            return $this->sendResponse([], 'Withdrawal approved successfully');

        } catch (\Exception $e) {
            Log::error('Withdraw approve failed', [
                'withdraw_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return $this->sendError('Failed to approve withdrawal', [], 500);
        }
    }

    /**
     * Manager – Reject Withdrawal Request
     */
    public function reject(RejectRequest $request, $id)
    {
        try {
            $validated = $request->validated();

            $manager = auth()->user();

            $withdrawal = WithdrawRequest::with('user')
                ->where('manager_id', $manager->id)
                ->find($id);

            if (!$withdrawal) {
                return $this->sendError('Withdrawal request not found.', [], 404);
            }

            if($withdrawal->manager_id !== $manager->id){
                return $this->sendError("You don't have permission to access this page.",[], 403);
            }

            if ($withdrawal->status !== TransactionStatus::PENDING->value) {
                return $this->sendError('Withdrawal already processed.', [], 422);
            }

            $withdrawal->update([
                'status'  => TransactionStatus::REJECTED->value,
                'reject_reason' => $validated['reason'],
            ]);

            // Notify user
            $withdrawal->user->notify(
                new UniversalNotification(
                    'Withdrawal Rejected',
                    "Your withdrawal request was rejected. Reason: {$validated['reason']}",
                    'error',
                    ['withdrawal_id' => $withdrawal->id]
                )
            );

            return $this->sendResponse([], 'Withdrawal rejected successfully');
        } catch (\Exception $e) {
            Log::error('Withdraw reject failed', [
                'withdraw_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return $this->sendError('Failed to reject withdrawal', [], 500);
        }
    }


    /**
     * Manager – Mark Withdrawal as Paid
     */
    public function markAsPaid($id)
    {
        try {
            $manager = auth()->user();

            $withdrawal = WithdrawRequest::with('user')
                ->where('manager_id', $manager->id)
                ->find($id);

            if (!$withdrawal) {
                return $this->sendError('Withdrawal request not found.', [], 404);
            }
            if($withdrawal->manager_id !== $manager->id){
                return $this->sendError("You don't have permission to access this page.",[], 403);
            }

            if ($withdrawal->status !== TransactionStatus::APPROVED->value) {
                return $this->sendError('Withdrawal not approved yet.', [], 422);
            }

            DB::transaction(function () use ($withdrawal, $manager) {

                $user = $withdrawal->user;

                // Ensure wallets exist
                $userWallet = $user->wallet()->firstOrCreate(['user_id' => $user->id]);
                $managerWallet = $manager->wallet()->firstOrCreate(['user_id' => $manager->id]);

                // Check user balance
                if ($userWallet->balance < $withdrawal->amount) {
                    throw new \Exception('User has insufficient balance.');
                }

                // USER wallet DEBIT
                $userWallet->decrement('balance', $withdrawal->amount);

                // MANAGER wallet CREDIT (cash/bank settlement)
                $managerWallet->increment('balance', $withdrawal->amount);

                // Ledger entry
                WalletTransaction::create([
                    'from_user_id' => $user->id,
                    'to_user_id'   => $manager->id,
                    'amount'       => $withdrawal->amount,
                    'type'         => TransactionType::DEBIT->value,
                    'source'       => TransactionSource::WITHDRAW->value,
                    'status'       => TransactionStatus::APPROVED->value,
                    'reference_id' => $withdrawal->id,
                ]);

                // Update withdrawal status
                $withdrawal->update([
                    'status' => TransactionStatus::PAID->value,
                ]);
            });

            // Notify user
            $withdrawal->user->notify(
                new UniversalNotification(
                    'Withdrawal Paid',
                    "Your withdrawal of {$withdrawal->amount} has been successfully paid.",
                    'success',
                    ['withdrawal_id' => $withdrawal->id]
                )
            );

            return $this->sendResponse([], 'Withdrawal marked as paid');

        } catch (\Throwable $e) {
            Log::error('Withdraw mark as paid failed', [
                'withdraw_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return $this->sendError('Failed to mark withdrawal as paid', [], 500);
        }
    }



}
