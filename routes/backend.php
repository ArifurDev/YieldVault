<?php


use App\Http\Controllers\API\Web\Backend\SupportTicketController;
use App\Http\Controllers\Web\Backend\DailyTaskController;
use App\Http\Controllers\Web\Backend\DashboardController;
use App\Http\Controllers\Web\Backend\DynamicPageController;
use App\Http\Controllers\Web\Backend\KycController;
use App\Http\Controllers\Web\Backend\LevelController;
use App\Http\Controllers\Web\Backend\NotificationController;
use App\Http\Controllers\Web\Backend\PackageController;
use App\Http\Controllers\Web\Backend\PaymentReportController;
use App\Http\Controllers\Web\Backend\PermissionController;
use App\Http\Controllers\Web\Backend\ProfileController;
use App\Http\Controllers\Web\Backend\RoiListController;
use App\Http\Controllers\Web\Backend\RoiSystemController;
use App\Http\Controllers\Web\Backend\RoleController;
use App\Http\Controllers\Web\Backend\SettingController;
use App\Http\Controllers\Web\Backend\TaskController;
use App\Http\Controllers\Web\Backend\TransactionController;
use App\Http\Controllers\Web\Backend\UserController;
use App\Http\Controllers\Web\Backend\UserRoleManagementController;
use App\Http\Controllers\Web\Backend\WalletController;

//! dashboard
Route::get('/dashboard',[DashboardController::class,'index'])->name('dashboard');
Route::get('/dashboard/user-growth', [DashboardController::class, 'userGrowth'])->name('dashboard.user-growth');
Route::get('/dashboard/recent-transactions', [DashboardController::class, 'recentTransactions'])->name('dashboard.recent-transactions');
//! profile
Route::controller(ProfileController::class)->group(function () {
    Route::middleware(['role_or_permission:profile view'])->get('/profile',  'index')->name('profile.edit');
    Route::middleware(['role_or_permission:profile update'])->patch('/profile', 'update')->name('profile.update');
    Route::middleware(['role_or_permission:profile change'])->patch('/password', 'passwordChange')->name('password.change');
    Route::middleware(['role_or_permission:profile email change'])->patch('/email-update', 'updateEmail')->name('email.change');
});
//! setting
Route::controller(SettingController::class)->group(function () {
    //! account or system setting
    Route::middleware(['role_or_permission:setting manage'])->get('/setting', 'index')->name('setting.index');
    Route::middleware(['role_or_permission:setting manage'])->post('/setting', 'store')->name('setting.store');

    //! smtp setting
    Route::middleware(['role_or_permission:setting manage'])->get('/smtp-setting', 'smtpIndex')->name('smtp.index');
    Route::middleware(['role_or_permission:setting manage'])->post('/smtp-setting', 'smtpStore')->name('smtp.store');

   //! Route for invite referral points & amount settings
    Route::middleware(['role_or_permission:referral setting manage'])->get('/settings/referral', 'referralIndex')->name('settings.referral.index');
    Route::middleware(['role_or_permission:referral setting manage'])->post('/settings/referral', 'referralStore')->name('settings.referral.store');
});


//! users
Route::controller(UserController::class)->group(function () {
    Route::middleware(['role_or_permission:user list'])->get('/user', 'index')->name('user.index');
    Route::middleware(['role_or_permission:user create'])->post('/user', 'store')->name('user.store');
    Route::middleware(['role_or_permission:user show'])->get('/user/{id}/show', 'show')->name('user.show');
    Route::middleware(['role_or_permission:user edit'])->get('/user/{id}/edit',  'edit')->name('user.edit');
    Route::middleware(['role_or_permission:user update'])->put('/user/{id}/update',  'update')->name('user.update');
    Route::middleware(['role_or_permission:user delete'])->delete('/user/{id}/delete',  'delete')->name('user.delete');
});

//! Route for role management
Route::controller(RoleController::class)->group(function () {
    Route::middleware(['role_or_permission:role list'])->get('/roles', 'index')->name('roles.index');
    Route::middleware(['role_or_permission:role create'])->get('/roles/add', 'create')->name('roles.add');
    Route::middleware(['role_or_permission:role create'])->post('/role/store', 'store')->name('role.store');
    Route::middleware(['role_or_permission:role edit'])->get('/role/edit/{id}', 'edit')->name('role.edit');
    Route::middleware(['role_or_permission:role edit'])->post('/role/update/{id}', 'update')->name('role.update');
    Route::middleware(['role_or_permission:role delete'])->delete('/role/delete/{id}', 'destroy')->name('role.delete');
});

