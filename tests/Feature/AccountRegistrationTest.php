<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_and_can_register_from_the_login_page(): void
    {
        $this->get('/')
            ->assertRedirect(route('login'));
        $this->get('/dashboard-lama')
            ->assertRedirect(route('login'));
        $this->getJson('/api/rates')
            ->assertUnauthorized();
        $this->get('/login')
            ->assertOk()
            ->assertSee('Buat akun')
            ->assertDontSee('Coba akun demo')
            ->assertDontSee('admin@cargotrace.com')
            ->assertDontSee('operator@cargotrace.com')
            ->assertDontSee('customer@cargotrace.com')
            ->assertDontSee('password123');

        $credentials = [
            'name' => 'CargoTrace Customer',
            'email' => 'new-customer@example.com',
            'password' => 'secure-pass-123',
            'password_confirmation' => 'secure-pass-123',
        ];

        $this->post('/register', $credentials)
            ->assertRedirect('/');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => $credentials['email'],
            'role' => 'customer',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Exception Pengiriman');

        $this->postJson('/api/register', [
            'name' => 'API Customer',
            'email' => 'api-customer@example.com',
            'password' => 'secure-pass-123',
            'password_confirmation' => 'secure-pass-123',
        ])
            ->assertCreated()
            ->assertJsonPath('user.role', 'customer')
            ->assertJsonStructure(['token']);
        $this->assertDatabaseHas('users', [
            'email' => 'api-customer@example.com',
            'role' => 'customer',
        ]);

        $this->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();

        $this->post('/login', [
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ])->assertRedirect('/');
        $this->assertAuthenticated();
    }
}
