<?php

namespace App\Http\Controllers\Web\Backend;

use App\Enum\Investment\Status;
use App\Http\Controllers\Controller;
use App\Models\InvestmentLog;
use App\Models\UserInvestment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoiSystemController extends Controller
{
    /*
     * roi system dashboard
     */
    public function index()
    {
        return view('backend.roi.system.dashboard');
    }

    /**
     * ROI Dashboard data (AJAX)
     */
    public function data()
    {
        $today = now()->toDateString();

        return response()->json([
            'active_investments' => UserInvestment::where('status', Status::ACTIVE)->count(),
            'today_roi_generated' => InvestmentLog::whereDate('date', $today)->exists(),
            'today_roi_amount' => InvestmentLog::whereDate('date', $today)->sum('profit'),
            'failed_jobs' => DB::table('failed_jobs')->count(),
            'total_roi_paid' => UserInvestment::sum('earned_amount'),
            'last_roi_run' => InvestmentLog::latest('created_at')->value('created_at'),
        ]);
    }
}