//! Route for permission management
Route::middleware(['role_or_permission:permission list'])->get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');  // Get permissions

//! Route for user role management
Route::prefix('user')->controller(UserRoleManagementController::class)->group(function () {
    Route::middleware(['role_or_permission:user role manage'])->get('/roles', 'index')->name('user.roles.index');
    Route::middleware(['role_or_permission:user role manage'])->post('/{id}/attach-role', 'attachRole')->name('user.attach.role');
    Route::middleware(['role_or_permission:user role manage'])->post('/{id}/detach-role', 'detachRole')->name('user.detach.role');
});

//! Route for dynamic page
Route::prefix('/dynamic-pages')->controller(DynamicPageController::class)->group(function () {
    Route::middleware(['role_or_permission:dynamic page manage'])->get('/', 'index')->name('dynamic-pages.index');
    Route::middleware(['role_or_permission:dynamic page manage'])->get('/create', 'create')->name('dynamic-pages.create');
    Route::middleware(['role_or_permission:dynamic page manage'])->post('/', 'store')->name('dynamic-pages.store');
    Route::middleware(['role_or_permission:dynamic page manage'])->get('/{id}/edit', 'edit')->name('dynamic-pages.edit');
    Route::middleware(['role_or_permission:dynamic page manage'])->post('/{id}','update')->name('dynamic-pages.update');
    Route::middleware(['role_or_permission:dynamic page manage'])->delete('/{id}','destroy')->name('dynamic-pages.delete');
});


//! Route for kyc
Route::prefix('/kyc')->controller(KycController::class)->group(function () {
    Route::middleware(['role_or_permission:kyc list'])->get('/', 'index')->name('kyc.index');
    Route::middleware(['role_or_permission:kyc update'])->get('/edit/{id}', 'edit')->name('kyc.edit');
    Route::middleware(['role_or_permission:kyc update'])->patch('/{id}', 'update')->name('kyc.update');
    Route::middleware(['role_or_permission:kyc delete'])->delete('/{id}', 'destroy')->name('kyc.delete');
});


//! Route for task management
Route::prefix('/tasks')->controller(TaskController::class)->group(function () {
    Route::middleware(['role_or_permission:task manage'])->get('/', 'index')->name('tasks.index');
    Route::middleware(['role_or_permission:task manage'])->get('/create', 'create')->name('tasks.create');
    Route::middleware(['role_or_permission:task manage'])->post('/create', 'store')->name('tasks.store');
    Route::middleware(['role_or_permission:task manage'])->get('/{id}/edit', 'edit')->name('tasks.edit');
    Route::middleware(['role_or_permission:task manage'])->put('/{id}', 'update')->name('tasks.update');
    Route::middleware(['role_or_permission:task manage'])->delete('/{id}', 'destroy')->name('tasks.delete');
});

//! Route for daily task management
Route::prefix('/daily-tasks')->controller(DailyTaskController::class)->group(function () {
    Route::middleware(['role_or_permission:daily task manage'])->get('/', 'index')->name('daily.task.index');
    Route::middleware(['role_or_permission:daily task manage'])->get('/assign', 'assign')->name('daily.tasks.assign');
    Route::middleware(['role_or_permission:daily task manage'])->post('/assign-store', 'assignStore')->name('daily.tasks.assign.store');
    Route::middleware(['role_or_permission:daily task manage'])->post('/store', 'store')->name('daily.tasks.store');
    Route::middleware(['role_or_permission:daily task manage'])->delete('/assign-remove/{id}', 'assignRemove')->name('daily.tasks.assign.remove');
    Route::middleware(['role_or_permission:daily task manage'])->put('/assign-update/{id}', 'assignUpdate')->name('daily.tasks.assign.update');
});

//! Route for Levels
Route::prefix('/levels')->controller(LevelController::class)->group(function () {
    Route::middleware(['role_or_permission:level manage'])->get('/', 'index')->name('level.index');
    Route::middleware(['role_or_permission:level manage'])->get('/edit/{id}', 'edit')->name('level.edit');
    Route::middleware(['role_or_permission:level manage'])->put('/update/{id}', 'update')->name('level.update');
});
//! Route for package
Route::prefix('/package')->controller(PackageController::class)->group(function () {
    Route::middleware(['role_or_permission:package manage'])->get('/', 'index')->name('package.index');
    Route::middleware(['role_or_permission:package manage'])->get('/edit/{id}', 'edit')->name('package.edit');
    Route::middleware(['role_or_permission:package manage'])->put('/update/{id}', 'update')->name('package.update');
});

