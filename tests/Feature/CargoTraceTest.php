<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CargoTraceTest extends TestCase
{
    use RefreshDatabase;
    // Pengaman: tolak menjalankan test kalau bukan di database *_test
    protected function beforeRefreshingDatabase(): void
    {
        $db = (string) config('database.connections.'.config('database.default').'.database');

        if (! str_ends_with($db, '_test')) {
            throw new \RuntimeException("Refusing to run tests on non-test database: {$db}");
        }
    }
    

    private function makeUser(string $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role])->save();

        return $user;
    }

    private function makeShipment(User $customer): Shipment
    {
        $carrier = Carrier::create(['name' => 'Test Line', 'code' => 'TST']);
        $origin = Location::create(['name' => 'Jakarta', 'country' => 'Indonesia', 'latitude' => -6.1, 'longitude' => 106.8]);
        $destination = Location::create(['name' => 'Rotterdam', 'country' => 'Netherlands', 'latitude' => 51.9, 'longitude' => 4.1]);

        return Shipment::create([
            'shipment_number' => 'CT-TEST-0001',
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $origin->id,
            'destination_id' => $destination->id,
            'cargo_type' => 'Electronics',
            'expected_departure' => now()->addDay(),
            'expected_arrival' => now()->addDays(20),
        ]);
    }

    public function test_login_succeeds_with_valid_credentials(): void
    {
        $user = $this->makeUser('admin');

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'password'])
            ->assertOk()
            ->assertJsonStructure(['user', 'token']);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = $this->makeUser('admin');

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertStatus(401);
    }

    public function test_customer_cannot_manage_carriers(): void
    {
        Sanctum::actingAs($this->makeUser('customer'));

        $this->postJson('/api/carriers', ['name' => 'X Line', 'code' => 'XLN'])
            ->assertStatus(403);
    }

    public function test_admin_can_create_carrier(): void
    {
        Sanctum::actingAs($this->makeUser('admin'));

        $this->postJson('/api/carriers', ['name' => 'X Line', 'code' => 'XLN'])
            ->assertStatus(201);

        $this->assertDatabaseHas('carriers', ['code' => 'XLN']);
    }

    public function test_customer_cannot_view_another_customers_shipment(): void
    {
        $owner = $this->makeUser('customer');
        $shipment = $this->makeShipment($owner);

        Sanctum::actingAs($this->makeUser('customer'));

        $this->getJson('/api/shipments/'.$shipment->id)->assertStatus(403);
    }

    public function test_shipment_rejects_arrival_before_departure(): void
    {
        $customer = $this->makeUser('customer');
        $shipment = $this->makeShipment($customer);

        Sanctum::actingAs($this->makeUser('operator'));

        $this->postJson('/api/shipments', [
            'customer_id' => $customer->id,
            'carrier_id' => $shipment->carrier_id,
            'origin_id' => $shipment->origin_id,
            'destination_id' => $shipment->destination_id,
            'cargo_type' => 'Electronics',
            'expected_departure' => now()->addDays(10)->toDateTimeString(),
            'expected_arrival' => now()->addDays(2)->toDateTimeString(),
            'items' => [['description' => 'Laptop', 'quantity' => 1, 'weight_kg' => 10]],
        ])->assertStatus(422);
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        $shipment = $this->makeShipment($this->makeUser('customer'));

        Sanctum::actingAs($this->makeUser('operator'));

        $this->postJson('/api/shipments/'.$shipment->id.'/status', ['status' => 'DELIVERED'])
            ->assertStatus(409);
    }

    public function test_valid_status_transition_creates_tracking_event(): void
    {
        $shipment = $this->makeShipment($this->makeUser('customer'));

        Sanctum::actingAs($this->makeUser('operator'));

        $this->postJson('/api/shipments/'.$shipment->id.'/status', ['status' => 'BOOKED'])
            ->assertOk();

        $this->assertDatabaseHas('shipments', ['id' => $shipment->id, 'status' => 'BOOKED']);
        $this->assertDatabaseHas('tracking_events', ['shipment_id' => $shipment->id, 'event_type' => 'BOOKED']);
    }

    public function test_exception_over_threshold_marks_shipment_delayed_and_notifies_customer(): void
    {
        $customer = $this->makeUser('customer');
        $shipment = $this->makeShipment($customer);

        Sanctum::actingAs($this->makeUser('operator'));

        $this->postJson('/api/shipments/'.$shipment->id.'/exceptions', [
            'type' => 'CUSTOMS_HOLD',
            'severity' => 'HIGH',
            'delay_hours' => 30,
        ])->assertStatus(201);

        $this->assertTrue($shipment->fresh()->is_delayed);
        $this->assertDatabaseHas('user_notifications', ['user_id' => $customer->id, 'shipment_id' => $shipment->id]);
    }

    public function test_exception_under_threshold_does_not_mark_shipment_delayed(): void
    {
        $shipment = $this->makeShipment($this->makeUser('customer'));

        Sanctum::actingAs($this->makeUser('operator'));

        $this->postJson('/api/shipments/'.$shipment->id.'/exceptions', [
            'type' => 'WEATHER_DELAY',
            'severity' => 'LOW',
            'delay_hours' => 6,
        ])->assertStatus(201);

        $this->assertFalse($shipment->fresh()->is_delayed);
    }
}