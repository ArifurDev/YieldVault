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
        // setting Permissions -*-
        Permission::create(['name' => 'setting manage']);

        // user Permissions -*-
        Permission::create(['name' => 'user lists']);
        Permission::create(['name' => 'show user']);
        Permission::create(['name' => 'update user']);
        Permission::create(['name' => 'delete user']);

        // role and Permissions -*-
        Permission::create(['name' => 'r&p manage']);
    }
}
