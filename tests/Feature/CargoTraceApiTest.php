<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CargoTraceApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'role'     => 'Administrator',
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token']);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => $user->email,
            'password' => 'password-salah',
        ]);

        $this->assertTrue(in_array($response->status(), [401, 422]));
    }

    public function test_unauthenticated_user_cannot_access_shipments_api(): void
    {
        $response = $this->getJson('/api/shipments');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_fetch_shipments_list(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'role'     => 'Administrator',
        ]);

        $login = $this->postJson('/api/login', [
            'email'    => $user->email,
            'password' => 'password123',
        ]);

        $token = $login->json('token');

        $response = $this->withToken($token)->getJson('/api/shipments');

        $response->assertStatus(200);
    }
}