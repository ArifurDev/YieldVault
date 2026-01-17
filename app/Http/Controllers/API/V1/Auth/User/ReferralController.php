<?php

namespace App\Http\Controllers\API\V1\Auth\User;

use App\Helpers\helpers;
use App\Helpers\translateHelper;
use App\Http\Controllers\Controller;
use App\Models\DailyTask;
use App\Models\DailyTaskLog;
use App\Services\DataEncryptionService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReferralController extends Controller
{
    use ApiResponse;
    /**
     * get auth referral
     */
    public function index(Request $request)
    {
         $lang = $request->header('Accept-Language', 'en');
        try {
            $jwt = app(DataEncryptionService::class);

            $ref = DailyTaskLog::with('user')->whereUserId(auth()->id())
                ->whereNull('task_id')
                ->whereNull('daily_task_id')
                ->get();

            $user = auth()->user();
            // if not found that time generate code
            if (!$user->ref_code)
            {
                $user->ref_code = $jwt->encrypt(strtoupper(Str::random(8).$user->id));
                $user->save();
            }

            $response = [
                'invited'       => $ref->count(),
                'earn_program'  => $ref->sum('xp_rewarded'),
                'ref_code'      => $user && $user->ref_code
                    ? $jwt->decrypt($user->ref_code)
                    : null,
                'ref_url'       => $user && $user->ref_code
                    ? helpers::getFrontendUrl()
                       . '/user-auth/user-signup'
                       . '?ref=' . $jwt->decrypt($user->ref_code)
                    : null,
            ];

             $message = translateHelper::translateCached(
                'referral_list_success',
                'Referral list retrieved successfully.',
                $lang
            );

            return $this->sendResponse($response, $message);
        }catch (\Exception $e){
            $message = translateHelper::translateCached(
                'referral_list_failed',
                'Failed to retrieve referral data. Please try again later.',
                $lang
            );

            return $this->sendError($message, [], 500);
        }
    }

}
