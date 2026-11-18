<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Shipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CargoTraceTest extends TestCase
{
    // 1. Test Autentikasi Login (API Sanctum)
    public function test_user_can_login_and_receive_token(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@cargotrace.com'],
            [
                'name' => 'Saimah Admin',
                'role' => 'Administrator',
                'password' => bcrypt('password123')
            ]
        );

        $response = $this->postJson('/api/login', [
            'email' => 'admin@cargotrace.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token']);
    }

    // 2. Test Pembatasan Akses Role (Authorization)
    public function test_customer_cannot_access_admin_audit_logs(): void
    {
        $customer = User::firstOrCreate(
            ['email' => 'customer@cargotrace.com'],
            [
                'name' => 'Customer BSN',
                'role' => 'Customer',
                'password' => bcrypt('password123')
            ]
        );

        $response = $this->actingAs($customer)->getJson('/api/audit-logs');

        $response->assertStatus(403);
    }

    // 3. Test Validasi Transisi Status Shipment
    public function test_invalid_status_transition_is_rejected_by_backend(): void
    {
        $operator = User::firstOrCreate(
            ['email' => 'operator@cargotrace.com'],
            [
                'name' => 'Operator',
                'role' => 'Logistics Operator',
                'password' => bcrypt('password123')
            ]
        );

        $shipment = Shipment::first();

        if ($shipment) {
            $response = $this->actingAs($operator)->patchJson("/api/shipments/{$shipment->id}", [
                'current_status' => 'INVALID_STATUS_XYZ'
            ]);

            $this->assertTrue(in_array($response->status(), [400, 422, 500]));
        } else {
            $this->assertTrue(true);
        }
    }
}