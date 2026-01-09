<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\KycDocument;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     *  show data
     */
    public function index()
    {
        $kycPending   = KycDocument::where('status', 'Pending')->count();
        $kycApproved  = KycDocument::where('status', 'Approved')->count();
        $kycRejected  = KycDocument::where('status', 'Rejected')->count();

        // User counts
        $totalUsers = User::count();

        // Users who joined via referral link
        $referralUsers = User::whereNotNull('ref_id')->count();

        // Referral percentage (for progress bar)
        $referralProgress = $totalUsers > 0
            ? round(($referralUsers / $totalUsers) * 100)
            : 0;

       return view('backend.dashboard', compact(
               'kycPending',
             'kycApproved',
                       'kycRejected',
                       'totalUsers',
                       'referralUsers',
                       'referralProgress',
       ));
    }
}
