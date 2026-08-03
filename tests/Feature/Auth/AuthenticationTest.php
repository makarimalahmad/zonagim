<?php

namespace Tests\Feature\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'cf_turnstile_response' => 'dummy-token',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('market', absolute: false));
    }

    public function test_users_return_to_safe_intended_url_after_login(): void
    {
        $user = User::factory()->create();
        $intended = route('profile.edit');

        $response = $this->withSession(['url.intended' => $intended])->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'cf_turnstile_response' => 'dummy-token',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect($intended);
    }

    public function test_external_intended_url_is_rejected_after_login(): void
    {
        $user = User::factory()->create();

        $response = $this->withSession([
            'url.intended' => 'https://example.com/phishing',
        ])->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'cf_turnstile_response' => 'dummy-token',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('market', absolute: false));
    }

    public function test_admin_intended_url_is_rejected_after_login(): void
    {
        $user = User::factory()->create();

        $response = $this->withSession([
            'url.intended' => url('/admin/products'),
        ])->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'cf_turnstile_response' => 'dummy-token',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('market', absolute: false));
    }

    public function test_admin_credentials_are_rejected_by_public_login_with_generic_error(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->from('/login')->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
            'cf_turnstile_response' => 'dummy-token',
        ]);

        $this->assertGuest();
        $response
            ->assertRedirect('/login')
            ->assertSessionHasErrors([
                'email' => 'Email atau kata sandi yang Anda masukkan tidak sesuai. Silakan periksa kembali.',
            ]);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
            'cf_turnstile_response' => 'dummy-token',
        ]);

        $this->assertGuest();
    }

    public function test_user_login_is_blocked_for_two_minutes_after_three_failures(): void
    {
        $user = User::factory()->create();
        $payload = [
            'email' => $user->email,
            'password' => 'wrong-password',
            'cf_turnstile_response' => 'dummy-token',
        ];

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            $this->from('/login')
                ->post('/login', $payload)
                ->assertSessionHasErrors([
                    'email' => 'Email atau kata sandi yang Anda masukkan tidak sesuai. Silakan periksa kembali.',
                ]);
        }

        $this->from('/login')
            ->post('/login', $payload)
            ->assertSessionHasErrors([
                'throttle' => 'Terlalu banyak percobaan login gagal. Coba lagi dalam 2 menit.',
            ]);

        $key = strtolower($user->email).'|127.0.0.1';
        $attempts = RateLimiter::attempts($key);

        $this->from('/login')
            ->post('/login', [
                ...$payload,
                'password' => 'password',
            ])
            ->assertSessionHasErrors([
                'throttle' => 'Terlalu banyak percobaan login gagal. Coba lagi dalam 2 menit.',
            ]);

        $this->assertSame(LoginRequest::MAX_FAILED_ATTEMPTS, $attempts);
        $this->assertSame($attempts, RateLimiter::attempts($key));
        $this->assertGreaterThan(0, RateLimiter::availableIn($key));
        $this->assertLessThanOrEqual(LoginRequest::LOCKOUT_SECONDS, RateLimiter::availableIn($key));
        $this->assertGuest();
    }

    public function test_user_login_page_restores_active_lockout_from_session(): void
    {
        $user = User::factory()->create();
        $key = strtolower($user->email).'|127.0.0.1';

        for ($attempt = 1; $attempt <= LoginRequest::MAX_FAILED_ATTEMPTS; $attempt++) {
            RateLimiter::hit($key, LoginRequest::LOCKOUT_SECONDS);
        }

        $this->withSession(['user-login-rate-limit-key' => $key])
            ->get('/login')
            ->assertInertia(fn ($page) => $page
                ->where('lockoutSeconds', fn (int $seconds): bool => $seconds > 0 && $seconds <= LoginRequest::LOCKOUT_SECONDS)
            );
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
