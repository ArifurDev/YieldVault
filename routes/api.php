<?php

use App\Http\Controllers\API\V1\Auth\User\AuthenticationController;
use App\Http\Controllers\API\V1\Auth\User\ForgotPasswordController;
use App\Http\Controllers\API\V1\Auth\User\ProfileController;
use App\Http\Controllers\API\V1\Auth\User\SocialAuthController;
use App\Http\Controllers\API\V1\DailyTaskController;
use App\Http\Controllers\API\V1\FirebaseTokenController;
use App\Http\Controllers\API\V1\KycController;
use App\Http\Controllers\API\V1\LevelController;
use App\Http\Controllers\API\V1\PackageController;
use App\Http\Controllers\API\V1\ReferralController;
use App\Http\Controllers\API\V1\Auth\User\ReferralController as AuthUserReferralController;
use App\Http\Controllers\API\V1\WalletController;
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


Route::group(['middleware' => ['jwt.verify','daily.streak']], function () {
    /**
     * Profile
     */
    Route::prefix('v1/auth')->controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'index');
        Route::put('/profile', 'update');
        Route::post('/logout', 'logout');

        Route::put('/notification', 'notificationSwitch'); // notification toggle button
        Route::put('update-password', 'updatePassword');
    });

    /**
     * Auth Referral
     */
    Route::prefix('v1/auth/ref')->controller(AuthUserReferralController::class)->group(function () {
        Route::get('/', 'index');
    });


    /**
     * kyc -submit
     */
    Route::prefix('v1/auth')->controller(KycController::class)->group(function () {
        Route::post('/kyc/submit-document', 'submitDocument');
    });

    /**
     * Task
     */
    Route::prefix('v1/tasks')->controller(DailyTaskController::class)->group(function () {
        Route::get('/today', 'todayTask');
        Route::post('/today-completed', 'taskCompleted');
        Route::get('/history', 'historyTask');
    });

    /**
     * level management
     */
    Route::prefix('v1/levels')->controller(LevelController::class)->group(function () {
        Route::get('/', 'getLevels');
    });

    /**
     * packages management
     */
    Route::prefix('v1/packages')->controller(PackageController::class)->group(function () {
        Route::get('/', 'getPackages');
    });

    /**
     * wallet management
     */
    Route::prefix('v1/wallet')->controller(WalletController::class)->group(function () {
        Route::get('/get-filter-data', 'getFilterData');
        Route::get('/', 'getWallet');
        Route::get('/transactions', 'getTransactions');
    });


    // --------------------------------- manager role

    /**
     * kyc -feature
     */
    Route::prefix('v1')->controller(KycController::class)->group(function () {
        Route::get('/kyc', 'index');
        Route::get('/kyc-documents/{id}', 'show');      // View a single KYC document
        Route::put('/kyc-documents/{id}/review', 'update'); // Approve/Reject KYC
    })->middleware(['is_manager']);

    /**
     * Firebase Token Module
     */
    Route::prefix('v1/firebase/token')->controller(FirebaseTokenController::class)->group(function () {
        Route::post('/add', 'store');
        Route::post('/get', 'getToken');
        Route::post('/delete', 'deleteToken');
    });
});

//! Route for Referral link
Route::get('v1/ref/{token}', [ReferralController::class, 'handle']);


