<?php

namespace App\Services;

use App\Mail\NotifyFA;
use App\Mail\NotifyUser;
use App\Models\TwoFactorOtp;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class TwoFactorOtpService
{
    /**
     * OTP lifetime (minutes)
     */
    private const EXPIRY_MINUTES = 5;

    /**
     * Generate OTP, store hash, and send via email
     */
    public function generate(User $user): void
    {
        // Invalidate any previous unused OTPs
        $this->invalidateExisting($user);

        // generate a secure alphanumeric OTP (letters + numbers, mixed case)
        $otp = $this->generateAlphaNumericOTP(6);

        // Store OTP hash in DB
        TwoFactorOtp::create([
            'user_id'    => $user->id,
            'otp_hash'   => Hash::make($otp),
            'expires_at' => now()->addMinutes(self::EXPIRY_MINUTES),
        ]);

        $title = 'Two-Factor Authentication Code';

        Mail::to($user->twoFactorEmail())
            ->send(new NotifyFA(
                $title,
                $otp,                 // plain OTP
                self::EXPIRY_MINUTES  // integer expiry
            ));
    }

    /**
     * Verify OTP
     */
    public function verify(User $user, string $otp): bool
    {
        $record = TwoFactorOtp::where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$record) {
            return false;
        }

        // Check OTP hash
        if (!Hash::check($otp, $record->otp_hash)) {
            return false;
        }

        // Mark OTP as used
        $record->update([
            'used_at' => now(),
        ]);

        return true;
    }

    /**
     * Invalidate all unused OTPs for the user
     */
    public function invalidateExisting(User $user): void
    {
        TwoFactorOtp::where('user_id', $user->id)
            ->whereNull('used_at')
            ->update([
                'used_at' => now(),
            ]);
    }



    // generate a secure alphanumeric OTP (letters + numbers, mixed case)
    private function generateAlphaNumericOTP($length = 7): string
    {
        $letters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';

        // Ensure at least 1 letter and 1 number
        $otp = [
            $letters[random_int(0, strlen($letters) - 1)],
            $numbers[random_int(0, strlen($numbers) - 1)],
        ];

        // Remaining characters (letters + numbers)
        $all = $letters . $numbers;

        for ($i = 2; $i < $length; $i++) {
            $otp[] = $all[random_int(0, strlen($all) - 1)];
        }

        // Securely shuffle so pattern is unpredictable
        shuffle($otp);

        return implode('', $otp);
    }
}
