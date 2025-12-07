<?php

namespace App\Http\Controllers\API\V1\Auth\User;

use App\Enum\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\SignupRequest;
use App\Http\Resources\Api\V1\Auth\UserResource;
use App\Models\DailyTask;
use App\Models\DailyTaskLog;
use App\Models\ReferralLink;
use App\Models\User;
use App\Services\DataEncryptionService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthenticationController extends Controller
{
    use ApiResponse;
    /**
     * user registration
     */
    public function signup(SignupRequest $request)
    {
        $validated = $request->validated();
    
        DB::beginTransaction();
        try {
            $jwt = app(DataEncryptionService::class);

            // store data in database
            $user = User::create([
                'first_name' => $validated['first_name'],
                'last_name'  => $validated['last_name'],
                'email'      => $validated['email'],
                'password'   => Hash::make($validated['password']),
                'role'       => Role::USER->value,
            ]);
            $user->update([
                'ref_code' => $jwt->encrypt(strtoupper(Str::random(8).$user->id)), // Own referral code
            ]);

            // check ref and xp add and check
            $this->checkRef($validated,$request,$user);

            $token = auth('api')->login($user);
            DB::commit();
            // success response
            return $this->sendResponse(new UserResource($user),'User registration successful.',200,$token);
        }catch (\Exception $exception){
            DB::rollBack();
            Log::error("Failed to create account:".$exception->getMessage());
            return $this->sendError("Failed to create account:".$exception->getMessage(),[],500);
        }
    }
    /**
     * signing
     */
    public function login(LoginRequest $request)
    {
        $validatedData = $request->validated();
        try {

            // Attempt to log the user in
            if (!$token = JWTAuth::attempt($validatedData)) {
                return $this->sendError('Invalid credentials. Please check your email and password.', [], 401);
            }

            $user = Auth::user();

            return $this->sendResponse(new UserResource($user), 'User logged in successfully.', 200, $token);
        }catch (\Exception $exception){
            Log::error("Failed to login:".$exception->getMessage());
            return $this->sendError("Failed to login:".$exception->getMessage(),[],500);
        }
    }

    private function checkRef($data,$request,$user)
    {
        $jwt = app(DataEncryptionService::class);

        $refToken = $request->ref ?? null;  // from ?ref=token
        $taskId  = $request->task ? $jwt->decrypt($data['task']) : null; // from ?task=encrypted

        // daily task invitation
        if($refToken && $taskId){
            $refLink = ReferralLink::where('unique_token', $refToken)
                ->where('expires_at', '>', now())
                ->first();

            if($refLink){

                if ($refLink->user_id != $user->id) {
                    // Save referred_by
                    $user->ref_id = $refLink->user_id;
                    $user->save();

                    // Track referral click/register
                    $refLink->meta = json_encode([
                        'signup_ip'     => $request->ip(),
                        'signup_agent'  => $request->userAgent(),
                        'signup_at'     => now(),
                    ]);
                    $refLink->save();

                    // REWARD XP TO REFERRER (if task found)
                    $today = now()->format('Y-m-d');
                    $dailyTask = DailyTask::with('task')
                        ->where('task_id', $taskId)
                        ->where('date', $today)
                        ->first();

                    if ($dailyTask && $dailyTask->task->type->value === 'invite') {
                        DailyTaskLog::firstOrCreate([
                            'user_id'       => $refLink->user_id,
                            'task_id'       => $dailyTask->task_id,
                            'daily_task_id' => $dailyTask->id,
                        ], [
                            'xp_rewarded'   => $dailyTask->task->xp,
                        ]);
                    }
                    return true;
                }
                return true;
            }
            return true;
        }

        /**
         * CASE B — Normal Referral (NO daily invite task)
         * ----------------------------------------
         * Example: Give 10 XP for normal referrals (optional)
         */
        if ($refToken){
            $user = User::where('ref_code', $refToken)->first();
            if(!$user){
                return true;
            }

            $normalRewardXp = 10; // change as needed

            DailyTaskLog::create([
                'user_id'       => $user->id,
                'task_id'       => null,
                'daily_task_id' => null,
                'xp_rewarded'   => $normalRewardXp,
            ]);

            return true;
        }
    }

}
