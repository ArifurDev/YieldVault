<?php

namespace App\Http\Controllers\API\V1\Manager;

use App\Http\Controllers\Controller;
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
}
