<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\ReferralLink;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    use ApiResponse;
    public function handle($token, Request $request)
    {
        $link = ReferralLink::where('unique_token', $token)->first();

        if (!$link) {
            return redirect(config('app.frontend_url'));
        }

        if ($link->isExpired()) {
            return redirect('https://yourapp.com/referral-expired');
        }

        // Prevent self-referral
        if (auth()->check() && auth()->id() === $link->user_id) {
            return redirect('https://yourapp.com');
        }


        // Save tracking meta
        $link->meta = [
            'ip'        => $request->ip(),
            'browser'   => $request->userAgent(),
            'platform'  => PHP_OS,
            'clicked_at'=> now()
        ];
        $link->save();
//
//        // Store referral code in session for signup
//        session(['ref_token' => $token]);

        // Redirect to App or Website signup
        return redirect(config('app.frontend_url'). "?ref=" .$link->ref_code);
    }



}
