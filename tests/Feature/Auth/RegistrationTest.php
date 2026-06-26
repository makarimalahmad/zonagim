<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_start_otp_flow_and_are_not_logged_in_yet(): void
    {
        // Stub HIBP (aturan uncompromised) supaya tidak bergantung jaringan.
        Http::fake([
            'api.pwnedpasswords.com/*' => Http::response("FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF:1\n", 200),
        ]);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Zx9k-Lm7Qw2pR!',
            'password_confirmation' => 'Zx9k-Lm7Qw2pR!',
            'cf_turnstile_response' => 'dummy-token',
        ]);

        // Registrasi memicu alur OTP: user belum dibuat, belum login,
        // dan diarahkan ke halaman verifikasi OTP.
        $response->assertRedirect(route('verification.otp'));
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }
}
