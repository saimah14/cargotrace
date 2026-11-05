<?php

namespace Database\Seeders;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun demo (password semuanya: "password")
        $accounts = [
            ['Admin', 'admin@cargotrace.test', User::ROLE_ADMIN],
            ['Operator', 'operator@cargotrace.test', User::ROLE_OPERATOR],
            ['Customer', 'customer@cargotrace.test', User::ROLE_CUSTOMER],
        ];

        foreach ($accounts as [$name, $email, $role]) {
            $user = User::firstOrNew(['email' => $email]);
            $user->name = $name;
            $user->password = Hash::make('password');
            $user->role = $role; // diisi langsung karena 'role' tidak fillable
            $user->save();
        }

        // Carrier fiktif (data demo sintetis)
        $carriers = [
            ['Nusantara Ocean Line', 'NOL'],
            ['Straits Freight Co', 'SFC'],
            ['Eurasia Cargo Lines', 'ECL'],
        ];

        foreach ($carriers as [$name, $code]) {
            Carrier::firstOrCreate(['code' => $code], ['name' => $name]);
        }

        // Lokasi: rute Asia Tenggara -> Eropa
        $locations = [
            ['Tanjung Priok, Jakarta', 'Indonesia', -6.1046, 106.8800],
            ['Singapore Port', 'Singapore', 1.2644, 103.8200],
            ['Port Klang', 'Malaysia', 3.0000, 101.3900],
            ['Colombo Port', 'Sri Lanka', 6.9500, 79.8500],
            ['Port of Rotterdam', 'Netherlands', 51.9500, 4.1400],
            ['Port of Hamburg', 'Germany', 53.5400, 9.9700],
        ];

        foreach ($locations as [$name, $country, $lat, $lng]) {
            Location::firstOrCreate(
                ['name' => $name, 'country' => $country],
                ['latitude' => $lat, 'longitude' => $lng]
            );
        }
    }
}