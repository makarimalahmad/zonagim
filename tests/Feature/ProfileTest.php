<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_page_shares_join_date(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('profile.created_at', $user->created_at->toJSON())
                ->missing('auth.user.phone')
                ->missing('auth.user.address')
                ->missing('auth.user.created_at')
            );
    }

    public function test_profile_information_can_be_updated_without_allowing_email_takeover(): void
    {
        $user = User::factory()->create();
        $originalEmail = $user->email;
        $originalVerification = $user->email_verified_at;

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'attacker@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Informasi profil berhasil diperbarui.')
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame($originalEmail, $user->email);
        $this->assertTrue($originalVerification->equalTo($user->email_verified_at));
    }

    public function test_phone_and_address_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '81234567890',
                'address' => [
                    'country' => 'Indonesia',
                    'province' => 'Jawa Barat',
                    'city' => 'Bandung',
                    'street' => 'Jalan Asia Afrika 1',
                    'zip' => '40111',
                ],
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Informasi profil berhasil diperbarui.')
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('81234567890', $user->phone);
        $this->assertSame('Bandung', $user->address['city']);
        $this->assertSame('40111', $user->address['zip']);
    }

    public function test_invalid_phone_and_postal_code_are_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/profile')
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '+628123',
                'address' => [
                    'country' => 'Indonesia',
                    'zip' => 'abc',
                ],
            ])
            ->assertSessionHasErrors([
                'phone' => 'Nomor WhatsApp harus diawali angka 8 dan berisi 9–14 digit.',
                'address.zip' => 'Kode pos harus terdiri dari 5 angka.',
            ])
            ->assertRedirect('/profile');
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Informasi profil berhasil diperbarui.')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrors('password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
