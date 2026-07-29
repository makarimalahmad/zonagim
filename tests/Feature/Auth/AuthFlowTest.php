<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\PendingRegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_authenticates_and_redirects_to_market(): void
    {
        $user = User::factory()->create([
            'email' => 'buyer@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'buyer@example.com',
            'password' => 'password',
            'cf_turnstile_response' => 'dummy-token',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('market', absolute: false));
    }

    public function test_otp_verification_creates_user_redirects_to_login_without_auto_login(): void
    {
        // Stub HIBP (uncompromised rule) supaya tidak bergantung jaringan.
        Http::fake([
            'api.pwnedpasswords.com/*' => Http::response("FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF:1\n", 200),
        ]);

        // Step 1 — registrasi: belum membuat user, simpan pending + OTP di cache.
        $register = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'Zx9k-Lm7Qw2pR!',
            'password_confirmation' => 'Zx9k-Lm7Qw2pR!',
            'cf_turnstile_response' => 'dummy-token',
        ]);

        $register->assertRedirect(route('verification.otp'));
        $this->assertDatabaseMissing('users', ['email' => 'newuser@example.com']);

        $pendingId = session('auth.verification.pending_id');
        $this->assertIsString($pendingId, 'ID registrasi tertunda harus tersimpan di sesi');
        $pending = app(PendingRegistrationService::class)->get($pendingId);
        $this->assertNotNull($pending, 'Registrasi tertunda harus tersimpan di cache');
        $this->assertArrayHasKey('otp_hash', $pending);
        $this->assertArrayNotHasKey('otp_code', $pending);
        $otp = app(PendingRegistrationService::class)->currentOtpForTesting($pendingId);
        $this->assertIsString($otp);

        // Step 2 — verifikasi OTP: buat user, redirect ke LOGIN, JANGAN auto-login.
        $verify = $this->post('/verify-otp', [
            'otp' => $otp,
        ]);

        $verify->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
    }
}
