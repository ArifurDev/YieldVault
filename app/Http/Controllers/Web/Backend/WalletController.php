<?php

namespace App\Http\Controllers\Web\Backend;

use App\Enum\Role;
use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class WalletController extends Controller
{
    /**
     * Wallet list
     */
//    public function index(Request $request)
//    {
//        if ($request->ajax()) {
//
//            $data = Wallet::with('user')->latest();
//
//
//            return DataTables::of($data)
//
//                ->addColumn('user', function ($row) {
//                    return '
//                        <div class="d-flex flex-column">
//                            <span class="fw-bold text-gray-800">'.$row->user->first_name.'</span>
//                            <small class="text-muted">'.$row->user->email.'</small>
//                        </div>
//                    ';
//                })
//
//                ->addColumn('role', fn($row) =>
//                    '<span class="badge badge-light-primary">'.
//                    ucfirst($row->user->role->value).
//                    '</span>'
//                )
//
//                ->addColumn('balance', fn($row) =>
//                    '<span class="fw-bold text-success">$ '.number_format($row->balance, 2).'</span>'
//                )
//
//                ->addColumn('status', function ($row) {
//
//                    if ($row->user->role === Role::ADMIN) {
//                        return '<span class="badge badge-light-success">Active</span>';
//                    }
//
//                    return $row->user->is_active
//                        ? '<span class="badge badge-light-success">Active</span>'
//                        : '<span class="badge badge-light-danger">Blocked</span>';
//                })
//
//                ->addColumn('actions', function ($row) {
//                    return '
//                        <a href="'.route('wallet.show', $row->user_id).'"
//                           class="btn btn-light btn-sm">
//                           View
//                        </a>
//                    ';
//                })
//
//                ->rawColumns(['user', 'role', 'balance', 'status', 'actions'])
//                ->make(true);
//        }
//
//        return view('backend.layout.wallet.index');
//    }

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



}
