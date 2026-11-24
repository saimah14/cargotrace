<?php

namespace Database\Seeders;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\TrackingEvent;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoShipmentSeeder extends Seeder
{
    public function run(): void
    {
        if (Shipment::exists()) {
            return;
        }

        $customer = User::where('email', 'customer@cargotrace.com')->firstOrFail();
        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();
        $corporate = User::updateOrCreate(
            ['email' => 'corporate@cargotrace.com'],
            [
                'name' => 'PT Astra Mesin',
                'password' => Hash::make('password123'),
                'role' => 'Customer',
            ]
        );

        $carrier = fn (string $code) => Carrier::where('code', $code)->firstOrFail();
        $location = fn (string $name) => Location::where('name', $name)->firstOrFail();

        $makeShipment = function (
            string $number,
            User $owner,
            string $carrierCode,
            string $origin,
            string $destination,
            string $status,
            int $departureDays,
            int $arrivalDays,
            string $cargoType,
            array $items,
            array $details
        ) use ($carrier, $location) {
            $shipment = Shipment::create([
                'shipment_number' => $number,
                'customer_id' => $owner->id,
                'carrier_id' => $carrier($carrierCode)->id,
                'origin_id' => $location($origin)->id,
                'destination_id' => $location($destination)->id,
                'cargo_type' => $cargoType,
                'transport_mode' => $details['transport_mode'],
                'sender_name' => $details['sender_name'],
                'sender_phone' => $details['sender_phone'] ?? null,
                'origin_address' => $details['origin_address'] ?? null,
                'receiver_name' => $details['receiver_name'],
                'receiver_phone' => $details['receiver_phone'] ?? null,
                'destination_address' => $details['destination_address'],
                'cost' => $details['cost'],
                'expected_departure' => now()->addDays($departureDays),
                'expected_arrival' => now()->addDays($arrivalDays),
            ]);
            $shipment->items()->createMany($items);
            $shipment->forceFill(['status' => $status])->save();

            return $shipment;
        };

        $addEvent = function (
            Shipment $shipment,
            string $type,
            string $place,
            int $hoursAgo,
            string $description,
            bool $isException = false
        ) use ($location, $operator) {
            $placeLocation = $location($place);

            return TrackingEvent::create([
                'shipment_id' => $shipment->id,
                'location_id' => $placeLocation->id,
                'created_by' => $operator->id,
                'event_type' => $type,
                'occurred_at' => now()->subHours($hoursAgo),
                'latitude' => $placeLocation->latitude,
                'longitude' => $placeLocation->longitude,
                'description' => $description,
                'is_exception' => $isException,
            ]);
        };

        $customerShipment = $makeShipment(
            'CT-2026-003',
            $customer,
            'GCR',
            'Medan',
            'Tanjung Priok',
            'IN_TRANSIT',
            -2,
            2,
            'Paket Skincare',
            [['description' => 'Paket Skincare', 'quantity' => 1, 'weight_kg' => 4, 'volume_cbm' => 0.02]],
            [
                'transport_mode' => 'AIR',
                'sender_name' => 'Saimah Store',
                'sender_phone' => '0812-6411-90',
                'receiver_name' => 'Nadia Putri',
                'receiver_phone' => '0813-7700-21',
                'destination_address' => 'Jl. Sudirman Kav. 21, Jakarta Selatan',
                'cost' => 300000,
            ]
        );
        $addEvent($customerShipment, 'BOOKED', 'Medan', 48, 'Paket telah dibooking.');
        $addEvent($customerShipment, 'PICKED_UP', 'Medan', 24, 'Paket telah dijemput dari pengirim.');
        $addEvent($customerShipment, 'IN_TRANSIT', 'Medan', 2, 'Paket berangkat dari Medan menuju Jakarta.');
        $addEvent($customerShipment, 'PORT_CONGESTION', 'Tanjung Priok', 1, 'Pelabuhan tujuan mengalami kepadatan dan estimasi tiba bergeser.', true);

        ShipmentException::firstOrCreate(
            [
                'shipment_id' => $customerShipment->id,
                'type' => 'PORT_CONGESTION',
            ],
            [
                'severity' => 'MEDIUM',
                'status' => 'INVESTIGATING',
                'notes' => 'Kepadatan pelabuhan memperlambat proses bongkar muat.',
                'detected_at' => now()->subHour(),
            ]
        );

        $seaShipment = $makeShipment(
            'CT-2026-001',
            $corporate,
            'NOL',
            'Tanjung Priok',
            'Rotterdam',
            'IN_TRANSIT',
            -12,
            5,
            'Komponen Mesin',
            [['description' => 'Komponen Mesin', 'quantity' => 1, 'weight_kg' => 450, 'volume_cbm' => 8]],
            [
                'transport_mode' => 'SEA',
                'sender_name' => 'PT Astra Mesin',
                'sender_phone' => '021-88912',
                'receiver_name' => 'EuroTech BV',
                'receiver_phone' => '+31-10442',
                'destination_address' => 'Havenstraat 45, Rotterdam, NL',
                'cost' => 11250000,
            ]
        );
        $seaShipment->forceFill([
            'estimated_arrival' => now()->addHours(24),
            'is_delayed' => true,
        ])->save();
        $addEvent($seaShipment, 'BOOKED', 'Tanjung Priok', 288, 'Booking kapal dikonfirmasi.');
        $addEvent($seaShipment, 'PICKED_UP', 'Tanjung Priok', 240, 'Kargo dimuat ke kapal.');
        $addEvent($seaShipment, 'IN_TRANSIT', 'Singapore', 72, 'Kapal transit melewati Singapura.');
        $addEvent($seaShipment, 'WEATHER_DELAY', 'Singapore', 1, 'Cuaca buruk dan gelombang tinggi menunda jadwal tiba.', true);

        ShipmentException::create([
            'shipment_id' => $seaShipment->id,
            'type' => 'WEATHER_DELAY',
            'severity' => 'HIGH',
            'status' => 'OPEN',
            'notes' => 'Cuaca badai dan gelombang tinggi di North Sea.',
            'detected_at' => now()->subHour(),
        ]);

        $airShipment = $makeShipment(
            'CT-2026-002',
            $corporate,
            'GCR',
            'Tanjung Priok',
            'Tokyo',
            'IN_TRANSIT',
            -3,
            1,
            'Semiconductor',
            [['description' => 'Semiconductor', 'quantity' => 10, 'weight_kg' => 120, 'volume_cbm' => 1.2]],
            [
                'transport_mode' => 'AIR',
                'sender_name' => 'CV Nusantara Chip',
                'sender_phone' => '021-55210',
                'receiver_name' => 'Tokyo Micro Corp',
                'receiver_phone' => '+81-3551',
                'destination_address' => 'Minato-ku 2-14, Tokyo, JP',
                'cost' => 9000000,
            ]
        );
        $addEvent($airShipment, 'BOOKED', 'Tanjung Priok', 72, 'Booking penerbangan dikonfirmasi.');
        $addEvent($airShipment, 'PICKED_UP', 'Tanjung Priok', 48, 'Semiconductor telah dimuat.');
        $addEvent($airShipment, 'IN_TRANSIT', 'Singapore', 6, 'Pesawat kargo dalam perjalanan menuju Tokyo.');

        UserNotification::create([
            'user_id' => $customer->id,
            'shipment_id' => $customerShipment->id,
            'message' => "Paket Anda {$customerShipment->shipment_number} sedang dalam perjalanan menuju Jakarta.",
        ]);
        UserNotification::create([
            'user_id' => $corporate->id,
            'shipment_id' => $seaShipment->id,
            'message' => "Shipment {$seaShipment->shipment_number} mengalami keterlambatan akibat cuaca buruk.",
        ]);
    }
}
