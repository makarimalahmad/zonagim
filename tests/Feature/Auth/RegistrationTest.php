<?php

namespace Tests\Feature\Auth;

use App\Models\User;
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

    public function test_registration_password_indicator_accepts_underscore_as_symbol(): void
    {
        $source = file_get_contents(
            dirname(__DIR__, 3).DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'Pages'.DIRECTORY_SEPARATOR.'Auth'.DIRECTORY_SEPARATOR.'Register.jsx',
        );

        $this->assertStringContainsString('<>_]', $source);
        $this->assertStringContainsString('Simbol (!@#$_)', $source);
    }

    public function test_existing_email_receives_informative_indonesian_error(): void
    {
        $user = User::factory()->create();

        $response = $this->from('/register')->post('/register', [
            'name' => 'Test User',
            'email' => $user->email,
            'password' => 'Zx9k-Lm7Qw2pR!',
            'password_confirmation' => 'Zx9k-Lm7Qw2pR!',
            'cf_turnstile_response' => 'dummy-token',
        ]);

        $response
            ->assertRedirect('/register')
            ->assertSessionHasErrors([
                'email' => 'Alamat email ini sudah terdaftar. Silakan gunakan email lain atau masuk ke akun Anda.',
            ]);
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
