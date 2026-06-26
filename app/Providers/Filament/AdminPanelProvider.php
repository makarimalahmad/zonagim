<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    // ❌ HAPUS method canAccessPanel() dari sini

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Auth\Login::class)
            ->authGuard('admin')
            ->brandName('LapakGimID')
            ->font('Outfit')

            ->colors([
                'primary' => Color::Yellow,
            ])
            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'App\Filament\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Pages'),
                for: 'App\Filament\Pages'
            )
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(
                in: app_path('Filament/Widgets'),
                for: 'App\Filament\Widgets'
            )
            ->widgets([
                AccountWidget::class,
                // FilamentInfoWidget::class, // Removed for production
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
            ])
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn(): string => '
                    <style>
                        /* ==============================
                           GLOBAL TYPOGRAPHY & INTERFACE
                           ============================== */
                        body {
                            -webkit-font-smoothing: antialiased;
                            -moz-osx-font-smoothing: grayscale;
                            --font-sans: "Outfit", sans-serif;
                        }
                        
                        /* HEADER - Transparent & Clean */
                        .fi-header {
                            background: transparent !important;
                            border: none !important;
                            box-shadow: none !important;
                            padding-bottom: 1.5rem;
                        }

                        /* LOGO Styling - Keep Brand Gold */
                        .fi-logo {
                            color: #eab308 !important;
                            font-size: 1.5rem;
                            font-weight: 800;
                            letter-spacing: -0.01em;
                        }

                        /* BUTTONS - Elegant Gold (Consistent) */
                        .fi-btn-primary {
                            background: #eab308 !important;
                            border: none !important;
                            color: #000 !important;
                            font-weight: 600 !important;
                            text-transform: uppercase;
                            letter-spacing: 0.05em;
                            padding: 0.5rem 1.25rem !important;
                            border-radius: 0.75rem !important;
                            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                        }
                        .fi-btn-primary:hover {
                            background: #ca8a04 !important;
                            transform: translateY(-2px);
                            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                        }

                        /* CARDS & CONTAINERS - Restored Backgrounds */
                        .fi-section, 
                        .fi-wi-stats-overview-stat,
                        .fi-wi-chart {
                            border-radius: 1rem !important;
                            transition: transform 0.2s ease, box-shadow 0.2s ease;
                        }
                        
                        .fi-wi-stats-overview-stat:hover {
                            transform: translateY(-2px);
                        }

                        /* ==============================
                           DARK MODE STYLES (Elegant & Deep)
                           ============================== */
                        :is(.dark) body {
                            background: #0b1221 !important; /* Zinc-950 */
                        }

                        :is(.dark) .fi-topbar,
                        :is(.dark) .fi-sidebar {
                            background-color: #0b1221 !important;
                            border-color: #1b2740 !important;
                        }

                        /* Cards in Dark Mode */
                        :is(.dark) .fi-section, 
                        :is(.dark) .fi-wi-stats-overview-stat,
                        :is(.dark) .fi-wi-chart,
                        :is(.dark) .fi-ta-ctn {
                            background-color: #0e1629 !important; /* Zinc-900 */
                            border: 1px solid #1b2740 !important;
                            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                        }

                        :is(.dark) .fi-main-sidebar, 
                        :is(.dark) .fi-sidebar-header {
                            background-color: #0b1221 !important;
                            border-right: 1px solid #1b2740 !important;
                        }

                        :is(.dark) input:not([type="checkbox"]):not([type="radio"]), 
                        :is(.dark) .fi-input, 
                        :is(.dark) .fi-input-wrp,
                        :is(.dark) .fi-select-input {
                            background-color: #0b1221 !important;
                            border-color: #1b2740 !important;
                            color: white !important;
                        }

                        :is(.dark) input:not([type="checkbox"]):not([type="radio"]):focus, 
                        :is(.dark) .fi-input-wrp:focus-within {
                            border-color: #eab308 !important;
                            ring: 1px solid #eab308 !important;
                            background-color: #0b1221 !important;
                        }

                        /* ==============================
                           GARIS PEMISAH TABEL & DIVIDER
                           (default divider Filament terlalu samar di tema navy,
                           jadi garis baris tabel "hilang" - ini memunculkannya lagi)
                           ============================== */
                        :is(.dark) table tbody tr,
                        :is(.dark) table thead tr,
                        :is(.dark) table thead th,
                        :is(.dark) .fi-ta-header-cell,
                        :is(.dark) .fi-ta-cell,
                        :is(.dark) [class*="divide-y"] > * {
                            border-color: #25324d !important;
                        }

                        :is(html:not(.dark)) table tbody tr,
                        :is(html:not(.dark)) table thead tr,
                        :is(html:not(.dark)) table thead th,
                        :is(html:not(.dark)) .fi-ta-header-cell,
                        :is(html:not(.dark)) .fi-ta-cell,
                        :is(html:not(.dark)) [class*="divide-y"] > * {
                            border-color: #e2e8f0 !important;
                        }

                        /* ==============================
                           LIGHT MODE STYLES (Clean & White)
                           ============================== */
                        :is(html:not(.dark)) body {
                            background: #f4f6fa !important; /* Zinc-100 */
                        }

                        :is(html:not(.dark)) .fi-topbar,
                        :is(html:not(.dark)) .fi-sidebar {
                            background-color: #ffffff !important;
                        }

                        /* Cards in Light Mode */
                        :is(html:not(.dark)) .fi-section, 
                        :is(html:not(.dark)) .fi-wi-stats-overview-stat,
                        :is(html:not(.dark)) .fi-wi-chart,
                        :is(html:not(.dark)) .fi-ta-ctn {
                            background-color: #ffffff !important;
                            border: 1px solid #e2e8f0 !important; /* Zinc-200 */
                            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
                        }

                        :is(html:not(.dark)) .fi-main-sidebar {
                            background-color: #ffffff !important;
                            border-right: 1px solid #f4f6fa !important;
                        }

                        :is(html:not(.dark)) input:not([type="checkbox"]):not([type="radio"]), 
                        :is(html:not(.dark)) .fi-input, 
                        :is(html:not(.dark)) .fi-input-wrp,
                        :is(html:not(.dark)) .fi-select-input {
                            background-color: #ffffff !important;
                            border-color: #e2e8f0 !important;
                            color: #0b1221 !important;
                        }

                        :is(html:not(.dark)) input:not([type="checkbox"]):not([type="radio"]):focus, 
                        :is(html:not(.dark)) .fi-input-wrp:focus-within {
                            border-color: #eab308 !important;
                            box-shadow: 0 0 0 1px #eab308 !important;
                            background-color: #ffffff !important;
                        }
                    </style>
                ',
            );
    }
}
