<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\AktivitasTerakhirWidget;
use App\Filament\Widgets\RingkasanKontenWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
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
            ->brandName('Hasnur Group')
            // Warna utama situs, supaya panel tidak terasa seperti aplikasi
            // lain. Scaffold Filament menaruh ->colors() kedua di bawah ->login()
            // yang menimpa ini; panggilan itu dihapus, bukan ditumpuk.
            ->colors([
                'primary' => Color::hex('#2F3BA2'),
            ])
            // Mode gelap dan terang sudah menyala bawaan di Filament v4;
            // ditulis di sini supaya terlihat sebagai keputusan, bukan kebetulan.
            ->darkMode()
            ->themeSwitcher()
            // Lonceng pemberitahuan. Alur Draf → Tinjau → Setujui hanya berjalan
            // bila orang tahu gilirannya tiba; sebelum ini satu-satunya petunjuk
            // adalah angka di menu, yang cuma terlihat oleh yang memang membuka
            // panel hari itu.
            ->databaseNotifications()
            ->databaseNotificationsPolling('60s')
            ->login()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            // FilamentInfoWidget dilepas: isinya tautan ke dokumentasi Filament
            // dan nomor versinya. Berguna bagi yang membangun panel, tidak bagi
            // yang memakainya tiap hari, dan ia menempati ruang paling atas.
            ->widgets([
                AccountWidget::class,
                RingkasanKontenWidget::class,
                AktivitasTerakhirWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
