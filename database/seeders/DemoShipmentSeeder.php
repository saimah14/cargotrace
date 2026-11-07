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

class DemoShipmentSeeder extends Seeder
{
    public function run(): void
    {
        if (Shipment::exists()) {
            return; // jangan menggandakan data demo
        }

        $customer = User::where('email', 'customer@cargotrace.test')->firstOrFail();
        $operator = User::where('email', 'operator@cargotrace.test')->firstOrFail();

        $carrier = fn (string $code) => Carrier::where('code', $code)->firstOrFail();
        $loc = fn (string $name) => Location::where('name', $name)->firstOrFail();

        $make = function (string $number, string $carrierCode, string $from, string $to, string $status, int $departDays, int $etaDays, string $cargo, array $items) use ($customer, $carrier, $loc) {
            $shipment = Shipment::create([
                'shipment_number' => $number,
                'customer_id' => $customer->id,
                'carrier_id' => $carrier($carrierCode)->id,
                'origin_id' => $loc($from)->id,
                'destination_id' => $loc($to)->id,
                'cargo_type' => $cargo,
                'expected_departure' => now()->addDays($departDays),
                'expected_arrival' => now()->addDays($etaDays),
            ]);

            $shipment->items()->createMany($items);
            $shipment->forceFill(['status' => $status])->save();

            return $shipment;
        };

        $event = function (Shipment $s, string $type, ?string $place, int $daysAgo, string $description, bool $isException = false) use ($loc, $operator) {
            $location = $place ? $loc($place) : null;

            return TrackingEvent::create([
                'shipment_id' => $s->id,
                'location_id' => $location?->id,
                'created_by' => $operator->id,
                'event_type' => $type,
                'occurred_at' => now()->subDays($daysAgo),
                'latitude' => $location?->latitude,
                'longitude' => $location?->longitude,
                'description' => $description,
                'is_exception' => $isException,
            ]);
        };

        $jkt = 'Tanjung Priok, Jakarta';
        $sin = 'Singapore Port';
        $klg = 'Port Klang';
        $cmb = 'Colombo Port';
        $rtm = 'Port of Rotterdam';
        $ham = 'Port of Hamburg';

        // 1. Dalam perjalanan, sesuai jadwal
        $s1 = $make('CT-DEMO-0001', 'NOL', $jkt, $rtm, 'IN_TRANSIT', -12, 10, 'Electronics', [
            ['description' => 'Laptops', 'quantity' => 100, 'weight_kg' => 250.5, 'volume_cbm' => 3.2],
            ['description' => 'Monitors', 'quantity' => 60, 'weight_kg' => 420, 'volume_cbm' => 5.5],
        ]);
        $event($s1, 'BOOKED', $jkt, 12, 'Booking confirmed');
        $event($s1, 'PICKED_UP', $jkt, 10, 'Cargo picked up at origin port');
        $event($s1, 'IN_TRANSIT', $sin, 8, 'Departed Singapore');
        $event($s1, 'ARRIVED_TRANSSHIPMENT', $cmb, 3, 'Arrived at transshipment port');

        // 2. Delayed karena customs hold
        $s2 = $make('CT-DEMO-0002', 'SFC', $sin, $ham, 'IN_TRANSIT', -15, 5, 'Textiles', [
            ['description' => 'Garment cartons', 'quantity' => 400, 'weight_kg' => 1800, 'volume_cbm' => 22],
        ]);
        $event($s2, 'BOOKED', $sin, 15, 'Booking confirmed');
        $event($s2, 'PICKED_UP', $sin, 13, 'Cargo picked up');
        $event($s2, 'IN_TRANSIT', $klg, 11, 'Departed Port Klang');
        $event($s2, 'CUSTOMS_HOLD', $cmb, 2, 'Held at customs for document inspection', true);
        ShipmentException::create([
            'shipment_id' => $s2->id,
            'type' => 'CUSTOMS_HOLD',
            'severity' => 'HIGH',
            'status' => 'OPEN',
            'notes' => 'Held at customs for document inspection',
            'detected_at' => now()->subDays(2),
        ]);
        $s2->forceFill([
            'estimated_arrival' => $s2->expected_arrival->copy()->addHours(30),
            'is_delayed' => true,
        ])->save();
        UserNotification::create([
            'user_id' => $customer->id,
            'shipment_id' => $s2->id,
            'message' => "Shipment {$s2->shipment_number} is delayed. New ETA: {$s2->estimated_arrival->toDateTimeString()}.",
        ]);

        // 3. Sudah diterima
        $s3 = $make('CT-DEMO-0003', 'ECL', $klg, $rtm, 'DELIVERED', -40, -5, 'Machinery parts', [
            ['description' => 'Spare parts crates', 'quantity' => 20, 'weight_kg' => 900, 'volume_cbm' => 12],
        ]);
        $event($s3, 'BOOKED', $klg, 40, 'Booking confirmed');
        $event($s3, 'PICKED_UP', $klg, 38, 'Cargo picked up');
        $event($s3, 'IN_TRANSIT', $cmb, 30, 'Departed Colombo');
        $event($s3, 'DELIVERED', $rtm, 5, 'Delivered to consignee');

        // 4. Baru dibuat
        $make('CT-DEMO-0004', 'NOL', $jkt, $ham, 'CREATED', 5, 30, 'Furniture', [
            ['description' => 'Teak furniture sets', 'quantity' => 50, 'weight_kg' => 1500, 'volume_cbm' => 30],
        ]);

        // 5. Sudah dibooking
        $s5 = $make('CT-DEMO-0005', 'SFC', $cmb, $rtm, 'BOOKED', 2, 25, 'Tea', [
            ['description' => 'Tea chests', 'quantity' => 300, 'weight_kg' => 6000, 'volume_cbm' => 40],
        ]);
        $event($s5, 'BOOKED', $cmb, 1, 'Booking confirmed');
    }
}