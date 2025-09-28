<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\KehadiranPihakWidget;
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
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->renderHook(
                'panels::head.end',
                fn(): string => '
                <meta name="theme-color" content="#1f2937"/>
                <link rel="apple-touch-icon" href="' . asset('logo/logo192.png') . '">
                <link rel="manifest" href="' . asset('/admin-manifest.json') . '">
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
            '
            )
            ->renderHook(
                'panels::body.end',
                fn(): string => '
                <button id="pwa-install-btn" style="
                    display: none;
                    position: fixed;
                    top: 80px;
                    right: 20px;
                    width: 50px;
                    height: 50px;
                    background: linear-gradient(135deg, #1f2937, #374151);
                    color: white;
                    border: none;
                    border-radius: 50%;
                    box-shadow: 0 4px 15px rgba(31, 41, 55, 0.3);
                    cursor: pointer;
                    z-index: 9999;
                    font-size: 18px;
                ">
                    <i class="bi bi-download"></i>
                </button>
                <script src="' . asset('/sw.js') . '"></script>
                <script src="' . asset('pwa-install.js') . '"></script>
                <script>
                    if ("serviceWorker" in navigator) {
                        navigator.serviceWorker.register("/sw.js");
                    }
                </script>
            '
            )
            ->resources([
                // \App\Filament\Resources\PerkaraResource::class,
                // \App\Filament\Resources\CheckinPihakResource::class,
                // \App\Filament\Resources\AntrianSidangs\AntrianSidangResource::class, // ← tambahkan ini
            ])
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                \App\Filament\Widgets\AntrianStatsOverview::class,
                KehadiranPihakWidget::class
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
            ->favicon(asset('storage/img/logo_ma.png'));
    }
}
