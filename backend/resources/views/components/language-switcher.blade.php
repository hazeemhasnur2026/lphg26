@props(['variant' => 'desktop'])

@php
    use Mcamara\LaravelLocalization\Facades\LaravelLocalization as L10n;

    $locales = L10n::getSupportedLocales();
    $current = app()->getLocale();
@endphp

@if ($variant === 'mobile')
  {{-- Di layar kecil semua pilihan ditampilkan berjajar: menu bertingkat di
       dalam menu bertingkat menyulitkan, dan pilihannya cuma tiga. --}}
  <div class="nav-row mt-3 mb-3 flex gap-2 px-1" style="animation-delay:300ms">
    @foreach ($locales as $code => $properties)
      <a href="{{ L10n::getLocalizedURL($code) }}" hreflang="{{ $code }}"
         @if ($code === $current) aria-current="true" @endif
         class="flex-1 rounded-md border px-3 py-2 text-center text-sm font-semibold transition
                {{ $code === $current ? 'border-primary bg-primary text-white' : 'border-slate-200 text-slate-600' }}">
        {{ strtoupper($code) }}
      </a>
    @endforeach
  </div>
@else
  <div class="relative ml-1" x-data="{ open: false }" x-on:click.outside="open = false">
    {{-- Ikon saja: navbar sudah padat, dan globe dikenali lintas bahasa, persis
         gunanya di tempat orang belum tentu bisa membaca bahasa halaman yang
         sedang dibuka. Bahasa aktif ditandai di dalam menu, bukan dengan
         menempelkan kodenya di bilah navigasi. --}}
    <button type="button" x-on:click="open = !open"
            class="flex h-10 w-10 items-center justify-center rounded-md text-slate-600 transition hover:bg-slate-50 hover:text-primary"
            title="{{ __('nav.language') }}" aria-haspopup="true" :aria-expanded="open.toString()">
      <span class="sr-only">{{ __('nav.language') }}</span>
      <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18zm0 0c2.5-2.5 3.5-5.8 3.5-9S14.5 5.5 12 3m0 18c-2.5-2.5-3.5-5.8-3.5-9S9.5 5.5 12 3M3.5 9h17m-17 6h17"/>
      </svg>
    </button>

    <div x-show="open" x-cloak x-transition
         class="absolute right-0 z-50 mt-1 w-44 overflow-hidden rounded-xl border border-slate-100 bg-white py-1 shadow-lg">
      @foreach ($locales as $code => $properties)
        {{-- Nama bahasanya ditulis dalam bahasa itu sendiri: orang yang mencari
             bahasanya belum tentu bisa membaca bahasa halaman saat ini. --}}
        <a href="{{ L10n::getLocalizedURL($code) }}" hreflang="{{ $code }}"
           @if ($code === $current) aria-current="true" @endif
           class="flex items-center justify-between gap-2 px-4 py-2 text-sm transition hover:bg-slate-50 hover:text-primary
                  {{ $code === $current ? 'font-bold text-primary' : 'text-slate-600' }}">
          {{ $properties['native'] }}
          @if ($code === $current)
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
            </svg>
          @endif
        </a>
      @endforeach
    </div>
  </div>
@endif
