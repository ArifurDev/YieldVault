<?php

namespace App\Http\Controllers\API\V1\Manager;

use App\Enum\ActivityLog\Action;
use App\Enum\ActivityLog\Level;
use App\Enum\ActivityLog\Type;
use App\Enum\Wallet\TransactionSource;
use App\Enum\Wallet\TransactionStatus;
use App\Enum\Wallet\TransactionType;
use App\Helpers\translateHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Manager\Deposit\getDepositRequest;
use App\Http\Requests\Api\V1\Manager\Deposit\RejectRequest;
use App\Http\Resources\Api\V1\Manager\Deposit\DepositRequestDetailsResource;
use App\Http\Resources\Api\V1\Manager\Deposit\getDepositRequestResource;
use App\Models\ActiveLog;
use App\Models\DepositRequest;
use App\Models\WalletTransaction;
use App\Notifications\UniversalNotification;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
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
        $lang = $request->header('Accept-Language', 'en');
        try {
            $validated = $request->validated();
            $page = $validated['page'] ?? 1;
            $perPage = $validated['per_page'] ?? 10;

            // get current user
            $manager = auth()->user();

            if ($manager->role->value !== 'manager') {
                $message = translateHelper::translateCached(
                    'deposit_permission_denied',
                    "You don't have permission to access this page.",
                    $lang
                );

                return $this->sendError($message, [], 403);
            }

            $deposits = DepositRequest::with('user')
                ->where('manager_id', $manager->id)
                ->latest()
                ->paginate($perPage, ['*'], 'page', $page);

            $successMessage = translateHelper::translateCached(
                'deposit_list_success',
                'Deposit request list retrieved successfully.',
                $lang
            );

            return $this->sendResponse(
                getDepositRequestResource::collection($deposits),
                $successMessage,
                200,
                [],
                $deposits
            );
        } catch (\Exception $e) {
            Log::error("Failed to get DepositRequest list : " . $e->getMessage());
            $errorMessage = translateHelper::translateCached(
                'deposit_list_failed',
                'Failed to get deposit request list.',
                $lang
            );

            return $this->sendError($errorMessage, [], 500);
        }
    }

    /**
     * View Deposit Request
     */
    public function show(Request $request ,$id)
    {
        $lang = $request->header('Accept-Language', 'en');
        try {
            // get current user
            $user = auth()->user();

            // Role check
            if ($user->role->value !== 'manager') {
                $message = translateHelper::translateCached(
                    'deposit_view_permission_denied',
                    "You don't have permission to access this resource.",
                    $lang
                );

                return $this->sendError($message, [], 403);
            }
            $deposit = DepositRequest::with('user')->where('manager_id', $user->id)->find($id);
            if (!$deposit) {
                $message = translateHelper::translateCached(
                    'deposit_not_found',
                    'Deposit request not found.',
                    $lang
                );

                return $this->sendError($message, [], 404);
            }
            if ($deposit->manager_id !== $user->id) {
                $message = translateHelper::translateCached(
                    'deposit_view_permission_denied',
                    "You don't have permission to access this page.",
                    $lang
                );

                return $this->sendError($message, [], 403);
            }
            $successMessage = translateHelper::translateCached(
                'deposit_details_success',
                'Deposit request details retrieved successfully.',
                $lang
            );

            return $this->sendResponse(
                new DepositRequestDetailsResource($deposit),
                $successMessage
            );
        } catch (\Exception $e) {
            Log::error("Failed to get DepositRequest : " . $e->getMessage());
            $errorMessage = translateHelper::translateCached(
                'deposit_details_failed',
                'Failed to retrieve deposit request details.',
                $lang
            );

            return $this->sendError($errorMessage, [], 500);
        }
    }

    /**
     * Manager – Approve Deposit Request (LOCK SAFE)
     */
    public function approve(Request $request,$depositId)
    {
        $lang = $request->header('Accept-Language', 'en');
        try {
            $manager = auth()->user();

            // Basic validation (no lock yet)
            $deposit = DepositRequest::with('user')->where('manager_id', $manager->id)->find($depositId);

            if (!$deposit) {
                return $this->sendError(
                    translateHelper::translateCached(
                        'deposit_not_found',
                        'Deposit request not found.',
                        $lang
                    ),[],404
                );
            }

            if ($deposit->manager_id !== $manager->id) {
                return $this->sendError(
                    translateHelper::translateCached(
                        'deposit_permission_denied',
                        "You don't have permission to access this page.",
                        $lang
                    ),[], 403
                );
            }

            if ($deposit->status !== TransactionStatus::PENDING->value) {
                return $this->sendError(
                    translateHelper::translateCached(
                        'deposit_already_processed',
                        'Deposit already processed.',
                        $lang
                    ), [], 422
                );
            }

            // Ensure manager wallet exists
            $managerWallet = $manager->wallet()->firstOrCreate([
                'user_id' => $manager->id
            ]);

            // Early balance check
            if ($managerWallet->balance < $deposit->amount) {
                return $this->sendError(
                    translateHelper::translateCached(
                        'deposit_manager_insufficient_balance',
                        'Manager does not have sufficient balance to approve this deposit.',
                        $lang
                    ), [],422
                );
            }

            /* ================= TRANSACTION WITH LOCK ================= */
            DB::transaction(function () use ($depositId, $manager,$lang) {

                // Lock deposit row
                $deposit = DepositRequest::where('id', $depositId)
                    ->lockForUpdate()
                    ->first();

                if ($deposit->status !== TransactionStatus::PENDING->value) {
                    throw new \RuntimeException(
                        translateHelper::translateCached(
                            'deposit_already_processed',
                            'Deposit already processed.',
                            $lang
                        )
                    );
                }

                // Lock manager wallet
                $managerWallet = $manager->wallet()
                    ->lockForUpdate()
                    ->firstOrCreate(['user_id' => $manager->id]);

                // Lock user & user wallet
                $requestUser = $deposit->user()->lockForUpdate()->first();
                $userWallet = $requestUser->wallet()
                    ->lockForUpdate()
                    ->firstOrCreate(['user_id' => $requestUser->id]);

                // FINAL balance check
                if ($managerWallet->balance < $deposit->amount) {

                    throw new \RuntimeException(
                        translateHelper::translateCached(
                            'deposit_manager_insufficient_balance',
                            'Manager has insufficient balance.',
                            $lang
                        )
                    );
                }

                // Manager → User wallet transfer
                $managerWallet->decrement('balance', $deposit->amount);
                $userWallet->increment('balance', $deposit->amount);

                // Ledger
                WalletTransaction::create([
                    'from_user_id' => $manager->id,
                    'to_user_id' => $requestUser->id,
                    'amount' => $deposit->amount,
                    'type' => TransactionType::CREDIT->value,
                    'source' => TransactionSource::DEPOSIT->value,
                    'status' => TransactionStatus::APPROVED->value,
                    'reference_id' => $deposit->id,
                ]);

                // Update deposit status
                $deposit->update([
                    'status' => TransactionStatus::APPROVED->value,
                ]);

                // Log activity
                ActiveLog::create([
                    'actor_id' => $manager->id,
                    'target_user_id' => $requestUser->id,
                    'type' => Type::DEPOSIT,
                    'action' => Action::APPROVED,
                    'amount' => $deposit->amount,
                    'reference_type' => DepositRequest::class,
                    'reference_id' => $deposit->id,
                    'message' => "Approved {$requestUser->first_name}'s deposit ({$deposit->amount})",
                    'level' => Level::SUCCESS,
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

            return $this->sendResponse(
                [],
                translateHelper::translateCached(
                    'deposit_approve_success',
                    'Deposit approved successfully.',
                    $lang
                )
            );

        } catch (\Exception $e) {
            Log::error('Deposit approval failed', [
                'deposit_id' => $depositId,
                'error' => $e->getMessage(),
            ]);

            return $this->sendError(
                translateHelper::translateCached(
                    'deposit_approve_failed',
                    'Failed to approve deposit.',
                    $lang
                ), [],500
            );
        }
    }


    /**
     * Manager – Reject Deposit Request
     */
    public function reject(RejectRequest $request, $deposit)
    {
        $lang = $request->header('Accept-Language', 'en');
        try {
            $validated = $request->validated();
            // get current user (manager)
            $user = auth()->user();

            // find deposit request under this manager
            $deposit = DepositRequest::with('user')
                ->where('manager_id', $user->id)
                ->find($deposit);

            if (!$deposit) {
                $errorMessage = translateHelper::translateCached(
                    'deposit_details_not_found',
                    'Deposit request not found.',
                    $lang
                );

                return $this->sendError($errorMessage, [], 404);
            }

            if ($deposit->manager_id !== $user->id) {
                $errorMessage = translateHelper::translateCached(
                    'deposit_permission_denied',
                    "You don't have permission to access this page.",
                    $lang
                );

                return $this->sendError($errorMessage, [], 403);
            }

            if ($deposit->status !== TransactionStatus::PENDING->value) {
                $errorMessage = translateHelper::translateCached(
                    'deposit_already_processed',
                    'Deposit already processed.',
                    $lang
                );

                return $this->sendError($errorMessage, [], 422);
            }

            // Reject deposit (NO wallet movement)
            $deposit->update([
                'status' => TransactionStatus::REJECTED->value,
                'reject_reason' => $validated['reason'],
            ]);

            ActiveLog::create([
                'actor_id' => $user->id,
                'target_user_id' => $deposit->user_id,
                'type' => Type::DEPOSIT,
                'action' => Action::REJECTED,
                'amount' => $deposit->amount,
                'reference_type' => DepositRequest::class,
                'reference_id' => $deposit->id,
                'message' => "Rejected {$deposit->user->first_name}'s deposit ({$deposit->amount})",
                'level' => Level::DANGER,
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

            $successMessage = translateHelper::translateCached(
                'deposit_rejected_success',
                'Deposit rejected successfully.',
                $lang
            );

            return $this->sendResponse([], $successMessage);
        } catch (\Exception $e) {
            Log::error('Deposit rejection failed', [
                'deposit_id' => $deposit ?? null,
                'error' => $e->getMessage(),
            ]);
            $errorMessage = translateHelper::translateCached(
                'deposit_rejected_failed',
                'Failed to reject deposit.',
                $lang
            );

            return $this->sendError($errorMessage, [], 500);
        }
    }

}
