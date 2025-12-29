<?php

namespace App\Http\Controllers\Web\Backend;

use App\Enum\Role;
use App\Enum\Wallet\TransactionSource;
use App\Enum\Wallet\TransactionStatus;
use App\Enum\Wallet\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;
use Yajra\DataTables\Facades\DataTables;

class WalletController extends Controller
{
    /**
     * Wallet list
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $query = Wallet::query()
                ->join('users', 'users.id', '=', 'wallets.user_id')
                ->select(
                    'wallets.*',
                    'users.first_name',
                    'users.email',
                    'users.role',
                    'users.kyc_status'
                );

            return DataTables::of($query)

                // Search by name & email
                ->filterColumn('user', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('users.first_name', 'like', "%{$keyword}%")
                            ->orWhere('users.email', 'like', "%{$keyword}%");
                    });
                })

                //  Search by role
                ->filterColumn('role', function ($query, $keyword) {
                    $query->where('users.role', 'like', "%{$keyword}%");
                })

                // Search by status text
                ->filterColumn('status', function ($query, $keyword) {
                    $keyword = strtolower($keyword);

                    if ($keyword === 'active') {
                        $query->where('users.kyc_status', 'approved');
                    }

                    if (in_array($keyword, ['blocked', 'pending'])) {
                        $query->where('users.kyc_status', '!=', 'approved');
                    }
                })

                // User column
                ->addColumn('user', function ($row) {
                    return '
                    <div class="d-flex flex-column">
                        <span class="fw-bold text-gray-800">'.$row->first_name.'</span>
                        <small class="text-muted">'.$row->email.'</small>
                    </div>
                ';
                })

                //  Role badge
                ->addColumn('role', function ($row) {
                    return '<span class="badge badge-light-primary">'
                        .ucfirst(str_replace('_', ' ', $row->role)).
                        '</span>';
                })

                //  Balance
                ->addColumn('balance', fn ($row) =>
                    '<span class="fw-bold text-success">$ '.number_format($row->balance, 2).'</span>'
                )

                // Status (from kyc_status)
                ->addColumn('status', function ($row) {

                    if ($row->user->role === Role::ADMIN) {
                        return '<span class="badge badge-light-success">Active</span>';
                    }

                    return match ($row->kyc_status) {
                        'approved' => '<span class="badge badge-light-success">Active</span>',
                        'pending'  => '<span class="badge badge-light-warning">Pending</span>',
                        default    => '<span class="badge badge-light-danger">Blocked</span>',
                    };
                })

                // Action
                ->addColumn('actions', function ($row) {
                    return '
                    <a href="'.route('wallet.show', $row->user_id).'"
                       class="btn btn-light btn-sm">
                       View
                    </a>
                ';
                })

                ->rawColumns(['user', 'role', 'balance', 'status', 'actions'])
                ->make(true);
        }

        return view('backend.layout.wallet.index');
    }


    /**
     * Show wallet transaction history
     */
    public function show(Request $request, $userID)
    {
        $user = User::findOrFail($userID);

        if ($request->ajax()) {

            $query = WalletTransaction::query()
                ->leftJoin('users as from_users', 'from_users.id', '=', 'wallet_transactions.from_user_id')
                ->leftJoin('users as to_users', 'to_users.id', '=', 'wallet_transactions.to_user_id')
                ->where(function ($q) use ($userID) {
                    $q->where('wallet_transactions.from_user_id', $userID)
                        ->orWhere('wallet_transactions.to_user_id', $userID);
                })
                ->where('wallet_transactions.status', 'approved')
                ->select([
                    'wallet_transactions.*',
                    'from_users.first_name as from_name',
                    'to_users.first_name as to_name',
                ]);

            return DataTables::of($query)

                // Amount
                ->addColumn('amount', fn ($row) =>
                    '<span class="fw-bold">$ '.number_format($row->amount, 2).'</span>'
                )

                // From
                ->addColumn('from', fn ($row) =>
                    $row->from_name ?? '<span class="text-muted">System</span>'
                )

                // To
                ->addColumn('to', fn ($row) =>
                    $row->to_name ?? '<span class="text-muted">System</span>'
                )

                // Credit / Debit
                ->addColumn('flow', function ($row) use ($userID) {
                    return $row->from_user_id == $userID
                        ? '<span class="badge badge-light-danger">Debit</span>'
                        : '<span class="badge badge-light-success">Credit</span>';
                })

                // Source
                ->addColumn('source', fn ($row) =>
                    '<span class="badge badge-light-primary">'
                    .ucfirst(str_replace('_', ' ', $row->source)).
                    '</span>'
                )

                // Date
                ->addColumn('date', fn ($row) =>
                $row->created_at->format('Y-m-d H:i')
                )

                ->rawColumns(['amount', 'from', 'to', 'flow', 'source'])
                ->make(true);
        }

        return view('backend.layout.wallet.transactions', compact('user'));
    }



    /**
     * Admin Wallet Action Controller (Give / Take)
     */
    public function adminWalletAction(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'amount'  => ['required', 'numeric', 'min:0.01'],
            'action'  => ['required', new Enum(TransactionType::class)],
        ]);

        try {
            $admin = auth()->user();
            $user  = User::with('wallet')->findOrFail($request->user_id);

            DB::transaction(function () use ($request, $user, $admin) {

                // Ensure wallet exists
                $wallet = $user->wallet()->firstOrCreate([
                    'user_id' => $user->id,
                ]);

                // DEBIT (Take Money)
                if ($request->action === TransactionType::DEBIT->value) {

                    if ($wallet->balance < $request->amount) {
                        throw new \Exception('Insufficient balance');
                    }

                    $wallet->decrement('balance', $request->amount);

                    WalletTransaction::create([
                        'from_user_id' => $user->id,
                        'to_user_id'   => $admin->id,
                        'amount'       => $request->amount,
                        'type'         => TransactionType::DEBIT->value,
                        'source'       => TransactionSource::ADMIN_FUND->value,
                        'status'       => TransactionStatus::APPROVED->value,
                    ]);
                }

                // CREDIT (Give Money)
                if ($request->action === TransactionType::CREDIT->value) {

                    $wallet->increment('balance', $request->amount);

                    WalletTransaction::create([
                        'from_user_id' => $admin->id,
                        'to_user_id'   => $user->id,
                        'amount'       => $request->amount,
                        'type'         => TransactionType::CREDIT->value,
                        'source'       => TransactionSource::ADMIN_FUND->value,
                        'status'       => TransactionStatus::APPROVED->value,
                    ]);
                }
            });

            return response()->json([
                'status'  => true,
                'message' => 'Wallet updated successfully',
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage() ?: 'Something went wrong',
            ], 422);
        }
    }


}
