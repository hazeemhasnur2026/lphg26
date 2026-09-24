<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title') - Hasnur Group</title>
<meta name="description" content="@yield('description')">
<meta name="theme-color" content="#2F3BA2">
<link rel="canonical" href="@yield('canonical', url()->current())">

{{-- Satu halaman kini punya tiga alamat. Tanpa hreflang, Google memperlakukan
     ketiganya sebagai halaman terpisah yang saling bersaing, bukan sebagai
     versi bahasa dari halaman yang sama.

     Argumen keempat getLocalizedURL dibiarkan false: kalau dipaksa true,
     versi Indonesia ditulis sebagai /id/... padahal URL sebenarnya tanpa
     prefiks. Hreflang jadi menunjuk alamat yang justru dialihkan.

     Kode bahasanya memakai tanda hubung, bukan garis bawah seperti di config:
     id_ID itu bentuk locale PHP, sedangkan hreflang memakai id-ID. --}}
@foreach (\Mcamara\LaravelLocalization\Facades\LaravelLocalization::getSupportedLocales() as $code => $properties)
  <link rel="alternate" hreflang="{{ str_replace('_', '-', $properties['regional'] ?? $code) }}" href="{{ \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getLocalizedURL($code) }}">
@endforeach
{{-- x-default menunjuk versi Indonesia: itu bahasa asli situs ini. --}}
<link rel="alternate" hreflang="x-default" href="{{ \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getLocalizedURL('id') }}">

<meta property="og:type" content="@yield('og_type', 'website')">
<meta property="og:site_name" content="Hasnur Group">
<meta property="og:locale" content="id_ID">
<meta property="og:url" content="@yield('canonical', url()->current())">
<meta property="og:title" content="@yield('title')">
<meta property="og:description" content="@yield('description')">
<meta property="og:image" content="@yield('og_image', asset('assets/img/logo-60th.png'))">
<meta name="twitter:card" content="summary_large_image">

<link rel="icon" type="image/png" sizes="32x32" href="/assets/img/ico/favicon-32x32.png">
<link rel="shortcut icon" href="/assets/img/ico/favicon.ico">

{{-- Tailwind dan Alpine dibangun sendiri, tidak lagi dari CDN. Saat pengujian
     kedua CDN pernah gagal bersamaan dan halaman kehilangan seluruh gaya
     sekaligus semua interaksi. Play CDN juga membangun CSS di browser, yang
     memakan satu frame tanpa gaya dan merugikan LCP. --}}
@vite(['resources/css/app.css', 'resources/js/app.js'])

{{-- Plus Jakarta Sans diunduh saat build dan dilayani dari domain sendiri.
     Sebelumnya dari Google Fonts: permintaan pemblokir render ke pihak ketiga,
     jenis ketergantungan yang sama yang menjatuhkan halaman ini saat CDN gagal. --}}
{{ Vite::fonts() }}
<link rel="stylesheet" href="/assets/css/style.css">
@stack('head')
</head>
<body class="font-sans text-slate-800 bg-white antialiased">

@include('partials.header')

<main>
@yield('content')
</main>

@include('partials.footer')

<script src="/assets/js/main.js"></script>
@stack('scripts')
</body>
</html>
