<?php

namespace App\Http\Controllers\Web\Backend;

use App\Helpers\helpers;
use App\Http\Controllers\Controller;
use App\Models\InvestmentLog;
use App\Models\UserInvestment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
class RoiListController extends Controller
{
    /**
     * ROI List page
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = UserInvestment::with('user')->latest();

            return DataTables::of($data)

                ->addColumn('user', function ($row) {
                    $fullName = $row->user->first_name . ' ' . $row->user->last_name;

                    return '
                        <div class="d-flex align-items-center">

                            <div class="d-flex flex-column">
                                <span class="text-gray-800 fw-bold">' . $fullName . '</span>
                                <span class="text-muted">' . $row->user->email . '</span>
                            </div>
                        </div>';
                })

                ->addColumn('amount', fn ($row) => number_format($row->amount, 2))

                ->addColumn('roi', fn ($row) => $row->roi_percent . '%')

                ->addColumn('earned', fn ($row) => number_format($row->earned_amount, 2))

                ->addColumn('expected', function ($row) {
                    return number_format(($row->amount * $row->roi_percent) / 100, 2);
                })

                ->addColumn('progress', function ($row) {
                    $expected = ($row->amount * $row->roi_percent) / 100;
                    $progress = $expected > 0
                        ? round(($row->earned_amount / $expected) * 100, 2)
                        : 0;

                    return '
                        <div class="progress h-6px w-100">
                            <div class="progress-bar bg-success" style="width: ' . $progress . '%"></div>
                        </div>
                        <span class="text-muted fs-8">' . $progress . '%</span>';
                })

                ->addColumn('status', function ($row) {

                    $status = $row->status?->value ?? 'pending';

                    $badge = match ($status) {
                        'active' => 'badge-light-success',
                        'completed' => 'badge-light-primary',
                        'cancelled' => 'badge-light-danger',
                        default => 'badge-light-warning',
                    };

                    return '<span class="badge ' . $badge . '">' . ucfirst($status) . '</span>';
                })

                ->addColumn('actions', function ($row) {
                    $logUrl = route('roi.logs', $row->id);

                    return '
                        <div class="text-end">
                            <a href="' . $logUrl . '" class="btn btn-sm btn-light-primary">
                                View Logs
                            </a>
                        </div>';
                })

                ->rawColumns(['user', 'progress', 'status', 'actions'])
                ->make(true);
        }

        return view('backend.roi.list.list');
    }

    /**
     * ROI LOGS PAGE (only view)
     */
    public function logs(UserInvestment $investment)
    {
        return view('backend.roi.list.logs', compact('investment'));
    }

    /**
     * ROI LOGS DATATABLE (ajax)
     */
    public function logsData(UserInvestment $investment)
    {
        $data = InvestmentLog::where('user_investment_id', $investment->id)->latest();

        return DataTables::of($data)

            ->addColumn('date', fn ($row) =>
            Carbon::parse($row->date)->format('d M Y')
            )

            ->addColumn('profit', fn ($row) =>
            number_format($row->profit, 2)
            )

            ->addColumn('status', fn () =>
            '<span class="badge badge-light-success">Credited</span>'
            )

            ->addColumn('created_at', fn ($row) =>
            Carbon::parse($row->created_at)->format('d M Y, h:i A')
            )

            ->rawColumns(['status'])
            ->make(true);
    }
}
