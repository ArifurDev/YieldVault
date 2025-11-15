<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\KycDocument;
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

       return view('backend.dashboard', compact(
           'kycPending',
           'kycApproved',
           'kycRejected'
       ));
    }
}
