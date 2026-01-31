<?php

namespace App\Http\Controllers\API\V1\Auth\User;

use App\Helpers\translateHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\TwoFactor\DisableTwoFactorRequest;
use App\Http\Requests\Api\V1\TwoFactor\EnableTwoFactorRequest;
use App\Http\Requests\Api\V1\TwoFactor\VerifyTwoFactorRequest;
use App\Services\TwoFactorOtpService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class TwoFactorController extends Controller
{
    use ApiResponse;

    /**
     * Get 2FA status
     */
    public function getTwoFactorStatus(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        try {
            // get current auth
            $user = auth()->user();

            $maskedEmail = $user->twoFactorEmail() ? $this->maskEmail($user->twoFactorEmail()) : null;

            $data = [
                'enabled' => $user->hasTwoFactorEnabled(),
                'email' => $maskedEmail,
            ];

            $message = translateHelper::translateCached(
                'two_factor_status_retrieved',
                'Two-factor authentication status retrieved successfully.',
                $lang
            );

            return $this->sendResponse($data, $message);
        } catch (\Exception $e) {
            Log::error("Get Two Factor Status failed", [
                'userId' => $user->id,
                'message' => $e->getMessage(),
            ]);
            $message = translateHelper::translateCached(
                'two_factor_status_failed',
                'Failed to retrieve two-factor authentication status. Please try again later.',
                $lang
            );

            return $this->sendError($message, [], 500);
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
        $lang = $request->header('Accept-Language', 'en');
        try {
            // validation
            $validated = $request->validated();

            // get current auth
            $user = auth()->user();

            // verify current password
            if (!Hash::check($request->input('current_password'), $user->password)) {
                $message = translateHelper::translateCached(
                    'two_factor_password_incorrect',
                    'Current password is incorrect.',
                    $lang
                );

                return $this->sendError($message, [], 422);
            }

            // Already enabled check
            if ($user->hasTwoFactorEnabled()) {
                $message = translateHelper::translateCached(
                    'two_factor_already_enabled',
                    'Two-factor authentication is already enabled.',
                    $lang
                );

                return $this->sendError($message, [], 422);
            }

            if ($validated['email']) {
                $user->two_factor_email = $validated['email'];
                $user->save();
            }
            // OTP send
            $otpService->generate($user);

            $message = translateHelper::translateCached(
                'two_factor_otp_sent',
                'Verification code sent to your email.',
                $lang
            );

            return $this->sendResponse([], $message);
        } catch (\Exception $e) {
            Log::error("EnableTwoFactor Exception: ", [
                'userId' => $user->id,
                'message' => $e->getMessage(),
            ]);
            $message = translateHelper::translateCached(
                'two_factor_enable_failed',
                'Failed to send verification code. Please try again later.',
                $lang
            );

            return $this->sendError($message, [], 500);
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
        $lang = $request->header('Accept-Language', 'en');
        try {
            $validated = $request->validated();

            // get current auth
            $user = auth()->user();

            if (!$user->hasTwoFactorEnabled()) {
                $message = translateHelper::translateCached(
                    'two_factor_not_enabled',
                    'Two-factor authentication is not enabled.',
                    $lang
                );

                return $this->sendError($message, [], 422);
            }

            if (!Hash::check($validated['password'], $user->password)) {

                $message = translateHelper::translateCached(
                    'two_factor_password_incorrect',
                    'Incorrect password.',
                    $lang
                );

                return $this->sendError($message, [], 422);
            }

            // OTP send
            $otpService->generate($user);

            $message = translateHelper::translateCached(
                'two_factor_otp_sent',
                'Verification code sent to your email.',
                $lang
            );

            return $this->sendResponse([], $message);
        } catch (\Exception $e) {
            Log::error("DisableTwoFactor Exception: ", [
                'userId' => $user->id,
                'message' => $e->getMessage(),
            ]);
            $message = translateHelper::translateCached(
                'two_factor_disable_failed',
                'Failed to send verification code. Please try again later.',
                $lang
            );

            return $this->sendError($message, [], 500);
        }
    }


    /**
     * Verify OTP (enable / disable / login)
     */
    public function verifyTwoFactor(VerifyTwoFactorRequest $request, TwoFactorOtpService $otpService)
    {
        $lang = $request->header('Accept-Language', 'en');
        try {
            $validated = $request->validated();

            $action = $validated['action'];

            // get current auth
            $user = $request->user();

            // OTP verify
            if (!$otpService->verify($user, $request->otp)) {
                $user->incrementTwoFactorFailures();
                $message = translateHelper::translateCached(
                    'two_factor_invalid_code',
                    'Invalid or expired verification code.',
                    $lang
                );

                return $this->sendError($message, [], 422);
            }

            // Reset failure count
            $user->resetTwoFactorFailures();

            // ACTION HANDLING
            if ($action === 'enable') {

                if ($user->hasTwoFactorEnabled()) {
                    $message = translateHelper::translateCached(
                        'two_factor_already_enabled',
                        'Two-factor authentication is already enabled.',
                        $lang
                    );

                    return $this->sendError($message, [], 422);
                }

                $user->update([
                    'two_factor_enabled' => true,
                    'two_factor_verified_at' => now(),
                ]);

                $message = translateHelper::translateCached(
                    'two_factor_enabled_success',
                    'Two-factor authentication enabled successfully.',
                    $lang
                );

                return $this->sendResponse([
                    'two_factor_enabled' => true,
                ], $message);
            }

            if ($request->action === 'disable') {

                if (!$user->hasTwoFactorEnabled()) {
                    $message = translateHelper::translateCached(
                        'two_factor_already_disabled',
                        'Two-factor authentication is already disabled.',
                        $lang
                    );

                    return $this->sendError($message, [], 422);
                }

                $user->update([
                    'two_factor_enabled' => false,
                    'two_factor_verified_at' => null,
                    'two_factor_email' => null,
                ]);

                $message = translateHelper::translateCached(
                    'two_factor_disabled_success',
                    'Two-factor authentication disabled successfully.',
                    $lang
                );

                return $this->sendResponse([
                    'two_factor_enabled' => false,
                ], $message);
            }
        } catch (\Exception $e) {
            Log::error("VerifyTwoFactor Exception: ", [
                'userId' => $user->id,
                'message' => $e->getMessage(),
            ]);
            $message = translateHelper::translateCached(
                'two_factor_verify_failed',
                'Failed to verify two-factor authentication. Please try again later.',
                $lang
            );

            return $this->sendError($message, [], 500);
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
