<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ProfileUiSourceTest extends TestCase
{
    private string $profileRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->profileRoot = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'Pages'.DIRECTORY_SEPARATOR.'Profile';
    }

    public function test_profile_modal_uses_body_portal_above_page_content(): void
    {
        $source = file_get_contents(
            dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'Components'.DIRECTORY_SEPARATOR.'Modal.jsx',
        );

        $this->assertStringContainsString('createPortal', $source);
        $this->assertStringContainsString('document.body', $source);
        $this->assertStringContainsString('z-[100]', $source);
        $this->assertStringContainsString('relative z-10', $source);
        $this->assertStringNotContainsString('shadow-xl', $source);
    }

    public function test_profile_date_formatter_handles_missing_or_invalid_date(): void
    {
        $source = file_get_contents($this->profileRoot.DIRECTORY_SEPARATOR.'Partials'.DIRECTORY_SEPARATOR.'UpdateProfileInformationForm.jsx');

        $this->assertStringContainsString('if (!dateString)', $source);
        $this->assertStringContainsString('Number.isNaN(date.getTime())', $source);
        $this->assertStringContainsString('new Intl.DateTimeFormat("id-ID"', $source);
        $this->assertStringContainsString('errors["address.zip"]', $source);
    }

    public function test_profile_uses_single_header_and_indonesian_interface(): void
    {
        $edit = file_get_contents($this->profileRoot.DIRECTORY_SEPARATOR.'Edit.jsx');
        $profile = file_get_contents($this->profileRoot.DIRECTORY_SEPARATOR.'Partials'.DIRECTORY_SEPARATOR.'UpdateProfileInformationForm.jsx');
        $password = file_get_contents($this->profileRoot.DIRECTORY_SEPARATOR.'Partials'.DIRECTORY_SEPARATOR.'UpdatePasswordForm.jsx');
        $delete = file_get_contents($this->profileRoot.DIRECTORY_SEPARATOR.'Partials'.DIRECTORY_SEPARATOR.'DeleteUserForm.jsx');
        $source = $edit.$profile.$password.$delete;

        $this->assertStringContainsString('<AuthenticatedLayout>', $edit);
        $this->assertStringNotContainsString('header={', $edit);
        $this->assertStringContainsString('Kembali ke Market', $edit);
        $this->assertStringContainsString('Pengaturan Akun', $edit);
        $this->assertStringContainsString('Keamanan Akun', $edit);
        $this->assertStringContainsString('Informasi Profil', $profile);
        $this->assertStringContainsString('Informasi profil gagal diperbarui.', $profile);
        $this->assertStringContainsString('Swal.fire', $profile);
        $this->assertStringContainsString('Perbarui Kata Sandi', $password);
        $this->assertStringContainsString('Kata sandi saat ini wajib diisi.', $password);
        $this->assertStringContainsString('Kata sandi baru wajib diisi.', $password);
        $this->assertStringContainsString('Konfirmasi kata sandi wajib diisi.', $password);
        $this->assertStringContainsString('text-slate-950', $password);
        $this->assertStringContainsString('Lengkapi semua kolom kata sandi.', $password);
        $this->assertStringContainsString('formErrors.current_password', $password);
        $this->assertStringContainsString('Kata sandi gagal diperbarui.', $password);
        $this->assertStringContainsString('reset(', $password);
        $this->assertStringContainsString('requestAnimationFrame', $password);
        $this->assertStringContainsString('currentPasswordInput.current?.focus()', $password);
        $this->assertStringContainsString('Swal.fire', $password);
        $this->assertStringContainsString('Hapus Akun', $delete);

        foreach ([
            'Settings',
            'Back to Market',
            'Edit Profile',
            'Account Security',
            'Appearance',
            'Notifications',
            'Profile Information',
            'Update Password',
            'Current Password',
            'New Password',
            'Confirm Password',
            'Delete Account',
            'Cancel',
            'Soon',
        ] as $legacyText) {
            $this->assertStringNotContainsString($legacyText, $source);
        }
    }
}
