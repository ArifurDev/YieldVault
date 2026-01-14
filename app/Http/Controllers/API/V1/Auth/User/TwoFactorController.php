<?php

namespace App\Http\Controllers\API\V1\Auth\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\TwoFactor\DisableTwoFactorRequest;
use App\Http\Requests\Api\V1\TwoFactor\EnableTwoFactorRequest;
use App\Http\Requests\Api\V1\TwoFactor\VerifyTwoFactorRequest;
use App\Services\TwoFactorOtpService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class TwoFactorController extends Controller
{
    use ApiResponse;

    /**
     * Get 2FA status
     */
    public function getTwoFactorStatus()
    {
        try {
            // get current auth
            $user = auth()->user();

            $maskedEmail = $user->twoFactorEmail() ? $this->maskEmail($user->twoFactorEmail()) : null;

            $data = [
                'enabled' => $user->hasTwoFactorEnabled(),
                'email'   => $maskedEmail,
            ];

            return $this->sendResponse($data,"Two Factor Authentication Status retrieved successfully");
        }catch (\Exception $e){
            Log::error("Get Two Factor Status failed",[
                'userId' => $user->id,
                'message' => $e->getMessage(),
            ]);
            return $this->sendError("Get Two Factor Status failed",[],500);
        }
    }

    /**
     *  ------------- Request OTP to ENABLE 2FA -------------
     *  Two-Factor Enable
     *  1. Enter email
     *  2. Enter current password
     */
    public function enableTwoFactor(EnableTwoFactorRequest $request, TwoFactorOtpService $otpService)
    {
        try {
            // validation
            $validated = $request->validated();

            // get current auth
            $user = auth()->user();

            // verify current password
            if (!Hash::check($request->input('current_password'), $user->password)) {
                return $this->sendError('Current password is incorrect.', [], 422);
            }

            // Already enabled check
            if ($user->hasTwoFactorEnabled()) {
                return $this->sendError('Two-factor authentication already enabled.',[],422);
            }

            if ($validated['email']) {
                $user->two_factor_email = $request->email;
                $user->save();
            }
            // OTP send
            $otpService->generate($user);

            return $this->sendResponse([],'Verification code sent to your email.');
        }catch (\Exception $e){
            Log::error("EnableTwoFactor Exception: ",[
                'userId' => $user->id,
                'message' => $e->getMessage(),
            ]);
            return $this->sendError("Failed to send verification code.", [], 422);
        }
    }

    /**
     *  ------------- Request OTP to Disable 2FA -------------
     *  Two-Factor Disable
     *  1. Enter email
     *  2. Enter current password
     */
    public function disableTwoFactor(DisableTwoFactorRequest $request, TwoFactorOtpService $otpService)
    {
        try {
            $validated = $request->validated();

            // get current auth
            $user = auth()->user();

            if (! $user->hasTwoFactorEnabled()) {
                return $this->sendError('Two-factor authentication is not enabled.',[],422);
            }

            if (! Hash::check($validated['password'], $user->password)) {
                return $this->sendError('Incorrect password.',[],422);
            }

            // OTP send
            $otpService->generate($user);

            return $this->sendResponse([], 'Verification code sent to your email.');
        }catch (\Exception $e){
            Log::error("DisableTwoFactor Exception: ",[
                'userId' => $user->id,
                'message' => $e->getMessage(),
            ]);
            return $this->sendError("Failed to send verification code.", [], 422);
        }
    }


    /**
     * Verify OTP (enable / disable / login)
     */
    public function verifyTwoFactor(VerifyTwoFactorRequest $request, TwoFactorOtpService $otpService)
    {
        try {
            $validated = $request->validated();

            $action = $validated['action'];

            // get current auth
            $user = $request->user();

            // OTP verify
            if (! $otpService->verify($user, $request->otp)) {
                $user->incrementTwoFactorFailures();
                return $this->sendError('Invalid or expired verification code.',[],422);
            }

            // Reset failure count
            $user->resetTwoFactorFailures();

            // ACTION HANDLING
            if ($action === 'enable') {

                if ($user->hasTwoFactorEnabled()) {
                    return $this->sendError('Two-factor authentication already enabled.',[],422);
                }

                $user->update([
                    'two_factor_enabled'     => true,
                    'two_factor_verified_at' => now(),
                ]);

                // notify


                return $this->sendResponse([
                    'two_factor_enabled'    => true,
                ],
                    'Two-factor authentication enabled successfully.');
            }

            if ($request->action === 'disable') {

                if (! $user->hasTwoFactorEnabled()) {
                    return $this->sendError('Two-factor authentication already disabled.',[],422);
                }

                $user->update([
                    'two_factor_enabled'     => false,
                    'two_factor_verified_at' => null,
                    'two_factor_email'       => null,
                ]);

                return $this->sendResponse([
                    'two_factor_enabled'    => false,
                ],
                    'Two-factor authentication disabled successfully.');
            }
        }catch (\Exception $e){
            Log::error("VerifyTwoFactor Exception: ",[
                'userId' => $user->id,
                'message' => $e->getMessage(),
            ]);
            return $this->sendError("Failed to verify two-factor authentication.", [], 422);
        }
    }

    // mask email
    private function maskEmail(string $email): string
    {
        [$name, $domain] = explode('@', $email);

        if (strlen($name) <= 2) {
            return substr($name, 0, 1) . '**@' . $domain;
        }

        return substr($name, 0, 2) . str_repeat('*', strlen($name) - 2) . '@' . $domain;
    }

}
