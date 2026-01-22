<?php

namespace App\Http\Controllers\API\V1\Manager;

use App\Enum\Wallet\TransactionSource;
use App\Enum\Wallet\TransactionStatus;
use App\Enum\Wallet\TransactionType;
use App\Helpers\translateHelper;
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
        $lang = $request->header('Accept-Language', 'en');
        try {
            $validated = $request->validated();
            $page = $validated['page'] ?? 1;
            $perPage = $validated['per_page'] ?? 10;

            // get current user
            $manager = auth()->user();

            if ($manager->role->value !== 'manager') {
                $errorMessage = translateHelper::translateCached(
                    'withdraw_permission_denied',
                    "You don't have permission to access this page.",
                    $lang
                );

                return $this->sendError($errorMessage, [], 403);
            }

            $withdrawal = WithdrawRequest::with('user')
                ->where('manager_id', $manager->id)
                ->latest()
                ->paginate($perPage, ['*'], 'page', $page);

            $successMessage = translateHelper::translateCached(
                'withdraw_list_success',
                'Withdrawal request list retrieved successfully.',
                $lang
            );

            return $this->sendResponse(
                getRequestResource::collection($withdrawal),
                $successMessage,200, [], $withdrawal
            );
        }catch (\Exception $e){
            Log::error('Failed to retrieve withdrawal requests', [
                'manager_id' => auth()->id(),
                'error'      => $e->getMessage(),
            ]);

            $errorMessage = translateHelper::translateCached(
                'withdraw_list_failed',
                'Failed to retrieve withdrawal request list.',
                $lang
            );

            return $this->sendError($errorMessage, [], 500);
        }
    }

    /**
     * View withdrawal Request
     */
    public function show(Request $request,$id)
    {
        $lang = $request->header('Accept-Language', 'en');
        try {
            // get current user
            $user = auth()->user();

            // Role check
            if ($user->role->value !== 'manager') {
                $errorMessage = translateHelper::translateCached(
                    'withdraw_permission_denied',
                    "You don't have permission to access this resource.",
                    $lang
                );

                return $this->sendError($errorMessage, [], 403);
            }
            $withdraw = WithdrawRequest::with('user')->where('manager_id', $user->id)->find($id);
            if (!$withdraw) {
                $errorMessage = translateHelper::translateCached(
                    'withdraw_request_not_found',
                    'Withdraw request not found.',
                    $lang
                );

                return $this->sendError($errorMessage, [], 404);
            }
            if($withdraw->manager_id !== $user->id){
                $errorMessage = translateHelper::translateCached(
                    'withdraw_access_denied',
                    "You don't have permission to access this page.",
                    $lang
                );

                return $this->sendError($errorMessage, [], 403);
            }
            $successMessage = translateHelper::translateCached(
                'withdraw_details_success',
                'Withdraw request details retrieved successfully.',
                $lang
            );

            return $this->sendResponse(
                new getRequestDetailsResource($withdraw),
                $successMessage
            );
        }catch (\Exception $e){
            Log::error("Failed to get DepositRequest : ".$e->getMessage());
            $errorMessage = translateHelper::translateCached(
                'withdraw_details_failed',
                'Failed to retrieve withdraw request details.',
                $lang
            );

            return $this->sendError($errorMessage, [], 500);
        }
    }

    /**
     * Manager – Approve Withdrawal Request
     */
    public function approve(Request $request,$id)
    {
        $lang = $request->header('Accept-Language', 'en');
        try {
            $manager = auth()->user();

            $withdrawal = WithdrawRequest::with('user')
                ->where('manager_id', $manager->id)
                ->find($id);

            if (!$withdrawal) {
                $message = translateHelper::translateCached(
                    'withdraw_request_not_found',
                    'Withdrawal request not found.',
                    $lang
                );
                return $this->sendError($message, [], 404);
            }

            if($withdrawal->manager_id !== $manager->id){
                $message = translateHelper::translateCached(
                    'withdraw_permission_denied',
                    "You don't have permission to access this page.",
                    $lang
                );
                return $this->sendError($message, [], 403);
            }

            if ($withdrawal->status !== TransactionStatus::PENDING->value) {
                $message = translateHelper::translateCached(
                    'withdraw_already_processed',
                    'Withdrawal already processed.',
                    $lang
                );
                return $this->sendError($message, [], 422);
            }

            // OPTIONAL EARLY BALANCE CHECK
            $user   = $withdrawal->user;
            $wallet = $user->wallet;

            if (!$wallet || $wallet->balance < $withdrawal->amount) {
                $message = translateHelper::translateCached(
                    'withdraw_insufficient_balance',
                    'User does not have sufficient balance for this withdrawal.',
                    $lang
                );
                return $this->sendError($message, [], 422);            }

            /* ================= TRANSACTION WITH LOCK ================= */
            DB::transaction(function () use ($id, $manager,$lang) {

                // LOCK withdrawal row
                $withdrawal = WithdrawRequest::where('id', $id)
                    ->lockForUpdate()
                    ->first();

                // Double safety check after lock
                if ($withdrawal->status !== TransactionStatus::PENDING->value) {
                    throw new \RuntimeException(
                        translateHelper::translateCached(
                            'withdraw_already_processed',
                            'Withdrawal already processed.',
                            $lang
                        )
                    );
                }

                // LOCK user wallet
                $user = $withdrawal->user()->lockForUpdate()->first();
                $userWallet = $user->wallet()->lockForUpdate()
                    ->firstOrCreate(['user_id' => $user->id]);

                // LOCK manager wallet
                $managerWallet = $manager->wallet()->lockForUpdate()
                    ->firstOrCreate(['user_id' => $manager->id]);

                // FINAL BALANCE CHECK
                if ($userWallet->balance < $withdrawal->amount) {
                    throw new \RuntimeException(
                        translateHelper::translateCached(
                            'withdraw_insufficient_balance',
                            'Insufficient wallet balance.',
                            $lang
                        )
                    );
                }

                // USER wallet DEBIT
                $userWallet->decrement('balance', $withdrawal->amount);

                // MANAGER wallet CREDIT
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
                    'status' => TransactionStatus::APPROVED->value,
                ]);
            });
            // Notify user
            $withdrawal->user->notify(
                new UniversalNotification(
                    'Withdrawal Approved',
                    "Your withdrawal request of {$withdrawal->amount} has been approved.",
                    'success',
                    ['withdrawal_id' => $withdrawal->id]
                )
            );

            $successMessage = translateHelper::translateCached(
                'withdraw_approved_success',
                'Withdrawal approved successfully.',
                $lang
            );

            return $this->sendResponse([], $successMessage);

        } catch (\Exception $e) {
            Log::error('Withdraw approve failed', [
                'withdraw_id' => $id,
                'error' => $e->getMessage(),
            ]);

            $errorMessage = translateHelper::translateCached(
                'withdraw_approve_failed',
                'Failed to approve withdrawal.',
                $lang
            );

            return $this->sendError($errorMessage, [], 500);
        }
    }

    /**
     * Manager – Reject Withdrawal Request
     */
    public function reject(RejectRequest $request, $id)
    {
        $lang = $request->header('Accept-Language', 'en');
        try {
            $validated = $request->validated();

            $manager = auth()->user();

            $withdrawal = WithdrawRequest::with('user')
                ->where('manager_id', $manager->id)
                ->find($id);

            if (!$withdrawal) {
                $message = translateHelper::translateCached(
                    'withdraw_request_not_found',
                    'Withdrawal request not found.',
                    $lang
                );
                return $this->sendError($message, [], 404);            }

            if($withdrawal->manager_id !== $manager->id){
                $message = translateHelper::translateCached(
                    'withdraw_permission_denied',
                    "You don't have permission to access this page.",
                    $lang
                );
                return $this->sendError($message, [], 403);
            }

            if ($withdrawal->status !== TransactionStatus::PENDING->value) {
                $message = translateHelper::translateCached(
                    'withdraw_already_processed',
                    'Withdrawal already processed.',
                    $lang
                );
                return $this->sendError($message, [], 422);
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

            $successMessage = translateHelper::translateCached(
                'withdraw_rejected_success',
                'Withdrawal rejected successfully.',
                $lang
            );

            return $this->sendResponse([], $successMessage);
        } catch (\Exception $e) {
            Log::error('Withdraw reject failed', [
                'withdraw_id' => $id,
                'error' => $e->getMessage(),
            ]);


            $errorMessage = translateHelper::translateCached(
                'withdraw_reject_failed',
                'Failed to reject withdrawal.',
                $lang
            );

            return $this->sendError($errorMessage, [], 500);
        }
    }

    /**
     * Manager – Mark Withdrawal as Paid
     * Mark as Paid = Payment Confirmation
     */
    public function markAsPaid(Request $request,$id)
    {
        $lang = $request->header('Accept-Language', 'en');
        try {
            $manager = auth()->user();

            // Basic validation
            $withdrawal = WithdrawRequest::with('user')
                ->where('manager_id', $manager->id)
                ->find($id);

            if (!$withdrawal) {
                $message = translateHelper::translateCached(
                    'withdraw_request_not_found',
                    'Withdrawal request not found.',
                    $lang
                );
                return $this->sendError($message, [], 404);
            }

            if ($withdrawal->manager_id !== $manager->id) {
                $message = translateHelper::translateCached(
                    'withdraw_permission_denied',
                    "You don't have permission to access this page.",
                    $lang
                );
                return $this->sendError($message, [], 403);
            }

            if ($withdrawal->status !== TransactionStatus::APPROVED->value) {
                $message = translateHelper::translateCached(
                    'withdraw_not_approved',
                    'Withdrawal must be approved before marking as paid.',
                    $lang
                );
                return $this->sendError($message, [], 422);
            }

            DB::transaction(function () use ($id,$lang) {

                // Lock withdrawal row (prevent double paid)
                $withdrawal = WithdrawRequest::where('id', $id)
                    ->lockForUpdate()
                    ->first();

                if ($withdrawal->status !== TransactionStatus::APPROVED->value) {
                    throw new \RuntimeException(
                        translateHelper::translateCached(
                            'withdraw_already_processed',
                            'Withdrawal already processed.',
                            $lang
                        )
                    );
                }

                // Only status update
                $withdrawal->update([
                    'status'  => TransactionStatus::PAID->value,
                ]);

                // Optional: update ledger status
                WalletTransaction::where('reference_id', $withdrawal->id)
                    ->update([
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

            $successMessage = translateHelper::translateCached(
                'withdraw_mark_paid_success',
                'Withdrawal marked as paid successfully.',
                $lang
            );

            return $this->sendResponse([], $successMessage);

        } catch (\Exception $e) {
            Log::error('Withdraw mark as paid failed', [
                'withdraw_id' => $id,
                'error'       => $e->getMessage(),
            ]);

            $errorMessage = translateHelper::translateCached(
                'withdraw_mark_paid_failed',
                'Failed to mark withdrawal as paid.',
                $lang
            );

            return $this->sendError($errorMessage, [], 500);
        }
    }

}
