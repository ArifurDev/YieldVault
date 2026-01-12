<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

//        User::factory()->create([
//            'name' => 'Test User',
//            'email' => 'test@example.com',
//        ]);
        $this->call([UserSeeder::class]);
        $this->call([PermissionSeeder::class]);

        $admin = User::where('role', 'admin')->first();
        $admin->givePermissionTo(Permission::all());

        // level seeder
        $this->call([LevelSeeder::class]);
        // package seeder
        $this->call([PackageSeeder::class]);


        /* ================= KYC MANAGE ================= */
        $kycManage = Role::firstOrCreate(['name' => 'Kyc Manage']);
        $kycManage->syncPermissions([
            'kyc list',
            'kyc update',
            'kyc delete',
        ]);

        /* ================= PROFILE MANAGE ================= */
        $profileManage = Role::firstOrCreate(['name' => 'profile Manage']);
        $profileManage->syncPermissions([
            'profile view',
            'profile update',
            'profile change',
            'profile email change',
        ]);

        /* ================= ROLE MANAGE ================= */
        $roleManage = Role::firstOrCreate(['name' => 'Role Manage']);
        $roleManage->syncPermissions([
            'role list',
            'role create',
            'role edit',
            'role delete',
            'permission list',
            'user role manage',
        ]);

        /* ================= SETTING MANAGE ================= */
        $settingManage = Role::firstOrCreate(['name' => 'Setting Manage']);
        $settingManage->syncPermissions([
            'setting manage',
            'referral setting manage',
            'dynamic page manage',
        ]);

        /* ================= TASK MANAGE ================= */
        $taskManage = Role::firstOrCreate(['name' => 'Task Manage']);
        $taskManage->syncPermissions([
            'task manage',
            'daily task manage',
        ]);

        /* ================= USER MANAGEMENT ================= */
        $userManage = Role::firstOrCreate(['name' => 'User Management']);
        $userManage->syncPermissions([
            'user list',
            'user create',
            'user show',
            'user edit',
            'user update',
            'user delete',
        ]);

        /* ================= WALLET MANAGE ================= */
        $walletManage = Role::firstOrCreate(['name' => 'Wallet Manage']);
        $walletManage->syncPermissions([
            'transaction list',
            'transaction view',
            'transaction export',
            'Wallet manage',
        ]);
    }
}
