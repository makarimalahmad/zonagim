<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    protected function getRememberFormComponent(): Component
    {
        return parent::getRememberFormComponent()->hidden();
    }

    public function getTitle(): string|Htmlable
    {
        return 'Login Admin';
    }

    public function getHeading(): string|Htmlable
    {
        return filled($this->userUndertakingMultiFactorAuthentication)
            ? 'Verifikasi tambahan'
            : 'Masuk';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return filled($this->userUndertakingMultiFactorAuthentication)
            ? 'Masukkan kode dari aplikasi autentikator untuk menyelesaikan proses masuk.'
            : 'Selamat datang kembali. Silakan masuk untuk melanjutkan.';
    }

    public function getMultiFactorChallengeFormContentComponent(): Component
    {
        return parent::getMultiFactorChallengeFormContentComponent()
            ->extraAttributes(['class' => 'admin-mfa-challenge']);
    }
}
