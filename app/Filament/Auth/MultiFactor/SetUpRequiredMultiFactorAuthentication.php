<?php

namespace App\Filament\Auth\MultiFactor;

use Filament\Auth\MultiFactor\Pages\SetUpRequiredMultiFactorAuthentication as BasePage;
use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class SetUpRequiredMultiFactorAuthentication extends BasePage
{
    public function getTitle(): string|Htmlable
    {
        return 'Siapkan autentikasi dua faktor';
    }

    public function getHeading(): string|Htmlable|null
    {
        return new HtmlString(view('filament.admin.mfa-brand')->render());
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Tingkatkan keamanan akun Anda dengan mendaftarkan aplikasi autentikator. MFA memberikan metode kedua untuk memverifikasi identitas Anda selain kata sandi.';
    }

    public function getMultiFactorAuthenticationContentComponent(): Component
    {
        return parent::getMultiFactorAuthenticationContentComponent()
            ->extraAttributes(['class' => 'admin-mfa-setup-card']);
    }

    public function hasLogo(): bool
    {
        return false;
    }

    public function getExtraBodyAttributes(): array
    {
        return [
            ...parent::getExtraBodyAttributes(),
            'class' => 'admin-mfa-setup-page',
        ];
    }
}
