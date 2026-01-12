<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /* ================= Profile ================= */
        Permission::create(['name' => 'profile view']);
        Permission::create(['name' => 'profile update']);
        Permission::create(['name' => 'profile change']);
        Permission::create(['name' => 'profile email change']);

        /* ================= Settings ================= */
        Permission::create(['name' => 'setting manage']);
        Permission::create(['name' => 'referral setting manage']);

        /* ================= Users ================= */
        Permission::create(['name' => 'user list']);
        Permission::create(['name' => 'user create']);
        Permission::create(['name' => 'user show']);
        Permission::create(['name' => 'user edit']);
        Permission::create(['name' => 'user update']);
        Permission::create(['name' => 'user delete']);

        /* ================= Roles ================= */
        Permission::create(['name' => 'role list']);
        Permission::create(['name' => 'role create']);
        Permission::create(['name' => 'role edit']);
        Permission::create(['name' => 'role delete']);

        /* ================= Permissions ================= */
        Permission::create(['name' => 'permission list']);

        /* ================= User Role ================= */
        Permission::create(['name' => 'user role manage']);

        /* ================= Dynamic Pages ================= */
        Permission::create(['name' => 'dynamic page manage']);

        /* ================= KYC ================= */
        Permission::create(['name' => 'kyc list']);
        Permission::create(['name' => 'kyc update']);
        Permission::create(['name' => 'kyc delete']);

        /* ================= Tasks ================= */
        Permission::create(['name' => 'task manage']);

        /* ================= Daily Tasks ================= */
        Permission::create(['name' => 'daily task manage']);

        /* ================= Levels ================= */
        Permission::create(['name' => 'level manage']);

        /* ================= Packages ================= */
        Permission::create(['name' => 'package manage']);

        /* ================= Transactions ================= */
        Permission::create(['name' => 'transaction list']);
        Permission::create(['name' => 'transaction view']);
        Permission::create(['name' => 'transaction export']);

        /* ================= Wallet ================= */
        Permission::create(['name' => 'Wallet manage']);
    }
}
