<?php

use App\Helpers\translateHelper;
use App\Http\Controllers\API\V1\Auth\User\AuthenticationController;
use App\Http\Controllers\API\V1\Auth\User\ForgotPasswordController;
use App\Http\Controllers\API\V1\Auth\User\ProfileController;
use App\Http\Controllers\API\V1\Auth\User\SocialAuthController;
use App\Http\Controllers\API\V1\Auth\User\TwoFactorController;
use App\Http\Controllers\API\V1\DailyTaskController;
use App\Http\Controllers\API\V1\DynamicPageController;
use App\Http\Controllers\API\V1\FirebaseTokenController;
use App\Http\Controllers\API\V1\InvestmentController;
use App\Http\Controllers\API\V1\KycController;
use App\Http\Controllers\API\V1\LevelController;
use App\Http\Controllers\API\V1\Manager\DashboardController;
use App\Http\Controllers\API\V1\Manager\WalletAccountController;
use App\Http\Controllers\API\V1\NotificationController;
use App\Http\Controllers\API\V1\PackageController;
use App\Http\Controllers\API\V1\PaymentReportController;
use App\Http\Controllers\API\V1\ReferralController;
use App\Http\Controllers\API\V1\Auth\User\ReferralController as AuthUserReferralController;
use App\Http\Controllers\API\V1\SupportTicketController;
use App\Http\Controllers\API\V1\WalletController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\Manager\DepositRequestController;
use App\Http\Controllers\API\V1\Manager\WithdrawalRequestController;
use Illuminate\Http\Request;

//! Route for user
Route::prefix('v1')->controller(AuthenticationController::class)->group(function () {
    Route::post('/sign-up', 'signup');
    Route::post('/login', 'login');

    Route::post('/login/2fa/verify', 'verifyTwoFactor')->middleware(['auth:api','2fa.temp']);
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

        // delete
        Route::delete('/delete-account', 'deleteAccount');
    });

    /**
     * Two-Factor Authentication
     */
    Route::prefix('v1/2FA')->controller(TwoFactorController::class)->group(function () {
        Route::get('/',  'getTwoFactorStatus');
        Route::post('/enable', 'enableTwoFactor');
        Route::post('/disable','disableTwoFactor');
        Route::post('/verify', 'verifyTwoFactor');
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
        Route::put('/update/kyc-document/{id}', 'updateDocument');
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
        Route::get('/current-progress', 'currentProgress');
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
        Route::get('/dashboard/summary', 'summary');
        Route::get('/get-filter-data', 'getFilterData');
        Route::get('/', 'getWallet');
        Route::get('/transactions', 'getTransactions');
        Route::post('/deposit-request', 'depositRequest');
        Route::post('/withdraw-request', 'withdrawRequest');
    });

    Route::prefix('v1/')->controller(WalletController::class)->group(function () {
        Route::get('dashboard/summary', 'summary');
    });

    /**
     * Notification - user, manage
     */
    Route::prefix('v1/notifications')->controller(NotificationController::class)->group(function () {
        Route::get('/', 'index');
        Route::patch('/read/{id?}',  'readNotifications');
    });

    /**
     * investment
     */
    Route::prefix('v1/investment')->controller(InvestmentController::class)->group(function () {
        Route::post('/invest',  'investNow');
        Route::get('/my',  'myInvestments');
        Route::get('/summary', 'investmentSummary');

    });

    /**
     * support ticket management
     */
    Route::prefix('v1/support-ticket')->controller(SupportTicketController::class)->group(function () {
        Route::get('/support/meta','getData');
        Route::get('/', 'index');
        Route::post('/',  'store');
        Route::get('/{ticket}', 'show');
        Route::post('/{ticket}/reply',  'reply');
        Route::post('/{ticket}/close',  'close');
    });

    // --------------------------------- manager role

    /**
     * kyc -feature
     */
    Route::prefix('v1')->controller(KycController::class)->group(function () {
        Route::get('/kyc', 'index');
        Route::get('/kyc-documents/{id?}', 'show');      // View a single KYC document
        Route::put('/kyc-documents/{id}/review', 'update'); // Approve/Reject KYC
    })->middleware(['is_manager']);


    Route::prefix('v1/manager')->group(function () {

        Route::prefix('deposit-requests')->controller(DepositRequestController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/{id}', 'show');
            Route::post('/{id}/approve', 'approve');
            Route::post('/{id}/reject', 'reject');
        });

        Route::prefix('withdrawal-requests')->controller(WithdrawalRequestController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/{id}', 'show');
            Route::post('/{id}/approve', 'approve');
            Route::post('/{id}/reject', 'reject');
            Route::post('/{id}/mark-as-paid', 'markAsPaid');
        });


        Route::prefix('wallet-accounts')->controller(WalletAccountController::class)->group(function () {
            Route::get('/',  'index');
            Route::post('/',  'store');
            Route::put('/{walletAccount}', 'update');
            Route::patch('/{walletAccount}/toggle',  'toggleStatus');
            Route::delete('/{walletAccount}','destroy');
        });

        Route::prefix('dashboard')->controller(DashboardController::class)->group(function () {
            Route::get('/quick-stats',  'summary');
            Route::get('/activity-logs', 'ActivityLogs');
            Route::get('/recent-transaction','getRecentTransactions');
        });

        Route::prefix('payment-reports')->controller(PaymentReportController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/{report}',  'show');
            Route::post('/{report}/resolve',  'resolve');
        });

    });

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

/**
 * dynamic page
 */
Route::prefix('v1/dynamic-pages')->controller(DynamicPageController::class)->group(function () {
    Route::get('/', 'getTitle');
    Route::get('/{slug}', 'getDetails');
});



Route::get('/test', function (Request $request) {
    $lang = $request->header('Accept-Language', 'en');


    $message = translateHelper::translateCached(
        'test_message',
        'User registration successful.',
        $lang
    );


   $title = "hello world";
   return response()->json([
       'lang' => $lang,
       'title' =>$message,
   ]);
});


Route::post('/test-mail', function (Request $request) {

    $request->validate([
        'email' => 'required|email',
    ]);

    Mail::raw('This is a test email from Laravel 🚀', function ($message) use ($request) {
        $message->to($request->email)
            ->subject('Test Mail');
    });

    return response()->json([
        'message' => 'Test mail sent successfully!'
    ]);
});
