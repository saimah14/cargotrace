<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ShipmentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_created_shipment_is_persisted_visible_to_its_customer_and_counted(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $otherCustomer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $carrier = Carrier::create(['name' => 'CargoTrace Air', 'code' => 'CTA']);
        $origin = Location::create([
            'name' => 'Jakarta',
            'country' => 'Indonesia',
            'latitude' => -6.2,
            'longitude' => 106.8,
        ]);
        $destination = Location::create([
            'name' => 'Tokyo',
            'country' => 'Japan',
            'latitude' => 35.6,
            'longitude' => 139.6,
        ]);

        $created = $this->actingAs($admin)->postJson('/api/shipments', [
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $origin->id,
            'destination_id' => $destination->id,
            'cargo_type' => 'Paket Skincare',
            'transport_mode' => 'AIR',
            'sender_name' => 'Toko Jakarta',
            'sender_phone' => '08123456789',
            'origin_address' => 'Jakarta Pusat',
            'receiver_name' => 'Nadia',
            'receiver_phone' => '08987654321',
            'destination_address' => 'Tokyo',
            'cost' => 250000,
            'expected_departure' => now()->toDateTimeString(),
            'expected_arrival' => now()->addDays(2)->toDateTimeString(),
            'items' => [[
                'description' => 'Paket Skincare',
                'quantity' => 1,
                'weight_kg' => 2.5,
            ]],
        ])->assertCreated()
            ->assertJsonPath('transport_mode', 'AIR')
            ->assertJsonPath('sender_name', 'Toko Jakarta')
            ->assertJsonPath('receiver_name', 'Nadia');

        $shipmentId = $created->json('id');
        $shipmentNumber = $created->json('shipment_number');

        $this->assertDatabaseHas('shipments', [
            'id' => $shipmentId,
            'customer_id' => $customer->id,
            'shipment_number' => $shipmentNumber,
        ]);
        $this->assertDatabaseHas('tracking_events', [
            'shipment_id' => $shipmentId,
            'event_type' => 'CREATED',
            'location_id' => $origin->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'shipment.created',
            'entity_id' => $shipmentId,
        ]);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $customer->id,
            'shipment_id' => $shipmentId,
        ]);

        $this->actingAs($customer)
            ->getJson('/api/shipments')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.shipment_number', $shipmentNumber)
            ->assertJsonPath('data.0.latest_tracking_event.event_type', 'CREATED')
            ->assertJsonPath('data.0.latest_tracking_event.location.name', 'Jakarta');

        $this->actingAs($otherCustomer)
            ->getJson('/api/shipments')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->actingAs($admin)
            ->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('total_shipments', 1)
            ->assertJsonPath('shipments_today', 1)
            ->assertJsonPath('shipments_this_month', 1)
            ->assertJsonPath('shipments_this_year', 1)
            ->assertJsonPath('revenue_this_month', '250000.00');

        $this->getJson('/api/audit-logs')
            ->assertOk()
            ->assertJsonPath('data.0.action', 'shipment.created');
    }

    public function test_admin_can_create_shipment_with_a_new_city_and_country(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $carrier = Carrier::create(['name' => 'CargoTrace Air', 'code' => 'CTA']);

        $this->actingAs($admin)->postJson('/api/shipments', [
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'origin_name' => 'Jakarta',
            'origin_country' => 'Indonesia',
            'origin_latitude' => -6.2088,
            'origin_longitude' => 106.8456,
            'destination_name' => 'Lisbon',
            'destination_country' => 'Portugal',
            'destination_latitude' => 38.7223,
            'destination_longitude' => -9.1393,
            'cargo_type' => 'Paket',
            'expected_departure' => now()->toDateTimeString(),
            'expected_arrival' => now()->addDays(3)->toDateTimeString(),
            'items' => [[
                'description' => 'Buku',
                'quantity' => 1,
                'weight_kg' => 1,
            ]],
        ])->assertCreated()
            ->assertJsonPath('origin.name', 'Jakarta')
            ->assertJsonPath('destination.name', 'Lisbon');

        $this->assertDatabaseHas('locations', [
            'name' => 'Lisbon',
            'country' => 'Portugal',
            'latitude' => 38.7223,
            'longitude' => -9.1393,
        ]);
    }

    public function test_weather_country_list_includes_country_names_and_local_aliases(): void
    {
        Cache::flush();
        Http::fake([
            'raw.githubusercontent.com/mledoze/countries/master/countries.json' => Http::response([
                [
                    'name' => ['common' => 'Japan'],
                    'translations' => ['ind' => ['common' => 'Jepang']],
                    'currencies' => ['JPY' => ['name' => 'Japanese yen', 'symbol' => '¥']],
                ],
                [
                    'name' => ['common' => 'Indonesia'],
                    'translations' => ['ind' => ['common' => 'Indonesia']],
                    'currencies' => ['IDR' => ['name' => 'Indonesian rupiah', 'symbol' => 'Rp']],
                ],
                [
                    'name' => ['common' => 'Antarctica'],
                    'translations' => ['ind' => ['common' => 'Antarktika']],
                    'currencies' => [],
                ],
            ], 200),
        ]);
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        $this->actingAs($customer)
            ->getJson('/api/countries')
            ->assertOk()
            ->assertJsonFragment(['countries' => ['Antarctica', 'Antarktika', 'Indonesia', 'Japan', 'Jepang']]);
    }
}
