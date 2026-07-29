<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_public_reset_does_not_send_link_to_admin_account(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->post('/forgot-password', ['email' => $admin->email]);

        Notification::assertNothingSent();
        $response
            ->assertSessionHasNoErrors()
            ->assertSessionHas(
                'status',
                'Jika email terdaftar sebagai akun pengguna, tautan reset telah dikirim.',
            );
    }

    public function test_public_reset_uses_same_response_for_unknown_email(): void
    {
        Notification::fake();

        $response = $this->post('/forgot-password', [
            'email' => 'tidak-ada@example.com',
        ]);

        Notification::assertNothingSent();
        $response->assertSessionHas(
            'status',
            'Jika email terdaftar sebagai akun pengguna, tautan reset telah dikirim.',
        );
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) {
            $response = $this->get('/reset-password/'.$notification->token);

            $response->assertStatus(200);

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        // Stub HIBP (aturan uncompromised) supaya tidak bergantung jaringan.
        Http::fake([
            'api.pwnedpasswords.com/*' => Http::response("FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF:1\n", 200),
        ]);

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
            // Password harus memenuhi aturan kekuatan (huruf besar/kecil, angka, simbol).
            $newPassword = 'Zx9k-Lm7Qw2pR!';

            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            return true;
        });
    }
}
