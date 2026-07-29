<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\UserSuspensionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UserSuspensionTest extends TestCase
{
    use RefreshDatabase;

    public function test_suspended_user_with_valid_credentials_receives_suspension_notice(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['suspended_at' => now()])->save();

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'cf_turnstile_response' => 'dummy-token',
        ]);

        $this->assertGuest();
        $response
            ->assertRedirect('/login')
            ->assertSessionHasErrors([
                'suspended' => 'Akun Anda telah ditangguhkan. Silakan hubungi administrator Zonagim untuk bantuan lebih lanjut.',
            ]);
    }

    public function test_suspended_user_with_invalid_password_receives_generic_error(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['suspended_at' => now()])->save();

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
            'cf_turnstile_response' => 'dummy-token',
        ]);

        $this->assertGuest();
        $response
            ->assertRedirect('/login')
            ->assertSessionHasErrors([
                'email' => 'Email atau kata sandi yang Anda masukkan tidak sesuai. Silakan periksa kembali.',
            ]);
    }

    public function test_existing_session_is_invalidated_after_user_is_suspended(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['suspended_at' => now()])->save();

        $response = $this->actingAs($user)->get('/profile');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    public function test_suspension_records_audit_data_rotates_token_and_revokes_all_database_sessions(): void
    {
        config(['session.driver' => 'database']);

        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create([
            'role' => 'user',
            'remember_token' => 'known-token',
        ]);

        DB::table('sessions')->insert([
            [
                'id' => 'session-one',
                'user_id' => $user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'PHPUnit',
                'payload' => '',
                'last_activity' => now()->timestamp,
            ],
            [
                'id' => 'session-two',
                'user_id' => $user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'PHPUnit',
                'payload' => '',
                'last_activity' => now()->timestamp,
            ],
        ]);

        $this->actingAs($admin);

        app(UserSuspensionService::class)->suspend($user, $admin, 'Pelanggaran ketentuan layanan.');

        $user->refresh();

        $this->assertTrue($user->isSuspended());
        $this->assertSame($admin->id, $user->suspended_by);
        $this->assertSame('Pelanggaran ketentuan layanan.', $user->suspension_reason);
        $this->assertNotSame('known-token', $user->remember_token);
        $this->assertDatabaseMissing('sessions', ['user_id' => $user->id]);
    }

    public function test_reactivation_clears_suspension_but_does_not_restore_old_session(): void
    {
        config(['session.driver' => 'database']);

        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $user->forceFill([
            'suspended_at' => now(),
            'suspended_by' => $admin->id,
            'suspension_reason' => 'Pelanggaran ketentuan layanan.',
        ])->save();

        DB::table('sessions')->insert([
            'id' => 'stale-session',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($admin);

        app(UserSuspensionService::class)->reactivate($user, $admin, 'password');

        $user->refresh();

        $this->assertFalse($user->isSuspended());
        $this->assertNull($user->suspended_by);
        $this->assertNull($user->suspension_reason);
        $this->assertDatabaseMissing('sessions', ['id' => 'stale-session']);
    }

    public function test_reactivation_rejects_direct_service_call_without_authenticated_admin_session(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $user->forceFill(['suspended_at' => now()])->save();

        $this->expectException(AuthorizationException::class);

        app(UserSuspensionService::class)->reactivate($user, $admin, 'password');
    }

    public function test_normal_user_cannot_reactivate_account_even_with_valid_password_and_session(): void
    {
        $actor = User::factory()->create(['role' => 'user']);
        $target = User::factory()->create(['role' => 'user']);
        $target->forceFill(['suspended_at' => now()])->save();

        $this->actingAs($actor);

        try {
            app(UserSuspensionService::class)->reactivate($target, $actor, 'password');
            $this->fail('User tanpa wewenang dapat mengaktifkan akun.');
        } catch (AuthorizationException) {
            $this->assertTrue($target->fresh()->isSuspended());
        }
    }

    public function test_admin_cannot_reactivate_account_with_wrong_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create(['role' => 'user']);
        $target->forceFill(['suspended_at' => now()])->save();

        $this->actingAs($admin);

        try {
            app(UserSuspensionService::class)->reactivate($target, $admin, 'wrong-password');
            $this->fail('Akun aktif tanpa verifikasi kata sandi admin.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('password', $exception->errors());
            $this->assertTrue($target->fresh()->isSuspended());
        }
    }

    public function test_policy_only_allows_narrow_status_actions_for_customer_targets(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'user']);
        $otherAdmin = User::factory()->create(['role' => 'admin']);

        $this->assertTrue(Gate::forUser($admin)->allows('suspend', $customer));
        $this->assertFalse(Gate::forUser($admin)->allows('suspend', $otherAdmin));
        $this->assertFalse(Gate::forUser($admin)->allows('update', $customer));

        $customer->forceFill(['suspended_at' => now()])->save();

        $this->assertTrue(Gate::forUser($admin)->allows('reactivate', $customer->fresh()));
    }
}
