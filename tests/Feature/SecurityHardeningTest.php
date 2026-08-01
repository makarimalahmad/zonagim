<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Services\PendingRegistrationService;
use App\Services\UserSuspensionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('otp-ip:127.0.0.1');
    }

    public function test_security_headers_are_applied_and_sensitive_pages_are_not_cached(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Cache-Control', 'no-store, private');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/verify-email/'.$user->id.'/hash')
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_shared_user_props_exclude_private_profile_and_security_fields(): void
    {
        $user = User::factory()->create([
            'phone' => '81234567890',
            'address' => ['street' => 'Jalan Rahasia'],
        ]);

        $this->actingAs($user)
            ->get('/market')
            ->assertInertia(fn ($page) => $page
                ->where('auth.user.name', $user->name)
                ->where('auth.user.email', $user->email)
                ->missing('auth.user.id')
                ->missing('auth.user.phone')
                ->missing('auth.user.address')
                ->missing('auth.user.created_at')
            );
    }

    public function test_user_serialization_hides_security_fields(): void
    {
        $user = User::factory()->create();
        $serialized = $user->toArray();

        foreach ([
            'password',
            'remember_token',
            'otp_code',
            'otp_expires_at',
            'suspended_by',
            'suspension_reason',
            'suspended_at',
            'app_authentication_secret',
            'app_authentication_recovery_codes',
        ] as $field) {
            $this->assertArrayNotHasKey($field, $serialized);
        }
    }

    public function test_trusted_host_middleware_is_enabled_in_application_bootstrap(): void
    {
        $bootstrap = file_get_contents(base_path('bootstrap/app.php'));

        $this->assertStringContainsString('$middleware->trustHosts(', $bootstrap);
        $this->assertStringNotContainsString("trustProxies(at: '*'", $bootstrap);
        $this->assertStringNotContainsString('HEADER_X_FORWARDED_HOST', $bootstrap);
    }

    public function test_reset_link_always_uses_canonical_app_url(): void
    {
        config(['app.url' => 'https://zonagim.my.id']);

        $user = User::factory()->create();
        $mail = (new ResetPasswordNotification('secure-token'))->toMail($user);
        $viewData = $mail->viewData;

        $this->assertStringStartsWith('https://zonagim.my.id/reset-password/', $viewData['url']);
        $this->assertStringNotContainsString('attacker.example', $viewData['url']);
    }

    public function test_json_ld_escapes_script_termination_from_stored_content(): void
    {
        Category::create(['name' => '</script><script>alert(1)</script>']);

        $response = $this->get('/market');

        $response->assertOk();
        $this->assertStringNotContainsString('</script><script>alert(1)</script>', $response->getContent());

        $template = file_get_contents(resource_path('views/app.blade.php'));
        $this->assertStringContainsString('JSON_HEX_TAG', $template);
        $this->assertStringContainsString('JSON_HEX_AMP', $template);
    }

    public function test_otp_is_session_bound_hashed_expires_and_locks_after_five_failures(): void
    {
        $pendingRegistrations = app(PendingRegistrationService::class);
        $pending = $pendingRegistrations->create([
            'name' => 'Calon Pengguna',
            'email' => 'pending@example.com',
            'password' => Hash::make('Strong1!Password'),
        ]);
        $stored = $pendingRegistrations->get($pending['id']);

        $this->assertArrayHasKey('otp_hash', $stored);
        $this->assertArrayNotHasKey('otp_code', $stored);
        $this->assertNotSame($pending['otp'], $stored['otp_hash']);

        for ($attempt = 1; $attempt <= 4; $attempt++) {
            $response = $this->withSession([
                'auth.verification.pending_id' => $pending['id'],
                'auth.verification.email' => 'pending@example.com',
            ])->post('/verify-otp', ['otp' => '000000']);

            $response->assertSessionHasErrors('otp');
        }

        $this->withSession([
            'auth.verification.pending_id' => $pending['id'],
            'auth.verification.email' => 'pending@example.com',
        ])->post('/verify-otp', ['otp' => '000000'])
            ->assertSessionHasErrors([
                'otp' => 'Terlalu banyak percobaan. Silakan daftar ulang untuk memperoleh kode baru.',
            ]);

        $this->assertNull($pendingRegistrations->get($pending['id']));
        $this->assertDatabaseMissing('users', ['email' => 'pending@example.com']);
    }

    public function test_otp_cannot_be_verified_without_matching_server_session(): void
    {
        $pending = app(PendingRegistrationService::class)->create([
            'name' => 'Calon Pengguna',
            'email' => 'pending@example.com',
            'password' => Hash::make('Strong1!Password'),
        ]);

        $this->post('/verify-otp', [
            'email' => 'pending@example.com',
            'otp' => $pending['otp'],
        ])->assertSessionHasErrors('otp');

        $this->assertDatabaseMissing('users', ['email' => 'pending@example.com']);
    }

    public function test_password_reset_revokes_all_existing_database_sessions(): void
    {
        config(['session.driver' => 'database']);

        $user = User::factory()->create();
        DB::table('sessions')->insert([
            'id' => 'stolen-session',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ]);

        $token = app('auth.password.broker')->createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewStrong1!Password',
            'password_confirmation' => 'NewStrong1!Password',
        ])->assertRedirect('/login');

        $this->assertDatabaseMissing('sessions', ['id' => 'stolen-session']);
    }

    public function test_direct_suspend_call_without_authenticated_admin_session_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create(['role' => 'user']);

        $this->expectException(AuthorizationException::class);

        app(UserSuspensionService::class)->suspend(
            $target,
            $admin,
            'Pelanggaran ketentuan layanan.',
        );
    }

    public function test_unsafe_intended_redirect_with_backslash_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->withSession(['url.intended' => '/\\attacker.example'])
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password',
                'cf_turnstile_response' => 'dummy-token',
            ]);

        $response->assertRedirect(route('market'));
    }
}
