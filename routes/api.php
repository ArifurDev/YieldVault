<?php

use App\Http\Controllers\API\V1\Auth\User\AuthenticationController;
use App\Http\Controllers\API\V1\Auth\User\ForgotPasswordController;
use App\Http\Controllers\API\V1\Auth\User\ProfileController;
use App\Http\Controllers\API\V1\Auth\User\SocialAuthController;
use App\Http\Controllers\API\V1\KycController;
use Illuminate\Support\Facades\Route;

//! Route for user
Route::prefix('v1')->controller(AuthenticationController::class)->group(function () {
    Route::post('/sign-up', 'signup');
    Route::post('/login', 'login');
});

//! Route for forgot-password
Route::prefix('v1/password')->controller(ForgotPasswordController::class)->group(function () {
    Route::post('/forgot', 'sendResetCode');       // Send OTP to email
    Route::post('/verify', 'verifyResetCode');     // Verify OTP
    Route::put('/reset', 'resetPassword');         // Set new password
});


// Route for user social auth
Route::post('v1/social-login', [SocialAuthController::class, 'SocialLogin']);


Route::group(['middleware' => ['jwt.verify']], function () {
    /**
     * Profile
     */
    Route::prefix('v1/auth')->controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'index');
        Route::put('/profile', 'update');
        Route::post('/logout', 'logout');

        Route::post('/notification', 'notificationSwitch'); // notification toggle button
        Route::put('update-password', 'updatePassword');
    });

    /**
     * kyc -submit
     */
    Route::prefix('v1/auth')->controller(KycController::class)->group(function () {
        Route::post('/kyc/submit-document', 'submitDocument');
    });


    // --------------------------------- manager role

    /**
     * kyc -feature
     */
    Route::prefix('v1')->controller(KycController::class)->group(function () {
        Route::get('/kyc', 'index');
        Route::get('/kyc-documents/{id}', 'show');      // View a single KYC document
        Route::put('/kyc-documents/{id}/review', 'update'); // Approve/Reject KYC
    });


});

