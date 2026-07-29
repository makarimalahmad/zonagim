<?php

namespace App\Filament\Pages;

use App\Services\AdminTotpDeviceService;
use Filament\Auth\MultiFactor\Contracts\MultiFactorAuthenticationProvider;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class MfaSecurity extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'keamanan-akun';

    protected string $view = 'filament.admin.pages.mfa-security';

    protected Width|string|null $maxContentWidth = Width::FiveExtraLarge;

    public function getTitle(): string|Htmlable
    {
        return 'Keamanan Akun';
    }

    public function getHeading(): string|Htmlable
    {
        return 'Keamanan Akun';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Kelola aplikasi autentikator dan kode pemulihan akun admin.';
    }

    public function content(Schema $schema): Schema
    {
        $user = Filament::auth()->user();
        $providers = collect(Filament::getMultiFactorAuthenticationProviders());
        $isEnabled = $providers->contains(
            fn (MultiFactorAuthenticationProvider $provider): bool => $provider->isEnabled($user),
        );

        return $schema->components([
            Section::make($isEnabled ? 'MFA Aktif' : 'MFA Belum Aktif')
                ->description($isEnabled
                    ? $user->totpDevices()->count().' dari '.AdminTotpDeviceService::MAX_DEVICES.' perangkat terdaftar'
                    : 'Aktifkan aplikasi autentikator untuk melindungi akses panel admin.')
                ->icon($isEnabled ? Heroicon::OutlinedShieldCheck : Heroicon::OutlinedShieldExclamation)
                ->iconColor($isEnabled ? 'success' : 'danger')
                ->iconSize(IconSize::TwoExtraLarge)
                ->extraAttributes(['class' => 'admin-account-security-card'])
                ->schema($providers
                    ->sort(fn (MultiFactorAuthenticationProvider $provider): int => $provider->isEnabled($user) ? 0 : 1)
                    ->map(fn (MultiFactorAuthenticationProvider $provider): Group => Group::make(
                        $provider->getManagementSchemaComponents(),
                    )->statePath($provider->getId()))
                    ->all()),
        ]);
    }
}
