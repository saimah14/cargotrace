<?php

namespace Database\Seeders;

use App\Models\Carrier;
use App\Models\Location;
use Illuminate\Database\Seeder;

class ShipmentCatalogSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Nusantara Ocean', 'code' => 'NOL'],
            ['name' => 'Garuda Cargo', 'code' => 'GCR'],
            ['name' => 'CargoTrace Logistics', 'code' => 'CTL'],
        ] as $carrier) {
            Carrier::updateOrCreate(['code' => $carrier['code']], [
                'name' => $carrier['name'],
                'is_active' => true,
            ]);
        }

        foreach ([
            ['name' => 'Tanjung Priok', 'country' => 'Indonesia', 'latitude' => -6.1045, 'longitude' => 106.8800],
            ['name' => 'Medan', 'country' => 'Indonesia', 'latitude' => 3.5952, 'longitude' => 98.6722],
            ['name' => 'Surabaya', 'country' => 'Indonesia', 'latitude' => -7.2575, 'longitude' => 112.7521],
            ['name' => 'Singapore', 'country' => 'Singapore', 'latitude' => 1.2902, 'longitude' => 103.8519],
            ['name' => 'Tokyo', 'country' => 'Japan', 'latitude' => 35.6762, 'longitude' => 139.6503],
            ['name' => 'Rotterdam', 'country' => 'Netherlands', 'latitude' => 51.9244, 'longitude' => 4.4777],
        ] as $location) {
            Location::updateOrCreate(
                ['name' => $location['name'], 'country' => $location['country']],
                $location
            );
        }
    }
}
