<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OperationManagerSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('operation_managers')->insert([
            'name' => 'Quản lý vận hành',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('Admin@123456'),
            'account_type' => 'operation_manager',
            'status' => 'active',
            'phone' => '0909000000',
            'remember_token' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'password_changed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
