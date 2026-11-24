<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserExceptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_list_shows_only_exceptions_for_each_customers_shipments(): void
    {
        $admin = User::factory()->create(['role' => 'Administrator']);
        $firstCustomer = User::factory()->create(['role' => 'customer']);
        $secondCustomer = User::factory()->create(['role' => 'Customer']);
        $carrier = Carrier::create(['name' => 'Cargo Carrier', 'code' => 'CARR']);
        $origin = Location::create([
            'name' => 'Jakarta',
            'country' => 'Indonesia',
            'latitude' => -6.2,
            'longitude' => 106.8,
        ]);
        $destination = Location::create([
            'name' => 'Singapore',
            'country' => 'Singapore',
            'latitude' => 1.3,
            'longitude' => 103.8,
        ]);

        $createShipment = function (User $customer, string $number) use ($carrier, $origin, $destination): Shipment {
            return Shipment::create([
                'shipment_number' => $number,
                'customer_id' => $customer->id,
                'carrier_id' => $carrier->id,
                'origin_id' => $origin->id,
                'destination_id' => $destination->id,
                'cargo_type' => 'General Cargo',
                'expected_departure' => now(),
                'expected_arrival' => now()->addDay(),
            ]);
        };

        $firstShipment = $createShipment($firstCustomer, 'CT-EXCEPTION-001');
        $firstShipment->exceptions()->create([
            'type' => 'WEATHER_DELAY',
            'severity' => 'HIGH',
            'status' => 'OPEN',
            'detected_at' => now(),
        ]);
        $firstShipment->trackingEvents()->create([
            'created_by' => $admin->id,
            'event_type' => 'IN_TRANSIT',
            'occurred_at' => now(),
            'description' => 'Shipment berangkat.',
        ]);
        UserNotification::create([
            'user_id' => $firstCustomer->id,
            'shipment_id' => $firstShipment->id,
            'message' => 'Shipment CT-EXCEPTION-001 sedang dalam perjalanan.',
        ]);

        $secondShipment = $createShipment($secondCustomer, 'CT-EXCEPTION-002');
        $secondShipment->exceptions()->create([
            'type' => 'CUSTOMS_HOLD',
            'severity' => 'MEDIUM',
            'status' => 'INVESTIGATING',
            'detected_at' => now(),
        ]);
        $secondShipment->trackingEvents()->create([
            'created_by' => $admin->id,
            'event_type' => 'IN_TRANSIT',
            'occurred_at' => now(),
            'description' => 'Shipment lain berangkat.',
        ]);
        UserNotification::create([
            'user_id' => $secondCustomer->id,
            'shipment_id' => $secondShipment->id,
            'message' => 'Shipment CT-EXCEPTION-002 sedang dalam perjalanan.',
        ]);

        $this->actingAs($admin)
            ->get('/')
            ->assertOk()
            ->assertSee('Operasional Shipment');

        $users = collect($this->getJson('/api/users')->assertOk()->json());

        $firstExceptions = $users->firstWhere('id', $firstCustomer->id)['exceptions'];
        $secondExceptions = $users->firstWhere('id', $secondCustomer->id)['exceptions'];

        $this->assertSame('CT-EXCEPTION-001', $firstExceptions[0]['shipment_number']);
        $this->assertSame('WEATHER_DELAY', $firstExceptions[0]['type']);
        $this->assertSame('CT-EXCEPTION-002', $secondExceptions[0]['shipment_number']);
        $this->assertSame('CUSTOMS_HOLD', $secondExceptions[0]['type']);

        $this->actingAs($firstCustomer)
            ->getJson('/api/shipments')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.shipment_number', 'CT-EXCEPTION-001');

        $this->getJson('/api/exceptions')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.shipment.shipment_number', 'CT-EXCEPTION-001');

        $this->getJson('/api/tracking')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.shipment.shipment_number', 'CT-EXCEPTION-001');

        $this->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonPath('notifications.data.0.shipment.shipment_number', 'CT-EXCEPTION-001');

        $this->getJson("/api/shipments/{$secondShipment->id}/exceptions")->assertForbidden();
        $this->getJson("/api/shipments/{$secondShipment->id}/tracking")->assertForbidden();
        $this->getJson('/api/users')->assertForbidden();
        $this->getJson('/api/audit-logs')->assertForbidden();
        $this->postJson('/api/carriers', ['name' => 'Unauthorized Carrier', 'code' => 'UNAUTH'])
            ->assertForbidden();

        $this->actingAs($admin)
            ->patchJson("/api/users/{$firstCustomer->id}/role", ['role' => 'operator'])
            ->assertOk()
            ->assertJsonPath('role', 'operator');

        $operator = User::factory()->create(['role' => 'Logistics Operator']);
        $createdShipment = $this->actingAs($operator)->postJson('/api/shipments', [
            'customer_id' => $secondCustomer->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $origin->id,
            'destination_id' => $destination->id,
            'cargo_type' => 'New Cargo',
            'expected_departure' => now()->toDateTimeString(),
            'expected_arrival' => now()->addDay()->toDateTimeString(),
            'items' => [[
                'description' => 'Test package',
                'quantity' => 1,
                'weight_kg' => 2,
                'volume_cbm' => 0.1,
            ]],
        ])->assertCreated();

        $shipmentId = $createdShipment->json('id');
        $this->postJson("/api/shipments/{$shipmentId}/tracking", [
            'event_type' => 'PICKED_UP',
            'occurred_at' => now()->toDateTimeString(),
            'description' => 'Cargo picked up.',
        ])->assertCreated();

        $exception = $this->postJson("/api/shipments/{$shipmentId}/exceptions", [
            'type' => 'WEATHER_DELAY',
            'severity' => 'MEDIUM',
            'notes' => 'Severe weather.',
        ])->assertCreated();

        $this->patchJson('/api/exceptions/'.$exception->json('exception.id'), [
            'status' => 'INVESTIGATING',
        ])->assertOk()->assertJsonPath('status', 'INVESTIGATING');
    }
}
