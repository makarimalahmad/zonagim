<?php

namespace Tests\Feature;

use App\Filament\Auth\MultiFactor\AppAuthentication;
use App\Filament\Auth\MultiFactor\SetUpAppAuthenticationAction;
use App\Filament\Auth\MultiFactor\SetUpRequiredMultiFactorAuthentication;
use App\Filament\Pages\MfaSecurity;
use App\Models\AdminTotpDevice;
use App\Models\User;
use App\Services\AdminTotpDeviceService;
use Filament\Auth\MultiFactor\Http\Middleware\EnsureMultiFactorAuthenticationIsEnabled;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use PragmaRX\Google2FAQRCode\Google2FA;
use Tests\TestCase;

class AdminMfaFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_unenrolled_admin_is_forced_to_mfa_setup_before_panel_access(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertRedirect('/admin/multi-factor-authentication/set-up');

        $this->actingAs($admin)
            ->get('/admin/products')
            ->assertRedirect('/admin/multi-factor-authentication/set-up');
    }

    public function test_enrolled_admin_can_access_panel(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createDevice($admin, 'Ponsel utama', 'JBSWY3DPEHPK3PXP');

        $this->actingAs($admin)->get('/admin')->assertOk();
    }

    public function test_first_enrollment_page_is_simple_and_setup_action_mounts_without_error(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $this->get('/admin/multi-factor-authentication/set-up')
            ->assertOk()
            ->assertSee('Daftarkan perangkat')
            ->assertDontSee('Daftarkan perangkat pertama')
            ->assertDontSee('Perangkat terdaftar · 0/3')
            ->assertDontSee('Daftarkan maksimal tiga perangkat');

        Livewire::test(SetUpRequiredMultiFactorAuthentication::class)
            ->mountAction('setUpAppAuthentication')
            ->assertHasNoErrors();
    }

    public function test_mfa_has_no_skip_or_bypass_and_uses_native_required_middleware(): void
    {
        $provider = file_get_contents(app_path('Providers/Filament/AdminPanelProvider.php'));
        $setupPage = file_get_contents(
            app_path('Filament/Auth/MultiFactor/SetUpRequiredMultiFactorAuthentication.php'),
        );

        $this->assertStringContainsString(SetUpRequiredMultiFactorAuthentication::class, $provider);
        $this->assertStringContainsString('isRequired: true', $provider);
        $this->assertStringNotContainsString('EnsureAdminMfaEnrollment', $provider);
        $this->assertStringNotContainsString('skipMfaEnrollment', $setupPage);
        $this->assertStringNotContainsString('Lewati untuk sekarang', $setupPage);
        $this->assertSame(
            EnsureMultiFactorAuthenticationIsEnabled::class,
            Filament::getPanel('admin')->getMultiFactorAuthenticationRequiredMiddlewareName(),
        );
    }

    public function test_setup_requires_two_consecutive_codes_but_login_challenge_uses_one_code(): void
    {
        $setupAction = file_get_contents(
            app_path('Filament/Auth/MultiFactor/SetUpAppAuthenticationAction.php'),
        );
        $login = file_get_contents(app_path('Filament/Auth/Login.php'));

        $this->assertStringContainsString("TextInput::make('firstCode')", $setupAction);
        $this->assertStringContainsString("TextInput::make('secondCode')", $setupAction);
        $this->assertStringContainsString('verifyKeyNewer(', $setupAction);
        $this->assertStringContainsString('$secondTimestamp !== $firstTimestamp + 1', $setupAction);
        $this->assertStringContainsString("Action::make('showInitialRecoveryCodes')", $setupAction);
        $this->assertStringNotContainsString("Action::make('downloadRecoveryCodes')", $setupAction);
        $this->assertStringContainsString('->modalFooterActionsAlignment(Alignment::End)', $setupAction);

        $theme = str_replace(
            "\r\n",
            "\n",
            file_get_contents(resource_path('css/filament/admin/theme.css')),
        );
        $this->assertStringContainsString(".admin-mfa-aws-modal .fi-modal-content {\n    min-height: 0;\n    flex: 1 1 auto;", $theme);
        $this->assertStringContainsString('overflow-y: auto;', $theme);
        $this->assertStringNotContainsString('.admin-mfa-aws-modal:has(.admin-mfa-reveal-panel)', $theme);
        $this->assertStringNotContainsString('function authenticate(', $login);

        $provider = AppAuthentication::make()->recoverable();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createDevice($admin, 'Ponsel utama', 'JBSWY3DPEHPK3PXP');
        $challengeComponents = $provider->getChallengeFormComponents($admin);

        $this->assertCount(2, $challengeComponents);
        $this->assertSame('code', $challengeComponents[0]->getName());
        $this->assertSame('recoveryCode', $challengeComponents[1]->getName());
    }

    public function test_enrolled_admin_can_open_mfa_security_management_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createDevice($admin, 'Ponsel utama', 'JBSWY3DPEHPK3PXP');

        $this->actingAs($admin)
            ->get('/admin/keamanan-akun')
            ->assertOk()
            ->assertSee('Keamanan Akun')
            ->assertSee('MFA Aktif')
            ->assertSee('1 dari 3 perangkat terdaftar')
            ->assertSee('Ponsel utama')
            ->assertDontSee('Perangkat terdaftar ·');
    }

    public function test_account_security_page_uses_single_status_surface_without_nested_card(): void
    {
        $page = file_get_contents(app_path('Filament/Pages/MfaSecurity.php'));
        $theme = file_get_contents(resource_path('css/filament/admin/theme.css'));

        $this->assertStringContainsString("Section::make(\$isEnabled ? 'MFA Aktif'", $page);
        $this->assertStringNotContainsString("Text::make(\$isEnabled ? 'MFA Aktif'", $page);
        $this->assertStringContainsString('.admin-account-security-card > .fi-section', $theme);
        $this->assertStringNotContainsString(".admin-account-mfa-actions {\n    padding:", $theme);
    }

    public function test_user_menu_exposes_mfa_status_and_management_link(): void
    {
        $provider = file_get_contents(app_path('Providers/Filament/AdminPanelProvider.php'));

        $this->assertStringContainsString("Action::make('mfaSecurity')", $provider);
        $this->assertStringContainsString("->label('Keamanan Akun')", $provider);
        $this->assertStringNotContainsString('Keamanan MFA · Aktif', $provider);
        $this->assertStringContainsString('MfaSecurity::getUrl()', $provider);
    }

    public function test_mfa_management_uses_step_up_verification_without_global_disable(): void
    {
        $regenerate = file_get_contents(
            app_path('Filament/Auth/MultiFactor/RegenerateRecoveryCodesAction.php'),
        );
        $setup = file_get_contents(
            app_path('Filament/Auth/MultiFactor/SetUpAppAuthenticationAction.php'),
        );
        $provider = file_get_contents(
            app_path('Filament/Auth/MultiFactor/AppAuthentication.php'),
        );

        $this->assertStringContainsString("ToggleButtons::make('method')", $regenerate);
        $this->assertStringContainsString("'authenticator' => 'Kode autentikator'", $regenerate);
        $this->assertStringContainsString("'class' => 'admin-mfa-recovery-codes-modal'", $regenerate);
        $this->assertStringContainsString("Actions::make([\n                                Action::make('copy')", str_replace("\r\n", "\n", $regenerate));
        $this->assertStringContainsString("Action::make('download')", $regenerate);
        $this->assertStringContainsString('->modalFooterActionsAlignment(Alignment::End)', $regenerate);

        $theme = file_get_contents(resource_path('css/filament/admin/theme.css'));
        $this->assertStringContainsString('.admin-mfa-recovery-code-list ul', $theme);
        $this->assertStringContainsString('grid-template-columns: repeat(2, minmax(0, 1fr));', $theme);
        $this->assertStringContainsString('list-style: none;', $theme);
        $this->assertStringContainsString('font-variant-numeric: tabular-nums;', $theme);
        $this->assertStringContainsString("'password' => 'Kata sandi'", $regenerate);
        $this->assertStringNotContainsString("ToggleButtons::make('verificationMethod')", $setup);
        $this->assertStringNotContainsString("OneTimeCodeInput::make('verificationCode')", $setup);
        $this->assertStringContainsString("Action::make('confirmAdditionalDevicePassword')", $setup);
        $this->assertStringContainsString("->modalHeading('Konfirmasi kata sandi admin')", $setup);
        $this->assertStringContainsString('Untuk menambahkan perangkat MFA, silakan masukkan kata sandi admin.', $setup);
        $this->assertStringContainsString("Action::make('additionalDeviceEnrollmentFailed')", $setup);
        $this->assertStringContainsString("->registerModalActions([\n                        Action::make('additionalDeviceEnrollmentFailed')", str_replace("\r\n", "\n", $setup));
        $this->assertStringContainsString("->modalHeading('Gagal menambahkan perangkat MFA')", $setup);
        $this->assertStringContainsString("'mountedActions.'.(\$action->getNestingIndex() ?? 0).'.data.password'", $setup);
        $this->assertStringContainsString("'Kata sandi admin salah. Silakan coba lagi.'", $setup);
        $this->assertStringContainsString('$action->halt();', $setup);
        $this->assertStringContainsString('AdminTotpDeviceService::PASSWORD_MAX_ATTEMPTS', $setup);
        $this->assertStringContainsString('getManagementSchemaComponents()', $provider);
        $this->assertStringContainsString("->label('Kelola keamanan')", $provider);
        $this->assertStringContainsString("->modalHeading('Gagal menghapus perangkat')", $provider);
        $this->assertStringContainsString("->modalIconColor('danger')", $provider);
        $this->assertStringContainsString("'class' => 'admin-mfa-error-modal'", $provider);
        $this->assertStringContainsString('->modalCloseButton(false)', $provider);
        $this->assertStringContainsString('->closeModalByClickingAway(false)', $provider);
        $this->assertStringContainsString('->closeModalByEscaping(false)', $provider);

        $theme = str_replace(
            "\r\n",
            "\n",
            file_get_contents(resource_path('css/filament/admin/theme.css')),
        );
        $this->assertStringContainsString('.admin-mfa-error-modal.fi-modal-window', $theme);
        $this->assertStringContainsString('border: 2px solid #ef4444;', $theme);
        $this->assertStringContainsString(".admin-mfa-error-modal.fi-modal-window {\n    overflow: hidden;\n    border: 2px solid #ef4444;\n    border-radius: 1rem;\n    box-shadow: none;", $theme);
        $this->assertStringContainsString('.admin-mfa-error-modal .fi-modal-icon', $theme);
        $this->assertStringContainsString(".admin-mfa-error-modal .fi-modal-header {\n    border-bottom: 0;", $theme);
        $this->assertStringContainsString(".admin-mfa-error-modal .fi-modal-footer {\n    border-top: 0;", $theme);
        $this->assertStringContainsString('background: transparent;', $theme);
        $this->assertStringNotContainsString('DisableAppAuthenticationAction', $provider);
        $this->assertFileDoesNotExist(
            app_path('Filament/Auth/MultiFactor/DisableAppAuthenticationAction.php'),
        );
    }

    public function test_admin_can_register_at_most_three_named_devices(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $service = app(AdminTotpDeviceService::class);

        foreach ([
            ['Ponsel utama', 'JBSWY3DPEHPK3PXP'],
            ['Laptop kerja', 'KRSXG5DSNFXGOIDB'],
            ['Tablet cadangan', 'MFRGGZDFMZTWQ2LK'],
        ] as $index => [$name, $secret]) {
            $payload = $service->pendingEnrollment(
                $admin,
                $name,
                $secret,
                $index === 0 ? null : 'password',
            );
            $service->activate($admin, $payload, 1);
        }

        $this->assertSame(3, $admin->totpDevices()->count());
        $this->assertSame(
            ['Ponsel utama', 'Laptop kerja', 'Tablet cadangan'],
            $admin->totpDevices()->orderBy('slot')->pluck('name')->all(),
        );

        $this->expectException(ValidationException::class);
        $service->pendingEnrollment($admin, 'Perangkat keempat', 'ONSWG4TFOQ======');
    }

    public function test_duplicate_device_name_is_rejected_before_password_step_up(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $service = app(AdminTotpDeviceService::class);
        $this->createDevice($admin, 'Ponsel Utama', 'JBSWY3DPEHPK3PXP');

        $this->assertTrue($service->deviceNameExists($admin, '  ponsel   utama  '));

        try {
            $service->pendingEnrollment(
                $admin,
                '  ponsel   utama  ',
                'KRSXG5DSNFXGOIDB',
            );
            $this->fail('Nama perangkat duplikat diterima.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                'Nama perangkat sudah terpakai. Gunakan nama lain.',
                $exception->errors()['deviceName'][0],
            );
            $this->assertArrayNotHasKey('password', $exception->errors());
        }
    }

    public function test_each_registered_device_can_verify_but_a_code_cannot_be_reused(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $service = app(AdminTotpDeviceService::class);
        $google2FA = app(Google2FA::class);

        $firstSecret = 'JBSWY3DPEHPK3PXP';
        $secondSecret = 'KRSXG5DSNFXGOIDB';
        $this->createDevice($admin, 'Ponsel utama', $firstSecret);
        $this->createDevice($admin, 'Laptop kerja', $secondSecret);

        $firstCode = $google2FA->getCurrentOtp($firstSecret);
        $secondCode = $google2FA->getCurrentOtp($secondSecret);

        $this->assertTrue($service->verifyAny($admin, $firstCode));
        $this->assertFalse($service->verifyAny($admin, $firstCode));
        $this->assertTrue($service->verifyAny($admin, $secondCode));
    }

    public function test_additional_device_requires_existing_mfa_or_password_step_up(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $service = app(AdminTotpDeviceService::class);
        $secret = 'JBSWY3DPEHPK3PXP';
        $this->createDevice($admin, 'Ponsel utama', $secret);

        try {
            $service->pendingEnrollment($admin, 'Laptop kerja', 'KRSXG5DSNFXGOIDB');
            $this->fail('Perangkat tambahan dapat didaftarkan tanpa verifikasi ulang.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('password', $exception->errors());
        }

        $payload = $service->pendingEnrollment(
            $admin,
            'Laptop kerja',
            'KRSXG5DSNFXGOIDB',
            'password',
        );
        $service->activate($admin, $payload, 1);

        $this->assertSame(2, $admin->totpDevices()->count());
    }

    public function test_three_wrong_additional_device_passwords_trigger_failure_modal(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createDevice($admin, 'Ponsel utama', 'JBSWY3DPEHPK3PXP');
        $this->actingAs($admin);
        $enrollmentId = (string) Str::uuid();
        $service = app(AdminTotpDeviceService::class);

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                $service->confirmAdditionalDevicePassword($admin, $enrollmentId, 'salah');
                $this->fail('Kata sandi salah diterima.');
            } catch (ValidationException $exception) {
                $this->assertSame(
                    $attempt === 3
                        ? 'Batas percobaan tercapai. Ulangi proses pendaftaran perangkat.'
                        : 'Kata sandi admin tidak valid.',
                    $exception->errors()['password'][0],
                );
            }

            $this->assertSame($attempt, $service->passwordAttempts($admin, $enrollmentId));
        }

        $this->assertSame(3, $service->passwordAttempts($admin, $enrollmentId));
        $this->assertSame(1, $admin->totpDevices()->count());
        $this->assertDatabaseMissing('admin_totp_devices', [
            'user_id' => $admin->getKey(),
            'name' => 'Laptop kerja',
        ]);
    }

    public function test_password_modal_registers_failure_modal_as_direct_child(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createDevice($admin, 'Ponsel utama', 'JBSWY3DPEHPK3PXP');
        $this->actingAs($admin);
        $livewire = Livewire::test(MfaSecurity::class)->instance();
        $setup = SetUpAppAuthenticationAction::make(AppAuthentication::make()->recoverable())
            ->livewire($livewire);
        $passwordModal = $setup->getModalAction('confirmAdditionalDevicePassword');

        $this->assertNotNull($passwordModal);
        $this->assertNotNull($passwordModal->getModalAction('additionalDeviceEnrollmentFailed'));
        $this->assertNull($setup->getModalAction('additionalDeviceEnrollmentFailed'));
    }

    public function test_correct_additional_device_password_clears_attempts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createDevice($admin, 'Ponsel utama', 'JBSWY3DPEHPK3PXP');
        $this->actingAs($admin);
        $service = app(AdminTotpDeviceService::class);
        $enrollmentId = (string) Str::uuid();

        try {
            $service->confirmAdditionalDevicePassword($admin, $enrollmentId, 'salah');
        } catch (ValidationException) {
        }

        $this->assertSame(1, $service->passwordAttempts($admin, $enrollmentId));
        $service->confirmAdditionalDevicePassword($admin, $enrollmentId, 'password');
        $this->assertSame(0, $service->passwordAttempts($admin, $enrollmentId));
    }

    public function test_deleting_device_requires_password_even_when_service_is_called_directly(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $service = app(AdminTotpDeviceService::class);
        $first = $this->createDevice($admin, 'Ponsel utama', 'JBSWY3DPEHPK3PXP');
        $this->createDevice($admin, 'Laptop kerja', 'KRSXG5DSNFXGOIDB');

        try {
            $service->delete($admin, $first->getKey(), 'salah');
            $this->fail('Perangkat dapat dihapus dengan kata sandi salah.');
        } catch (ValidationException $exception) {
            $this->assertSame('Kata sandi admin tidak valid.', $exception->errors()['password'][0]);
        }

        $this->assertDatabaseHas('admin_totp_devices', ['id' => $first->getKey()]);
    }

    public function test_last_device_cannot_be_deleted_but_any_non_last_device_can(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $service = app(AdminTotpDeviceService::class);
        $first = $this->createDevice($admin, 'Ponsel utama', 'JBSWY3DPEHPK3PXP');

        try {
            $service->delete($admin, $first->getKey(), 'password');
            $this->fail('Perangkat terakhir dapat dihapus.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                'Perangkat ini tidak dapat dihapus. Minimal satu perangkat autentikator harus tetap aktif.',
                $exception->errors()['device'][0],
            );
        }

        $second = $this->createDevice($admin, 'Laptop kerja', 'KRSXG5DSNFXGOIDB');
        $service->delete($admin, $first->getKey(), 'password');

        $this->assertDatabaseMissing('admin_totp_devices', ['id' => $first->getKey()]);
        $this->assertDatabaseHas('admin_totp_devices', ['id' => $second->getKey()]);
        $this->assertSame(1, $admin->totpDevices()->count());
    }

    public function test_device_secret_is_encrypted_and_hidden(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $device = $this->createDevice($admin, 'Ponsel utama', 'JBSWY3DPEHPK3PXP');
        $rawSecret = DB::table('admin_totp_devices')->where('id', $device->getKey())->value('secret');

        $this->assertNotSame('JBSWY3DPEHPK3PXP', $rawSecret);
        $this->assertSame('JBSWY3DPEHPK3PXP', $device->fresh()->secret);
        $this->assertArrayNotHasKey('secret', $device->toArray());
        $this->assertTrue($device->isGuarded('secret'));
    }

    public function test_legacy_migration_moves_only_admin_secret_into_encrypted_named_device(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'user']);
        $admin->saveAppAuthenticationSecret('JBSWY3DPEHPK3PXP');
        $customer->saveAppAuthenticationSecret('KRSXG5DSNFXGOIDB');
        Schema::drop('admin_totp_devices');

        $migration = require database_path('migrations/2026_07_23_020500_create_admin_totp_devices_table.php');
        $migration->up();

        $this->assertDatabaseHas('admin_totp_devices', [
            'user_id' => $admin->getKey(),
            'slot' => 1,
            'name' => 'Perangkat utama',
        ]);
        $this->assertDatabaseMissing('admin_totp_devices', ['user_id' => $customer->getKey()]);
        $device = AdminTotpDevice::query()->whereBelongsTo($admin)->sole();
        $rawSecret = DB::table('admin_totp_devices')->where('id', $device->getKey())->value('secret');
        $this->assertNotSame('JBSWY3DPEHPK3PXP', $rawSecret);
        $this->assertSame('JBSWY3DPEHPK3PXP', $device->secret);
    }

    public function test_correction_migration_repairs_already_serialized_device_secret(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $secret = 'JBSWY3DPEHPK3PXP';
        DB::table('admin_totp_devices')->insert([
            'user_id' => $admin->getKey(),
            'slot' => 1,
            'name' => 'Perangkat utama',
            'name_key' => hash('sha256', 'perangkat utama'),
            'secret' => encrypt($secret),
            'secret_fingerprint' => hash('sha256', $secret),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $migration = require database_path('migrations/2026_07_23_024500_fix_serialized_admin_totp_device_secrets.php');
        $migration->up();

        $device = AdminTotpDevice::query()->whereBelongsTo($admin)->sole();
        $this->assertSame($secret, $device->secret);
    }

    public function test_admin_cannot_manage_another_admin_device_through_service(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $otherAdmin = User::factory()->create(['role' => 'admin']);
        $device = $this->createDevice($otherAdmin, 'Ponsel utama', 'JBSWY3DPEHPK3PXP');
        $this->actingAs($admin);

        try {
            app(AdminTotpDeviceService::class)->delete($otherAdmin, $device->getKey(), 'password');
            $this->fail('Admin dapat mengelola perangkat admin lain.');
        } catch (AuthorizationException) {
            $this->assertDatabaseHas('admin_totp_devices', ['id' => $device->getKey()]);
        }
    }

    public function test_enrolled_admin_mfa_secret_and_recovery_codes_are_not_mass_assignable_or_serialized(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->assertFalse($user->isFillable('app_authentication_secret'));
        $this->assertFalse($user->isFillable('app_authentication_recovery_codes'));
        $this->assertContains('app_authentication_secret', $user->getHidden());
        $this->assertContains('app_authentication_recovery_codes', $user->getHidden());
    }

    private function createDevice(User $user, string $name, string $secret): AdminTotpDevice
    {
        $device = new AdminTotpDevice;
        $device->user()->associate($user);
        $device->slot = ($user->totpDevices()->max('slot') ?? 0) + 1;
        $device->name = $name;
        $device->name_key = hash('sha256', Str::lower($name));
        $device->secret = $secret;
        $device->secret_fingerprint = hash('sha256', $secret);
        $device->save();

        return $device;
    }
}
