<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use App\Models\Location;
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

        // Asset categories — matches the "Asset type" dropdown in the prototype
        $categories = [
            ['code' => 'LAPTOP',  'name' => 'Laptop / Desktop',    'default_useful_life_yrs' => 4],
            ['code' => 'PRINTER', 'name' => 'Printer / Scanner',   'default_useful_life_yrs' => 5],
            ['code' => 'NETWORK', 'name' => 'Network Equipment',   'default_useful_life_yrs' => 6],
            ['code' => 'AV',      'name' => 'AV Equipment',        'default_useful_life_yrs' => 5],
            ['code' => 'FURN',    'name' => 'Furniture',           'default_useful_life_yrs' => 10],
            ['code' => 'VEHICLE', 'name' => 'Vehicle',             'default_useful_life_yrs' => 8],
            ['code' => 'OTHER',   'name' => 'Other',               'default_useful_life_yrs' => 5],
        ];

        foreach ($categories as $cat) {
            AssetCategory::firstOrCreate(['code' => $cat['code']], $cat);
        }

        // Locations — standing in for the prototype's "Department" dropdown,
        // since the real schema tracks location rather than a separate
        // department field
        $locations = [
            ['code' => 'ICT',   'name' => 'ICT Division'],
            ['code' => 'ADMIN', 'name' => 'Administrative Office'],
            ['code' => 'RSCH',  'name' => 'Research Division'],
            ['code' => 'OPS',   'name' => 'Operations'],
            ['code' => 'FIN',   'name' => 'Finance'],
            ['code' => 'HR',    'name' => 'Human Resources'],
        ];

        foreach ($locations as $loc) {
            Location::firstOrCreate(['code' => $loc['code']], $loc);
        }
    }
}
