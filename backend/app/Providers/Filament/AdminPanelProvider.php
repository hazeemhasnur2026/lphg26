<?php

namespace App\Providers\Filament;

use App\Filament\Analitik\GrafikPeristiwaWidget;
use App\Filament\Analitik\PeringkatWidget;
use App\Filament\Analitik\RingkasanPeristiwaWidget;
use App\Filament\Widgets\AktivitasTerakhirWidget;
use App\Filament\Widgets\RingkasanKontenWidget;
use Filament\Auth\MultiFactor\App\AppAuthentication;
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
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Outerweb\FilamentTranslatableFields\TranslatableFieldsPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            // brandName tetap diisi walau tidak lagi tampil sebagai teks: ia
            // dipakai sebagai alt logo, judul tab peramban, dan nama pada
            // halaman login. Mengosongkannya menghapus ketiganya sekaligus.
            ->brandName('Hasnur Group')
            ->brandLogo(fn () => asset('assets/img/logo.png'))
            ->brandLogoHeight('2.25rem')
            // Warna utama situs, supaya panel tidak terasa seperti aplikasi
            // lain. Scaffold Filament menaruh ->colors() kedua di bawah ->login()
            // yang menimpa ini; panggilan itu dihapus, bukan ditumpuk.
            ->colors([
                'primary' => Color::hex('#0F2747'),
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

            /*
             * Autentikasi dua faktor lewat aplikasi autentikator (TOTP).
             *
             * Akun di panel ini dapat menerbitkan di situs korporat dan membaca
             * data pribadi pengunjung. Kata sandi saja tipis untuk keduanya,
             * dan pembatas percobaan login hanya memperlambat penebakan — ia
             * tidak menolong sama sekali bila kata sandinya bocor dari tempat
             * lain, yang merupakan cara paling umum akun diambil alih.
             *
             * Belum diwajibkan. Mewajibkannya sebelum ada satu pun orang yang
             * mendaftarkan perangkatnya akan mengunci seluruh redaksi di luar
             * panelnya sendiri pada deploy berikutnya. Setelah semua akun
             * mendaftar, tambahkan ->requiresMultiFactorAuthentication().
             */
            ->multiFactorAuthentication([
                AppAuthentication::make()
                    ->recoverable()
                    // Kode pemulihan sekali pakai. Tanpa ini, kehilangan ponsel
                    // berarti kehilangan akun, dan jalan keluar satu-satunya
                    // menjadi mematikan MFA lewat basis data — yang dalam
                    // prakteknya berarti mematikannya untuk semua orang.
                    ->regenerableRecoveryCodes(),
            ])
            ->profile()
            ->plugins([
                TranslatableFieldsPlugin::make()
                    ->supportedLocales(['id' => 'Indonesia', 'en' => 'English']),
            ])
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
            /*
             * Widget halaman Analitik. Terdaftar di Livewire, tapi sengaja tidak
             * masuk ->widgets() di atas: Dashboard menampilkan seluruh widget
             * yang terdaftar sebagai widget panel, dan lima tabel peringkat
             * analitik tidak punya urusan di halaman "apa yang perlu saya
             * kerjakan hari ini".
             *
             * Tanpa pendaftaran ini, halamannya rusak dengan cara yang tidak
             * terlihat pada pemuatan pertama. Livewire bisa menurunkan nama
             * komponen dari nama kelas saat merender, jadi HTML-nya keluar utuh;
             * tapi permintaan berikutnya mengirim nama itu kembali dan Livewire
             * harus memetakannya ke kelas — dan pemetaan balik hanya bekerja
             * untuk kelas yang terdaftar. Yang tidak terdaftar menghasilkan
             * ComponentNotFoundException, yang muncul di peramban sebagai
             * "419 Page Expired", bukan sebagai galat yang menyebut widget.
             *
             * Sebelumnya ketiganya diletakkan di luar app/Filament/Widgets
             * supaya tidak ikut ditemukan discoverWidgets() dan tidak muncul di
             * Dashboard. Itu benar, tapi setengah jalan: yang dihindari hanya
             * daftar widget panelnya, dan pendaftaran Livewire-nya ikut hilang.
             */
            ->livewireComponents([
                RingkasanPeristiwaWidget::class,
                GrafikPeristiwaWidget::class,
                PeringkatWidget::class,
            ])
            // Footer panel: pemilik sistem dan dua tautan yang paling sering
            // dicari orang yang sedang memakai panel ini.
            ->renderHook(
                PanelsRenderHook::FOOTER,
                fn (): View => view('filament.footer'),
            )

            // Tema sidebar. Disuntikkan sebagai <style> di <head>, bukan
            // sebagai tema Filament kustom, karena tema kustom menuntut
            // pipeline build tersendiri untuk sekitar enam puluh baris CSS.
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): View => view('filament.tema-sidebar'),
            )
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
