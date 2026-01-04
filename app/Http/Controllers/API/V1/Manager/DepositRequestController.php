<?php

namespace App\Http\Controllers\API\V1\Manager;

use App\Enum\Wallet\TransactionSource;
use App\Enum\Wallet\TransactionStatus;
use App\Enum\Wallet\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Manager\Deposit\getDepositRequest;
use App\Http\Requests\Api\V1\Manager\Deposit\RejectRequest;
use App\Http\Resources\Api\V1\Manager\Deposit\DepositRequestDetailsResource;
use App\Http\Resources\Api\V1\Manager\Deposit\getDepositRequestResource;
use App\Models\DepositRequest;
use App\Models\WalletTransaction;
use App\Notifications\UniversalNotification;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DepositRequestController extends Controller
{
    use ApiResponse;

    /**
     * list of deposit request list
     */
    public function index(getDepositRequest $request)
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

            $deposits = DepositRequest::with('user')
                ->where('manager_id', $manager->id)
                ->latest()
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->sendResponse(getDepositRequestResource::collection($deposits), 'Deposit request list retrieved successfully.',200,[],$deposits);
        }catch (\Exception $e){
            Log::error("Failed to get DepositRequest list : ".$e->getMessage());
            return $this->sendError("Failed to get DepositRequest list.",[], 500);
        }
    }

    /**
     * View Deposit Request
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
            $deposit = DepositRequest::with('user')->where('manager_id', $user->id)->find($id);
            if (!$deposit) {
                return $this->sendError("Deposit request not found.",[], 404);
            }
            if($deposit->manager_id !== $user->id){
                return $this->sendError("You don't have permission to access this page.",[], 403);
            }
            return $this->sendResponse( new DepositRequestDetailsResource($deposit), 'Deposit request details retrieved');
        }catch (\Exception $e){
            Log::error("Failed to get DepositRequest : ".$e->getMessage());
            return $this->sendError("Failed to get DepositRequest.",[], 500);
        }
    }

//    /**
//     * Manager – Approve Deposit Request
//     */
//    public function approve($deposit)
//    {
//        try {
//            // get current user
//            $user = auth()->user();
//
//            $deposit = DepositRequest::with('user')->where('manager_id', $user->id)->find($deposit);
//
//            if (!$deposit) {
//                return $this->sendError("Deposit request not found.",[], 404);
//            }
//            if($deposit->manager_id !== $user->id){
//                return $this->sendError("You don't have permission to access this page.",[], 403);
//            }
//            if ($deposit->status !== TransactionStatus::PENDING->value) {
//                return $this->sendError('Deposit already processed', [], 422);
//            }
//
//            $request_user  = $deposit->user;
//
//            DB::transaction(function () use ($deposit, $user, $request_user) {
//
//                // Manager → User wallet
//                $user->wallet->decrement('balance', $deposit->amount);
//                $request_user->wallet->increment('balance', $deposit->amount);
//
//                // Ledger
//                WalletTransaction::create([
//                    'from_user_id' => $user->id,
//                    'to_user_id'   => $request_user->id,
//                    'amount'       => $deposit->amount,
//                    'type'         => TransactionType::CREDIT->value,
//                    'source'       => TransactionSource::DEPOSIT->value,
//                    'status'       => TransactionStatus::APPROVED->value,
//                    'reference_id' => $deposit->id,
//                ]);
//
//                // Update status
//                $deposit->update([
//                    'status' => TransactionStatus::APPROVED->value,
//                ]);
//            });
//
//            // Notify user
//            $user->notify(new UniversalNotification(
//                'Deposit Approved',
//                "Your deposit of {$deposit->amount} has been approved.",
//                'success',
//                ['deposit_id' => $deposit->id]
//            ));
//
//            return $this->sendResponse([], 'Deposit approved successfully');
//        } catch (\Exception $e) {
//            Log::error('Deposit approval failed', [
//                'deposit_id' => $deposit->id,
//                'error'      => $e->getMessage(),
//            ]);
//
//            return $this->sendError('Failed to approve deposit', [], 500);
//        }
//    }

    /**
     * Manager – Approve Deposit Request (LOCK SAFE)
     */
    public function approve($depositId)
    {
        try {
            $manager = auth()->user();

            // Basic validation (no lock yet)
            $deposit = DepositRequest::with('user')->where('manager_id', $manager->id)->find($depositId);

            if (!$deposit) {
                return $this->sendError("Deposit request not found.", [], 404);
            }

            if ($deposit->manager_id !== $manager->id) {
                return $this->sendError("You don't have permission to access this page.", [], 403);
            }

            if ($deposit->status !== TransactionStatus::PENDING->value) {
                return $this->sendError('Deposit already processed', [], 422);
            }

            // Ensure manager wallet exists
            $managerWallet = $manager->wallet()->firstOrCreate([
                'user_id' => $manager->id
            ]);

            // Early balance check
            if ($managerWallet->balance < $deposit->amount) {
                return $this->sendError(
                    "Manager does not have sufficient balance to approve this deposit.",
                    [],
                    422
                );
            }

            /* ================= TRANSACTION WITH LOCK ================= */
            DB::transaction(function () use ($depositId, $manager) {

                // Lock deposit row
                $deposit = DepositRequest::where('id', $depositId)
                    ->lockForUpdate()
                    ->first();

                if ($deposit->status !== TransactionStatus::PENDING->value) {
                    throw new \Exception('Deposit already processed.');
                }

                // Lock manager wallet
                $managerWallet = $manager->wallet()
                    ->lockForUpdate()
                    ->firstOrCreate(['user_id' => $manager->id]);

                // Lock user & user wallet
                $requestUser = $deposit->user()->lockForUpdate()->first();
                $userWallet  = $requestUser->wallet()
                    ->lockForUpdate()
                    ->firstOrCreate(['user_id' => $requestUser->id]);

                // FINAL balance check
                if ($managerWallet->balance < $deposit->amount) {
                    throw new \Exception('Manager has insufficient balance.');
                }

                // Manager → User wallet transfer
                $managerWallet->decrement('balance', $deposit->amount);
                $userWallet->increment('balance', $deposit->amount);

                // Ledger
                WalletTransaction::create([
                    'from_user_id' => $manager->id,
                    'to_user_id'   => $requestUser->id,
                    'amount'       => $deposit->amount,
                    'type'         => TransactionType::CREDIT->value,
                    'source'       => TransactionSource::DEPOSIT->value,
                    'status'       => TransactionStatus::APPROVED->value,
                    'reference_id' => $deposit->id,
                ]);

                // Update deposit status
                $deposit->update([
                    'status' => TransactionStatus::APPROVED->value,
                ]);
            });

            // Notify user
            $deposit->user->notify(
                new UniversalNotification(
                    'Deposit Approved',
                    "Your deposit of {$deposit->amount} has been approved.",
                    'success',
                    ['deposit_id' => $deposit->id]
                )
            );

            return $this->sendResponse([], 'Deposit approved successfully');

        } catch (\Exception $e) {

            Log::error('Deposit approval failed', [
                'deposit_id' => $depositId,
                'error'      => $e->getMessage(),
            ]);

            return $this->sendError('Failed to approve deposit:'.$e->getMessage(), [], 500);
        }
    }



    /**
     * Manager – Reject Deposit Request
     */
    public function reject(RejectRequest $request, $deposit)
    {
        try {
            $validated = $request->validated();
            // get current user (manager)
            $user = auth()->user();

            // find deposit request under this manager
            $deposit = DepositRequest::with('user')
                ->where('manager_id', $user->id)
                ->find($deposit);

            if (!$deposit) {
                return $this->sendError("Deposit request not found.", [], 404);
            }

            if ($deposit->manager_id !== $user->id) {
                return $this->sendError( "You don't have permission to access this page.", [],403);
            }

            if ($deposit->status !== TransactionStatus::PENDING->value) {
                return $this->sendError('Deposit already processed', [], 422);
            }

            // Reject deposit (NO wallet movement)
            $deposit->update([
                'status'  => TransactionStatus::REJECTED->value,
                'reject_reason' => $validated['reason'],
            ]);

            // Notify request user
            $request_user = $deposit->user;
            $request_user->notify(
                new UniversalNotification(
                    'Deposit Rejected',
                    "Your deposit request of {$deposit->amount} has been rejected. Reason: {$validated['reason']}",
                    'error',
                    ['deposit_id' => $deposit->id]
                )
            );

            return $this->sendResponse([], 'Deposit rejected successfully');
        } catch (\Exception $e) {
            Log::error('Deposit rejection failed', [
                'deposit_id' => $deposit ?? null,
                'error'      => $e->getMessage(),
            ]);
            return $this->sendError('Failed to reject deposit', [], 500);
        }
    }

}
