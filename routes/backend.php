<?php


use App\Http\Controllers\Web\Backend\DailyTaskController;
use App\Http\Controllers\Web\Backend\DashboardController;
use App\Http\Controllers\Web\Backend\DynamicPageController;
use App\Http\Controllers\Web\Backend\KycController;
use App\Http\Controllers\Web\Backend\LevelController;
use App\Http\Controllers\Web\Backend\PermissionController;
use App\Http\Controllers\Web\Backend\ProfileController;
use App\Http\Controllers\Web\Backend\RoleController;
use App\Http\Controllers\Web\Backend\SettingController;
use App\Http\Controllers\Web\Backend\TaskController;
use App\Http\Controllers\Web\Backend\UserController;
use App\Http\Controllers\Web\Backend\UserRoleManagementController;
use App\Http\Controllers\Web\Backend\WalletController;

//! dashboard
Route::get('/dashboard',[DashboardController::class,'index'])->name('dashboard');
//! profile
Route::controller(ProfileController::class)->group(function () {
    Route::get('/profile',  'index')->name('profile.edit');
    Route::patch('/profile', 'update')->name('profile.update');
    Route::patch('/password', 'passwordChange')->name('password.change');
    Route::patch('/email-update', 'updateEmail')->name('email.change');
});
//! setting
Route::controller(SettingController::class)->group(function () {
    //! account or system setting
    Route::get('/setting', 'index')->name('setting.index');
    Route::post('/setting', 'store')->name('setting.store');

    //! smtp setting
    Route::get('/smtp-setting', 'smtpIndex')->name('smtp.index');
    Route::post('/smtp-setting', 'smtpStore')->name('smtp.store');

   //! Route for invite referral points & amount settings
    Route::get('/settings/referral', 'referralIndex')->name('settings.referral.index');
    Route::post('/settings/referral', 'referralStore')->name('settings.referral.store');
});


//! users
Route::controller(UserController::class)->group(function () {
    Route::get('/user', 'index')->name('user.index');
    Route::post('/user', 'store')->name('user.store');
    Route::get('/user/{id}/show', 'show')->name('user.show');
    Route::get('/user/{id}/edit',  'edit')->name('user.edit');
    Route::put('/user/{id}/update',  'update')->name('user.update');
    Route::delete('/user/{id}/delete',  'delete')->name('user.delete');
});


//! Route for role management
Route::controller(RoleController::class)->group(function () {
    Route::get('/roles', 'index')->name('roles.index');
    Route::get('/roles/add', 'create')->name('roles.add');
    Route::post('/role/store', 'store')->name('role.store');
    Route::get('/role/edit/{id}', 'edit')->name('role.edit');
    Route::post('/role/update/{id}', 'update')->name('role.update');
    Route::delete('/role/delete/{id}', 'destroy')->name('role.delete');
});

//! Route for permission management
Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');  // Get permissions


//! Route for user role management
Route::prefix('user')->controller(UserRoleManagementController::class)->group(function () {
    Route::get('/roles', 'index')->name('user.roles.index');
    Route::post('/{id}/attach-role', 'attachRole')->name('user.attach.role');
    Route::post('/{id}/detach-role', 'detachRole')->name('user.detach.role');
});


//! Route for dynamic page
Route::prefix('/dynamic-pages')->controller(DynamicPageController::class)->group(function () {
    Route::get('/', 'index')->name('dynamic-pages.index');
    Route::get('/create', 'create')->name('dynamic-pages.create');
    Route::post('/', 'store')->name('dynamic-pages.store');
    Route::get('/{id}/edit', 'edit')->name('dynamic-pages.edit');
    Route::post('/{id}','update')->name('dynamic-pages.update');
    Route::delete('/{id}','destroy')->name('dynamic-pages.delete');
});


//! Route for kyc
Route::prefix('/kyc')->controller(KycController::class)->group(function () {
    Route::get('/', 'index')->name('kyc.index');
    Route::get('/edit/{id}', 'edit')->name('kyc.edit');
    Route::patch('/{id}', 'update')->name('kyc.update');
    Route::delete('/{id}', 'destroy')->name('kyc.delete');
});


//! Route for task management
Route::prefix('/tasks')->controller(TaskController::class)->group(function () {
    Route::get('/', 'index')->name('tasks.index');
    Route::get('/create', 'create')->name('tasks.create');
    Route::post('/create', 'store')->name('tasks.store');
    Route::get('/{id}/edit', 'edit')->name('tasks.edit');
    Route::put('/{id}', 'update')->name('tasks.update');
    Route::delete('/{id}', 'destroy')->name('tasks.delete');
});

//! Route for daily task management
Route::prefix('/daily-tasks')->controller(DailyTaskController::class)->group(function () {
    Route::get('/', 'index')->name('daily.task.index');
    Route::get('/assign', 'assign')->name('daily.tasks.assign');
    Route::post('/assign-store', 'assignStore')->name('daily.tasks.assign.store');
    Route::post('/store', 'store')->name('daily.tasks.store');
    Route::delete('/assign-remove/{id}', 'assignRemove')->name('daily.tasks.assign.remove');
    Route::put('/assign-update/{id}', 'assignUpdate')->name('daily.tasks.assign.update');
});


//! Route for Levels
Route::prefix('/levels')->controller(LevelController::class)->group(function () {
    Route::get('/', 'index')->name('level.index');
    Route::get('/edit/{id}', 'edit')->name('level.edit');
    Route::put('/update/{id}', 'update')->name('level.update');
});


//! Route for Wallet Management
Route::prefix('/wallets')->controller(WalletController::class)->group(function () {
    Route::get('/', 'index')->name('wallet.index');
    Route::get('/wallet/{user}/transactions', 'show')->name('wallet.show');
    Route::put('/update/{id}', 'update')->name('wallet.update');

    Route::post('/wallet/admin/action',  'adminWalletAction')->name('wallet.admin.action');});


Route::get('/admin/managers', [UserController::class, 'managers'])->name('admin.managers');
