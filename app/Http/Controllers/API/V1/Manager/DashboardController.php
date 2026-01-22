<?php

namespace App\Http\Controllers\API\V1\Manager;

use App\Helpers\translateHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Manager\ActivityLogRequest;
use App\Http\Requests\Api\V1\Manager\getRecentTransactionRequest;
use App\Http\Resources\Api\V1\Manager\ActivityLogResource;
use App\Http\Resources\Api\V1\Manager\RecentTransactionResource;
use App\Models\ActiveLog;
use App\Models\DepositRequest;
use App\Models\KycDocument;
use App\Models\User;
use App\Models\WithdrawRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    use ApiResponse;

    /**
     * Manager Overview -> Quick Stats
     */
    public function summary(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        try {
            $manager = auth()->user();

            // Role check
            if ($manager->role->value !== 'manager') {
                $message = translateHelper::translateCached(
                    'manager_dashboard_unauthorized',
                    'Unauthorized access.',
                    $lang
                );

                return $this->sendError($message, [], 403);
            }

             // Pending Deposits
            $pendingDepositsQuery = DepositRequest::where('manager_id', $manager->id)
                ->where('status', 'pending');

            $pendingDeposits = [
                'count'  => $pendingDepositsQuery->count(),
                'amount' => number_format($pendingDepositsQuery->sum('amount'), 2),
            ];

            // Pending Withdrawals
            $pendingWithdrawalsQuery = WithdrawRequest::where('manager_id', $manager->id)
                ->where('status', 'pending');

            $pendingWithdrawals = [
                'count'  => $pendingWithdrawalsQuery->count(),
                'amount' => number_format($pendingWithdrawalsQuery->sum('amount'), 2),
            ];

            // Pending KYC
            $pendingKyc = KycDocument::where('status', 'Pending')->count();

            // Assigned Investors
            $assignedInvestors = User::where('manager_id', $manager->id)
                ->where('kyc_status', 'Approved')
                ->count();


            $successMessage = translateHelper::translateCached(
                'manager_dashboard_summary_success',
                'Manager dashboard summary retrieved successfully.',
                $lang
            );


            // Response
            return $this->sendResponse([
                'pending_deposits' => $pendingDeposits,
                'pending_withdrawals' => $pendingWithdrawals,
                'pending_kyc' => [
                    'count' => $pendingKyc,
                    'label' => 'Need Review' ? translateHelper::translateCached(
                        'manager_pending_kyc_label',
                        'Need Review',
                        $lang
                    ) : null,
                ],
                'assigned_investors' => [
                    'count' => $assignedInvestors,
                    'label' => 'Active' ? translateHelper::translateCached(
                        'manager_assigned_investors_label',
                        'Active',
                        $lang
                    ) : null,
                ],
            ], $successMessage);

        } catch (\Exception $e) {

            Log::error('Manager dashboard summary error', [
                'manager_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            $errorMessage = translateHelper::translateCached(
                'manager_dashboard_summary_failed',
                'Failed to retrieve dashboard summary.',
                $lang
            );

            return $this->sendError($errorMessage, [], 500);
        }
    }


    /**
     * Activity Logs
     */
    public function ActivityLogs(ActivityLogRequest $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        try {
            $validated = $request->validated();
            $page    = $validated['page'] ?? 1;
            $perPage = $validated['per_page'] ?? 10;

            $sortBy = 'created_at';
            $order  = $validated['order']   ?? 'desc';

            $query = ActiveLog::where('actor_id',auth()->user()->id)
                ->orderBy($sortBy, $order);

            $logs = $query->paginate($perPage, ['*'], 'page', $page);

            $successMessage = translateHelper::translateCached(
                'activity_logs_retrieved_success',
                'Activity logs retrieved successfully.',
                $lang
            );

            return $this->sendResponse(
                ActivityLogResource::collection($logs),
                $successMessage,
                200,
                [],
                $logs
            );
        }catch (\Exception $e) {
            Log::error('Activity log error', [
                'manager_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            $errorMessage = translateHelper::translateCached(
                'activity_logs_retrieved_failed',
                'Failed to retrieve activity logs.',
                $lang
            );

            return $this->sendError($errorMessage, [], 500);
        }
    }

    /**
     * Recent Transactions
     */
    public function getRecentTransactions(getRecentTransactionRequest $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        try {
            $validated = $request->validated();

            $page    = $validated['page'] ?? 1;
            $perPage = $validated['per_page'] ?? 10;


            $deposits = DB::table('deposit_requests')
                ->join('users', 'users.id', '=', 'deposit_requests.user_id')
                ->select(
                    'deposit_requests.id as source_id',
                    'deposit_requests.created_at as date',
                    DB::raw("CONCAT(users.first_name, ' ', users.last_name) as user"),
                    DB::raw("'deposit' as type"),
                    'deposit_requests.amount',
                    'deposit_requests.status'
                );

            $withdraws = DB::table('withdraw_requests')
                ->join('users', 'users.id', '=', 'withdraw_requests.user_id')
                ->select(
                    'withdraw_requests.id as source_id',
                    'withdraw_requests.created_at as date',
                    DB::raw("CONCAT(users.first_name, ' ', users.last_name) as user"),
                    DB::raw("'withdraw' as type"),
                    'withdraw_requests.amount',
                    'withdraw_requests.status'
                );

            $union = $deposits->unionAll($withdraws);

            $requests = DB::query()
                ->fromSub($union, 'requests')
                ->orderBy('date', 'desc')
                ->paginate($perPage);


            return $this->sendResponse(
                RecentTransactionResource::collection($requests),
                translateHelper::translateCached(
                    'recent_transactions_success',
                    'Recent transactions retrieved successfully.',
                    $lang
                ),
                200,
                [],
                $requests
            );

        }catch (\Exception $e) {
            Log::error('Recent transactions error', [
                'manager_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            $errorMessage = translateHelper::translateCached(
                'recent_transactions_failed',
                'Failed to retrieve recent transactions.',
                $lang
            );

            return $this->sendError($errorMessage, [], 500);
        }
    }

}