//! Route for transaction
Route::prefix('/transaction')->controller(TransactionController::class)->group(function () {
    Route::middleware(['role_or_permission:transaction list'])->get('/', 'index')->name('transaction.index');
    Route::middleware(['role_or_permission:transaction view'])->get('/transactions/{transaction}', 'show')->name('transaction.show');
    Route::middleware(['role_or_permission:transaction export'])->get('/export', 'export')->name('transaction.export');
});

//! Route for Wallet Management
Route::prefix('/wallets')->controller(WalletController::class)->group(function () {
    Route::get('/', 'index')->name('wallet.index');
    Route::middleware(['role_or_permission:Wallet manage'])->get('/wallet/{user}/transactions', 'show')->name('wallet.show');
    Route::middleware(['role_or_permission:Wallet manage'])->put('/update/{id}', 'update')->name('wallet.update');

    Route::middleware(['role_or_permission:Wallet manage'])->post('/wallet/admin/action',  'adminWalletAction')->name('wallet.admin.action');
});

Route::get('/admin/managers', [UserController::class, 'managers'])->name('admin.managers');
//Route::get('/admin/notifications', function () {
//    return view('backend.notifications.index');
//})->name('admin.notifications.index');


Route::prefix('/notification')->controller(NotificationController::class)->group(function () {
    Route::get('/', 'index')->name('notification.index');
    // Read single OR all
    Route::post('/read/{id?}','readNotifications')->name('notification.read');
});

Route::prefix('/roi')->controller(RoiSystemController::class)->group(function () {
    /*
     *  ROI DASHBOARD
     *  KPI cards, system health, today ROI status
     */
    Route::get('/dashboard', 'index')->name('roi.dashboard');
    Route::get('/dashboard/data','data')->name('roi.dashboard.data');


//    /*
//     * ROI LIST (Investment-wise ROI)
//     * Active investments, progress, earned, status
//    */
//    Route::get('/list','index')->name('list');
//    Route::get('/list/data','data')->name('list.data');
//
//    /*
//     * ROI LOGS (Per Investment)
//     * Daily ROI history for audit
//    */
//    Route::get('/{investment}/logs', 'logs')->name('logs');
//
//    /*
//     *  ROI AUDIT REPORT
//     *  Date range audit + CSV export
//    */
//    Route::get('/audit',  'index')->name('audit');
//    Route::get('/audit/data', 'data')->name('audit.data');
//    Route::get('/audit/export', 'export')->name('audit.export');
//
//    /*
//     * ROI SYSTEM ACTIONS
//     * Manual run, failed jobs, retry
//    */
//    Route::post('/run','run')->name('run');
//    Route::get('/failed-jobs','failedJobs')->name('failed.jobs');
//    Route::post('/failed-jobs/{id}/retry','retryFailedJob')->name('failed.jobs.retry');
});

Route::prefix('/roi')->controller(RoiListController::class)->group(function () {
    /*
     * ROI LIST (Investment-wise ROI)
     * Active investments, progress, earned, status
    */
    Route::get('/list','index')->name('roi.list');
    // ROI LOGS
    Route::get('/{investment}/logs', 'logs')->name('roi.logs');
    Route::get('/{investment}/logs/data', 'logsData')->name('roi.logs.data');
});

Route::post('clear-cache', [SettingController::class, 'clearCache'])->name('cache.clear');

Route::prefix('/support-tickets')->controller(SupportTicketController::class)->group(function () {
    Route::get('/',  'index')->name('support-tickets.index');
    Route::get('/data',  'data')->name('support-tickets.data');
    Route::get('/{ticket}',  'show')->name('support-tickets.show');
    Route::post('/{ticket}/reply',  'reply')->name('support-tickets.reply');
    Route::post('/{ticket}/close',  'close')->name('support-tickets.close');
});

// Route for payment Report
Route::prefix('payment-reports')->name('payment-reports.')->controller(PaymentReportController::class)->group(function () {
    Route::get('/',  'index')->name('index');
    Route::get('/create/{ticket}', 'create')->name('create');
    Route::post('/', 'store')->name('store');
    Route::get('/{paymentReport}','show')->name('show');
});

