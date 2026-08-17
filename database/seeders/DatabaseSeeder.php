<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (Schema::hasColumn('users', 'password_hash') && !Schema::hasColumn('users', 'password')) {
            DB::statement('ALTER TABLE users RENAME COLUMN password_hash TO password');
        }

        $testUsers = [
            ['employee_id' => 'EMP001', 'first_name' => 'Admin',      'last_name' => 'User', 'email' => 'admin@test.com',      'role' => 'administrative_admin'],
            ['employee_id' => 'EMP002', 'first_name' => 'TechAdmin',  'last_name' => 'User', 'email' => 'techadmin@test.com',  'role' => 'technical_admin'],
            ['employee_id' => 'EMP003', 'first_name' => 'Holder',     'last_name' => 'User', 'email' => 'holder@test.com',     'role' => 'asset_holder'],
            ['employee_id' => 'EMP004', 'first_name' => 'Technician', 'last_name' => 'User', 'email' => 'technician@test.com', 'role' => 'technician'],
        ];

        foreach ($testUsers as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, [
                    'password'  => Hash::make('password'),
                    'is_active' => true,
                ])
            );
        }
    }
}
