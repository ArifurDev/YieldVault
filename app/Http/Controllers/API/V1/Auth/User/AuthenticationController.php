<?php

namespace App\Http\Controllers\API\V1\Auth\User;

use App\Enum\ActivityLog\Action;
use App\Enum\ActivityLog\Level;
use App\Enum\ActivityLog\Type;
use App\Enum\Role;
use App\Enum\Wallet\TransactionSource;
use App\Enum\Wallet\TransactionStatus;
use App\Enum\Wallet\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\SignupRequest;
use App\Http\Requests\Api\V1\TwoFactor\LoginTwoFactorVerifyRequest;
use App\Http\Resources\Api\V1\Auth\UserResource;
use App\Models\ActiveLog;
use App\Models\DailyTask;
use App\Models\DailyTaskLog;
use App\Models\ReferralLink;
use App\Models\ReferralSetting;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\DataEncryptionService;
use App\Services\TwoFactorOtpService;
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

            Wallet::create([
                'user_id' => $user->id,
                'balance' => 0
            ]);

            // check ref and xp add and check
            $this->checkRef($validated,$request,$user);

            $token = auth('api')->login($user);
            //  ACTIVITY LOG (REGISTER)
            ActiveLog::create([
                'actor_id'       => $user->id,
                'target_user_id' => $user->id,
                'type'           => Type::REGISTER,
                'action'         => Action::REGISTERED,
                'message'        => "User {$user->first_name} registered successfully",
                'level'          => Level::SUCCESS,
            ]);
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
    public function login(LoginRequest $request,TwoFactorOtpService $otpService)
    {
        $validatedData = $request->validated();
        try {

            // Attempt to log the user in
            if (!$token = JWTAuth::attempt($validatedData)) {
                return $this->sendError('Invalid credentials. Please check your email and password.', [], 401);
            }

            $user = Auth::user();

            /**
             * If 2FA is ENABLED
             * → send OTP
             * → return TEMP TOKEN
             */
            if ($user->hasTwoFactorEnabled()) {

                // send OTP
                $otpService->generate($user);

                // TEMP token (short-lived, 2FA pending)
                $tempToken = JWTAuth::claims([
                    'two_factor' => true, // flag for middleware
                    'type'       => '2fa',
                ])->fromUser($user);

                $data = [
                    'two_factor_required' => true,
                    'temp_token' => $tempToken,
                ];

                return $this->sendResponse($data,'Verification code sent to your email.');
            }

            //  ACTIVITY LOG (LOGIN)
            ActiveLog::create([
                'actor_id'       => $user->id,
                'target_user_id' => $user->id,
                'type'           => Type::LOGIN,
                'action'         => Action::LOGIN,
                'message'        => "User {$user->first_name} logged in",
                'level'          => Level::INFO,
            ]);

            return $this->sendResponse(new UserResource($user), 'User logged in successfully.', 200, $token);
        }catch (\Exception $exception){
            Log::error("Failed to login:".$exception->getMessage());
            return $this->sendError("Failed to login:".$exception->getMessage(),[],500);
        }
    }

    /**
     * VERIFY LOGIN 2FA
     * POST /auth/login/2fa/verify
     */
    public function verifyTwoFactor(LoginTwoFactorVerifyRequest $request, TwoFactorOtpService $otpService)
    {
        try {
            $validated = $request->validated();

            // TEMP token user
            $user = Auth::user();

            /**
             * Ensure this token is a TEMP 2FA token
             */
            $payload = JWTAuth::parseToken()->getPayload();

            if ($payload->get('two_factor') !== true) {
                return $this->sendError("Invalid verification session.", [], 403);
            }

            // OTP verify
            if (! $otpService->verify($user, $request->otp)) {
                $user->incrementTwoFactorFailures();
                return $this->sendError("Invalid or expired verification code.", [], 403);
            }

            // Reset failure count
            $user->resetTwoFactorFailures();

            // OTP correct → issue REAL JWT
            $realToken = JWTAuth::fromUser($user);

            return $this->sendResponse(new UserResource($user),'User verification successful.',200,$realToken);
        }catch (\Exception $e){
            Log::error("Failed to verify two factor authentication",[
                'userId' => $user->id,
                'exception' => $e->getMessage(),
            ]);
            return $this->sendError("Failed to verify two factor authentication.", [], 403);
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

//        /**
//         * CASE B — Normal Referral (NO daily invite task)
//         * ----------------------------------------
//         * Example: Give 10 XP for normal referrals (optional)
//         */
//        if ($refToken){
//            $user = User::where('ref_code', $refToken)->first();
//            if(!$user){
//                return true;
//            }
//            $settings = ReferralSetting::current();
//
//            $normalRewardXp = 10; // change as needed
//
//            DailyTaskLog::create([
//                'user_id'       => $user->id,
//                'task_id'       => null,
//                'daily_task_id' => null,
//                'xp_rewarded'   => $settings ? $settings->referral_xp : $normalRewardXp,
//            ]);
//
//            // seeting ->referral_amount ? .. : 1
//            WalletTransaction::create([
//
//            ]);
//
//            return true;
//        }

        /**
         * CASE B — Normal Referral (NO daily invite task)
         * ----------------------------------------
         * Give XP + Wallet reward for referral
         */
        if ($refToken) {

            // Referrer (who will get reward)
            $referrer = User::with('wallet')
                ->where('ref_code', $refToken)
                ->first();

            if (!$referrer) {
                return true;
            }

            // Admin (source of funds)
            $admin = User::where('role', 'admin')->firstOrFail();

            // Settings
            $settings = ReferralSetting::current();

            $rewardXp     = $settings->referral_xp ?? 10;
            $rewardAmount = $settings->referral_amount ?? 1;

            try {

                DB::transaction(function () use ($referrer, $admin, $rewardXp, $rewardAmount) {

                    /**
                     *  Ensure referrer wallet exists
                     */
                    $wallet = $referrer->wallet()->firstOrCreate([
                        'user_id' => $referrer->id,
                    ]);

                    /**
                     * XP Reward Log
                     */
                    DailyTaskLog::create([
                        'user_id'       => $referrer->id,
                        'task_id'       => null,
                        'daily_task_id' => null,
                        'xp_rewarded'   => $rewardXp,
                    ]);

                    /**
                     * CREDIT Wallet Balance
                     */
                    $wallet->increment('balance', $rewardAmount);

                    /**
                     *  Wallet Transaction Log
                     */
                    WalletTransaction::create([
                        'from_user_id' => $admin->id, // admin as fund source
                        'to_user_id'   => $referrer->id,
                        'amount'       => $rewardAmount,
                        'type'         => TransactionType::CREDIT->value,
                        'source'       => TransactionSource::REFERRAL_REWARD->value,
                        'status'       => TransactionStatus::APPROVED->value,
                    ]);
                });

            } catch (\Throwable $e) {
                report($e); // log but don't break signup flow
            }

            return true;
        }

    }

}
