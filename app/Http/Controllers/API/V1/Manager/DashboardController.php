<?php

namespace App\Http\Controllers\API\V1\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Manager\ActivityLogRequest;
use App\Http\Resources\Api\V1\Manager\ActivityLogResource;
use App\Models\ActiveLog;
use App\Models\DepositRequest;
use App\Models\KycDocument;
use App\Models\User;
use App\Models\WithdrawRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    use ApiResponse;

    /**
     * Manager Overview -> Quick Stats
     */
    public function summary()
    {
        try {
            $manager = auth()->user();

            // Role check
            if ($manager->role->value !== 'manager') {
                return $this->sendError('Unauthorized access', [], 403);
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


            // Response

            return $this->sendResponse([
                'pending_deposits' => $pendingDeposits,
                'pending_withdrawals' => $pendingWithdrawals,
                'pending_kyc' => [
                    'count' => $pendingKyc,
                    'label' => 'Need Review',
                ],
                'assigned_investors' => [
                    'count' => $assignedInvestors,
                    'label' => 'Active',
                ],
            ], 'Manager dashboard summary retrieved successfully');

        } catch (\Exception $e) {

            Log::error('Manager dashboard summary error', [
                'manager_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return $this->sendError('Failed to retrieve dashboard summary',  [], 500);
        }
    }


    /**
     * Activity Logs
     */
    public function ActivityLogs(ActivityLogRequest $request)
    {
        $validated = $request->validated();
        $page    = $validated['page'] ?? 1;
        $perPage = $validated['per_page'] ?? 10;

        $sortBy = 'created_at';
        $order  = $validated['order']   ?? 'desc';

        $query = ActiveLog::where('actor_id',auth()->user()->id)
            ->orderBy($sortBy, $order);

        $logs = $query->paginate($perPage, ['*'], 'page', $page);

        return $this->sendResponse(  ActivityLogResource::collection($logs),'Activity logs retrieved successfully.', 200,[],$logs);
    }
}
