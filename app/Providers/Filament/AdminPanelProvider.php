<?php

namespace App\Providers\Filament;

use App\Filament\Auth\Login;
use App\Filament\Auth\MultiFactor\AppAuthentication;
use App\Filament\Auth\MultiFactor\SetUpRequiredMultiFactorAuthentication;
use App\Filament\Pages\MfaSecurity;
use App\Http\Middleware\EnsureAdmin;
use App\Services\AdminTotpDeviceService;
use Filament\Actions\Action;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->multiFactorAuthentication(
                [
                    AppAuthentication::make()
                        ->brandName('Zonagim Admin')
                        ->recoverable()
                        ->codeWindow(AdminTotpDeviceService::CODE_WINDOW),
                ],
                SetUpRequiredMultiFactorAuthentication::class,
                isRequired: true,
            )
            ->strictAuthorization()
            ->brandName('Zonagim')
            ->brandLogo(fn (): View => view('filament.admin.brand-logo'))
            ->darkModeBrandLogo(fn (): View => view('filament.admin.brand-logo'))
            ->brandLogoHeight('7rem')
            ->font('Outfit')
            ->colors([
                'primary' => Color::Yellow,
            ])
            ->defaultThemeMode(ThemeMode::System)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->userMenuItems([
                Action::make('mfaSecurity')
                    ->label('Keamanan Akun')
                    ->icon('heroicon-o-shield-check')
                    ->color('gray')
                    ->url(fn (): string => MfaSecurity::getUrl())
                    ->sort(10),
            ])
            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'App\Filament\Resources',
            )
            ->discoverPages(
                in: app_path('Filament/Pages'),
                for: 'App\Filament\Pages',
            )
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(
                in: app_path('Filament/Widgets'),
                for: 'App\Filament\Widgets',
            )
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                EnsureAdmin::class,
            ], isPersistent: true)
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
                fn (): View => view('filament.admin.login-lockout'),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn (): View => view('filament.admin.login-theme-switcher'),
            );
    }
}
