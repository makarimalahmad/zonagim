<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
