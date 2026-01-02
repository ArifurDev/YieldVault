<?php

namespace App\Http\Controllers\API\V1\Auth\User;

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
        $validated = $request->validated();

        try {
            // Generate a 4-digit random verification code
            $code = rand(1000, 9999);

            $email = $validated['email'];

            Cache::put("forgot_code_{$email}", $code, 600); // Store code for 10 minutes

            // send otp code this mail
            Mail::to($email)->send(new SendOtpMail($code));

            // success response
            return $this->sendResponse([], 'A verification code has been sent to your email address.'.$code, 200);
        }catch (\Exception $exception){
            Log::error("Forgot password OTP send fails:". $exception->getMessage());
            return $this->sendError("Forgot password OTP send fails:". $exception->getMessage(), [],500);
        }
    }

    /**
     * find otp code based on email
     */
    public function verifyResetCode(OtpVerifyRequest $request)
    {
        $validated = $request->validated();

        $code  = (int) $validated['otp'];
        $email = $validated['email'];

        $cachedCode = Cache::get("forgot_code_{$validated['email']}");

        if (!$cachedCode || $code !== $cachedCode) {
            return $this->sendError("The OTP you entered is incorrect or has expired. Please try again.", [], 400);
        }
        return $this->sendResponse([], 'OTP has been successfully verified.', 200);
    }

    /**
     * set new password
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $validated = $request->validated();

        $code = (int) $validated['otp'];
        $email = $validated['email'];

        $cachedCode = Cache::get("forgot_code_{$validated['email']}");


        if (!$cachedCode || $code !== $cachedCode) {
            return $this->sendError("The OTP you entered is incorrect or has expired. Please try again.", [], 400);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return $this->sendError('User not found.', [], 404);
        }
        $user->password = Hash::make($validated['new_password']);
        $user->save();

        Cache::forget("forgot_code_{$validated['email']}");

        return $this->sendResponse([], 'New password has been saved.', 200);
    }
}
