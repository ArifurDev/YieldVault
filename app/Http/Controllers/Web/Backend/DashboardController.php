<?php

namespace App\Http\Controllers\Web\Backend;

use App\Enum\Wallet\TransactionSource;
use App\Enum\Wallet\TransactionStatus;
use App\Http\Controllers\Controller;
use App\Models\KycDocument;
use App\Models\User;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     *  show data
     */
    public function index()
    {
        /* ================= KYC ================= */
        $kycPending   = KycDocument::where('status', 'Pending')->count();
        $kycApproved  = KycDocument::where('status', 'Approved')->count();
        $kycRejected  = KycDocument::where('status', 'Rejected')->count();

        /* ================= USERS ================= */
        $totalUsers = User::count();
        $referralUsers = User::whereNotNull('ref_id')->count();

        $referralProgress = $totalUsers > 0
            ? round(($referralUsers / $totalUsers) * 100)
            : 0;

        /* ================= USER-WISE FINANCE ================= */
        $topUsers = User::select(
            'users.id',
            'users.first_name',
            'users.last_name',
            DB::raw('SUM(investment_logs.profit) as total_profit')
        )
            ->join('user_investments', 'users.id', '=', 'user_investments.user_id')
            ->join('investment_logs', 'user_investments.id', '=', 'investment_logs.user_investment_id')
            ->groupBy('users.id', 'users.first_name', 'users.last_name')
            ->orderByDesc('total_profit')
            ->limit(5)
            ->get();

        /* ================= WALLET SUMMARY ================= */
        $totalDeposit = WalletTransaction::where('source', TransactionSource::DEPOSIT->value)
            ->where('status', TransactionStatus::APPROVED->value)
            ->sum('amount');

        $totalWithdraw = WalletTransaction::where('source', TransactionSource::WITHDRAW->value)
            ->where('status', TransactionStatus::PAID->value)
            ->sum('amount');

        $pendingTransactions = WalletTransaction::where('status', TransactionStatus::PENDING->value)->count();
        $failedTransactions  = WalletTransaction::where('status', TransactionStatus::REJECTED->value)->count();


        return view('backend.dashboard', compact(
               'kycPending',
             'kycApproved',
                       'kycRejected',
                       'totalUsers',
                       'referralUsers',
                       'referralProgress',

                        'topUsers',

                        'totalDeposit',
                        'totalWithdraw',

                        'pendingTransactions',
                        'failedTransactions'
       ));
    }

    /**
     * user growth
     */
    public function userGrowth(Request $request)
    {
        $filter = $request->get('filter', 'week');

        $query = User::query();

        switch ($filter) {
            case 'week':
                $query->where('created_at', '>=', Carbon::now()->startOfWeek());
                break;

            case 'month':
                $query->where('created_at', '>=', Carbon::now()->subMonth());
                break;

            case 'year':
                $query->where('created_at', '>=', Carbon::now()->startOfYear());
                break;

            case 'all':
            default:
                // no filter
                break;
        }

        $data = $query->select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as total')
        )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'labels' => $data->pluck('date')->map(fn($d) => Carbon::parse($d)->format('d M')),
            'values' => $data->pluck('total'),
        ]);
    }


    /**
     * get recent Transactions
     */
    public function recentTransactions()
    {
        $transactions = WalletTransaction::with(['fromUser', 'toUser'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($trx) {
                return [
                    'id'     => $trx->id,
                    'txn_id' => "TXN-". str_pad( $trx->id,6,'0',STR_PAD_LEFT),
                    'from'   => $trx->fromUser?->first_name . ' ' . $trx->fromUser?->last_name ?? 'System',
                    'to'     => $trx->toUser?->first_name . ' ' . $trx->toUser?->last_name ?? 'System',
                    'amount' => number_format($trx->amount, 2),
                    'type'   => ucfirst($trx->type),
                    'source' => ucwords(str_replace('_', ' ', $trx->source)),
                    'status' => ucfirst($trx->status),
                    'date'   => $trx->created_at->format('d M Y, h:i A'),
                ];
            });

        return response()->json($transactions);
    }
}
