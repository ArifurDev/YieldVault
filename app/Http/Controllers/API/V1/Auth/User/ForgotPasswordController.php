<?php

namespace App\Http\Controllers\API\V1\Auth\User;

use App\Helpers\translateHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ForgotPassword\ForgotRequest;
use App\Http\Requests\Api\V1\ForgotPassword\OtpVerifyRequest;
use App\Http\Requests\Api\V1\ForgotPassword\ResetPasswordRequest;
use App\Mail\Api\V1\SendOtpMail;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    use ApiResponse;

    /**
     * find mail and send otp code
     */
    public function sendResetCode(ForgotRequest $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        $validated = $request->validated();

        try {
            // Generate a 4-digit random verification code
            $code = rand(1000, 9999);

            $email = $validated['email'];

            Cache::put("forgot_code_{$email}", $code, 600); // Store code for 10 minutes

            // send otp code this mail
            Mail::to($email)->send(new SendOtpMail($code));

            $message = translateHelper::translateCached(
                'forgot_password_code_sent',
                'A verification code has been sent to your email address.',
                $lang
            );

            return $this->sendResponse([], $message, 200);
        }catch (\Exception $exception){
            Log::error("Forgot password OTP send fails:". $exception->getMessage());
            $message = translateHelper::translateCached(
                'forgot_password_send_failed',
                'Failed to send verification code. Please try again later.',
                $lang
            );

            return $this->sendError($message, [], 500);
        }
    }

    /**
     * find otp code based on email
     */
    public function verifyResetCode(OtpVerifyRequest $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        $validated = $request->validated();

        $code  = (int) $validated['otp'];
        $email = $validated['email'];

        $cachedCode = Cache::get("forgot_code_{$validated['email']}");

        if (!$cachedCode || $code !== $cachedCode) {
            $message = translateHelper::translateCached(
                'forgot_password_invalid_code',
                'The verification code is invalid or has expired. Please try again.',
                $lang
            );

            return $this->sendError($message, [], 400);
        }
        // OTP verified successfully
        $message = translateHelper::translateCached(
            'forgot_password_code_verified',
            'Verification code has been successfully verified.',
            $lang
        );

        return $this->sendResponse([], $message, 200);
    }

    /**
     * set new password
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        $validated = $request->validated();

        $code = (int) $validated['otp'];
        $email = $validated['email'];

        $cachedCode = Cache::get("forgot_code_{$validated['email']}");

        if (!$cachedCode || $code !== $cachedCode) {
            $message = translateHelper::translateCached(
                'forgot_password_invalid_code',
                'The verification code is invalid or has expired. Please try again.',
                $lang
            );

            return $this->sendError($message, [], 400);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $message = translateHelper::translateCached(
                'forgot_password_user_not_found',
                'User account not found.',
                $lang
            );

            return $this->sendError($message, [], 404);
        }
        $user->password = Hash::make($validated['new_password']);
        $user->save();

        Cache::forget("forgot_code_{$validated['email']}");

        $message = translateHelper::translateCached(
            'forgot_password_reset_success',
            'Your new password has been saved successfully.',
            $lang
        );

        return $this->sendResponse([], $message, 200);
    }
}
