<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@cargotrace.com'],
            ['name' => 'Saimah Admin', 'password' => Hash::make('password123'), 'role' => 'Administrator']
        );

        User::updateOrCreate(
            ['email' => 'operator@cargotrace.com'],
            ['name' => 'Operator Logistik', 'password' => Hash::make('password123'), 'role' => 'Logistics Operator']
        );

        User::updateOrCreate(
            ['email' => 'customer@cargotrace.com'],
            ['name' => 'Customer BSN', 'password' => Hash::make('password123'), 'role' => 'Customer']
        );
    }
}