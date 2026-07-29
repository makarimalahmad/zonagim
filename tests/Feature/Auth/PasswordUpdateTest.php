<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_can_be_updated(): void
    {
        // Stub HIBP (aturan uncompromised) supaya tidak bergantung jaringan.
        Http::fake([
            'api.pwnedpasswords.com/*' => Http::response("FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF:1\n", 200),
        ]);

        $user = User::factory()->create();

        // Password harus memenuhi aturan kekuatan (huruf besar/kecil, angka, simbol).
        $newPassword = 'Zx9k-Lm7Qw2pR!';

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'password',
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Kata sandi berhasil diperbarui.')
            ->assertRedirect('/profile');

        $this->assertTrue(Hash::check($newPassword, $user->refresh()->password));
    }

    public function test_password_validation_messages_use_indonesian(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'password',
                'password' => 'Ab1!',
                'password_confirmation' => 'Ab1!',
            ])
            ->assertSessionHasErrors([
                'password' => 'Kata sandi baru minimal 8 karakter.',
            ]);
    }

    public function test_correct_password_must_be_provided_to_update_password(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'wrong-password',
                'password' => 'Zx9k-Lm7Qw2pR!',
                'password_confirmation' => 'Zx9k-Lm7Qw2pR!',
            ]);

        $response
            ->assertSessionHasErrors([
                'current_password' => 'Kata sandi saat ini tidak sesuai.',
            ])
            ->assertRedirect('/profile');
    }
}
