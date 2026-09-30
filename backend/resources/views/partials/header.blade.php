<header class="fixed top-0 inset-x-0 z-50 transition-colors duration-300"
        :class="scrolled ? 'bg-white/95 backdrop-blur border-b border-slate-100' : 'bg-transparent border-b border-transparent'"
        x-data="{ scrolled: false, mobileOpen: false,
                  updateScrolled() { this.scrolled = window.scrollY > 40 || window.innerWidth >= 1280 } }"
        x-init="updateScrolled()"
        x-on:scroll.window="updateScrolled()"
        x-on:resize.window="updateScrolled()">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="relative flex items-center justify-between h-18 py-3">
      <!-- Logo. Hidden at the very top of the page (transparent header over
           the hero), fades in together with the full nav once scrolled. -->
      <a href="/" class="flex items-center shrink-0" x-show="scrolled"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
        <x-img src="/assets/img/logo.png" alt="Hasnur Group" class="h-9 sm:h-10 w-auto drop-shadow" sizes="176px" />
      </a>

      <!-- Desktop Nav. Hidden at the very top of the page, appears once the visitor scrolls -->
      <nav x-show="scrolled" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
           class="hidden xl:flex items-center gap-1 text-sm font-semibold text-slate-700">
        <a href="/" class="px-3 py-2 rounded-md hover:text-primary hover:bg-slate-50 transition">{{ __('nav.home') }}</a>

        <div class="relative" x-data="{ open: false }" x-on:mouseenter="open = true" x-on:mouseleave="open = false">
          <button class="px-3 py-2 rounded-md hover:text-primary hover:bg-slate-50 transition inline-flex items-center gap-1">{{ __('nav.about') }}<svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
          </button>
          <div x-show="open" x-transition x-cloak class="absolute left-0 top-full w-56 bg-white rounded-xl shadow-lg border border-slate-100 py-2">
            <a href="/page/about-us/overview" class="block px-4 py-2 text-sm hover:bg-slate-50 hover:text-primary">{{ __('nav.overview') }}</a>
            <a href="/page/about-us/vision-mission" class="block px-4 py-2 text-sm hover:bg-slate-50 hover:text-primary">{{ __('nav.vision') }}</a>
          </div>
        </div>

        <div class="relative" x-data="{ open: false }" x-on:mouseenter="open = true" x-on:mouseleave="open = false">
          <button class="px-3 py-2 rounded-md hover:text-primary hover:bg-slate-50 transition inline-flex items-center gap-1">{{ __('nav.business') }}<svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
          </button>
          <div x-show="open" x-transition x-cloak class="absolute left-0 top-full w-64 bg-white rounded-xl shadow-lg border border-slate-100 py-2">
            <a href="/page/our-business/logistic" class="block px-4 py-2 text-sm hover:bg-slate-50 hover:text-primary">{{ __('nav.business.logistic') }}</a>
            <a href="/page/our-business/agribusiness-forestry" class="block px-4 py-2 text-sm hover:bg-slate-50 hover:text-primary">{{ __('nav.business.agribusiness') }}</a>
            <a href="/page/our-business/energy" class="block px-4 py-2 text-sm hover:bg-slate-50 hover:text-primary">{{ __('nav.business.energy') }}</a>
            <a href="/page/our-business/technology-services" class="block px-4 py-2 text-sm hover:bg-slate-50 hover:text-primary">{{ __('nav.business.technology') }}</a>
            <a href="/page/our-business/education" class="block px-4 py-2 text-sm hover:bg-slate-50 hover:text-primary">{{ __('nav.business.education') }}</a>
            <a href="/page/our-business/consumer" class="block px-4 py-2 text-sm hover:bg-slate-50 hover:text-primary">{{ __('nav.business.consumer') }}</a>
            <a href="/page/our-business/investment" class="block px-4 py-2 text-sm hover:bg-slate-50 hover:text-primary">{{ __('nav.business.investment') }}</a>
          </div>
        </div>

        <div class="relative" x-data="{ open: false }" x-on:mouseenter="open = true" x-on:mouseleave="open = false">
          <button class="px-3 py-2 rounded-md hover:text-primary hover:bg-slate-50 transition inline-flex items-center gap-1">{{ __('nav.csr') }}<svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
          </button>
          <div x-show="open" x-transition x-cloak class="absolute left-0 top-full w-56 bg-white rounded-xl shadow-lg border border-slate-100 py-2">
            <a href="/page/csr/csr-overview" class="block px-4 py-2 text-sm hover:bg-slate-50 hover:text-primary">{{ __('nav.overview') }}</a>
            <a href="/page/csr/csr-activity" class="block px-4 py-2 text-sm hover:bg-slate-50 hover:text-primary">{{ __('nav.csr.activity') }}</a>
          </div>
        </div>

        <div class="relative" x-data="{ open: false }" x-on:mouseenter="open = true" x-on:mouseleave="open = false">
          <button class="px-3 py-2 rounded-md hover:text-primary hover:bg-slate-50 transition inline-flex items-center gap-1">
            SHE
            <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
          </button>
          <div x-show="open" x-transition x-cloak class="absolute left-0 top-full w-56 bg-white rounded-xl shadow-lg border border-slate-100 py-2">
            <a href="/page/she/overview-1" class="block px-4 py-2 text-sm hover:bg-slate-50 hover:text-primary">{{ __('nav.overview') }}</a>
            <a href="/page/she/she-activity" class="block px-4 py-2 text-sm hover:bg-slate-50 hover:text-primary">{{ __('nav.she.activity') }}</a>
          </div>
        </div>

        <div class="relative" x-data="{ open: false }" x-on:mouseenter="open = true" x-on:mouseleave="open = false">
          <button class="px-3 py-2 rounded-md hover:text-primary hover:bg-slate-50 transition inline-flex items-center gap-1">{{ __('nav.career') }}<svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
          </button>
          <div x-show="open" x-transition x-cloak class="absolute left-0 top-full w-48 bg-white rounded-xl shadow-lg border border-slate-100 py-2">
            <a href="/page/career/our-vacancy" class="block px-4 py-2 text-sm hover:bg-slate-50 hover:text-primary">{{ __('nav.career.vacancy') }}</a>
          </div>
        </div>

        <div class="relative" x-data="{ open: false }" x-on:mouseenter="open = true" x-on:mouseleave="open = false">
          <button class="px-3 py-2 rounded-md hover:text-primary hover:bg-slate-50 transition inline-flex items-center gap-1">{{ __('nav.publication') }}<svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
          </button>
          <div x-show="open" x-transition x-cloak class="absolute left-0 top-full w-56 bg-white rounded-xl shadow-lg border border-slate-100 py-2">
            <a href="/page/publication/latest-update" class="block px-4 py-2 text-sm hover:bg-slate-50 hover:text-primary">{{ __('nav.publication.latest') }}</a>
            <a href="/page/publication/news" class="block px-4 py-2 text-sm hover:bg-slate-50 hover:text-primary">{{ __('nav.publication.news') }}</a>
            <a href="/page/publication/press-release" class="block px-4 py-2 text-sm hover:bg-slate-50 hover:text-primary">{{ __('nav.publication.press') }}</a>
            <a href="/page/publication/multimedia" class="block px-4 py-2 text-sm hover:bg-slate-50 hover:text-primary">{{ __('nav.publication.multimedia') }}</a>
            <a href="/page/publication/gallery" class="block px-4 py-2 text-sm hover:bg-slate-50 hover:text-primary">{{ __('nav.publication.gallery') }}</a>
          </div>
        </div>

        {{-- Pencarian diberi ikon, bukan kotak isian yang selalu terbuka:
             bilah navigasi ini sudah penuh di lebar laptop, dan kotak yang
             memakan ruang tetap akan menekan menu lain ke baris kedua. --}}
        <a href="{{ \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getLocalizedURL(app()->getLocale(), url('/cari')) }}"
           class="px-3 py-2 rounded-md hover:text-primary hover:bg-slate-50 transition inline-flex items-center gap-1.5"
           aria-label="{{ __('nav.search') }}">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/>
          </svg>
          <span class="sr-only">{{ __('nav.search') }}</span>
        </a>
        <a href="/page/contact/contact-us" class="ml-2 px-4 py-2 rounded-md bg-primary text-white hover:bg-primary-dark transition">{{ __('nav.contact') }}</a>

        <x-language-switcher />
      </nav>

      <!-- Mobile burger. Always present (no inline nav to fall back to on
           small screens), toggles the dropdown below. ml-auto keeps it
           flush right even pre-scroll, when the logo is hidden and this
           is the only visible flex child (justify-between alone would
           otherwise drop a lone item to the start). Animated as three bars
           that morph into an X (rotate + fade the middle bar) instead of
           swapping two unrelated icons. -->
      <button class="xl:hidden ml-auto flex h-11 w-11 items-center justify-center text-primary transition-opacity duration-200 hover:opacity-70"
              x-on:click="mobileOpen = !mobileOpen"
              :aria-label="mobileOpen ? 'Tutup menu' : 'Buka menu'" :aria-expanded="mobileOpen">
        <!-- All three bars sit centred on top-1/2 (-mt-px offsets half the 2px
             height). The closed state pushes the outer two apart with
             translateY, so opening only has to cancel that translate and
             rotate. Transform and opacity are the only animated properties,
             never top/bottom, which would relayout on every frame. -->
        <span class="relative block h-4 w-6" aria-hidden="true">
          <span class="absolute left-0 top-1/2 -mt-px h-0.5 w-6 rounded-full bg-current transition-transform duration-300 ease-in-out"
                :class="mobileOpen ? 'rotate-45' : '-translate-y-[7px]'"></span>
          <span class="absolute left-0 top-1/2 -mt-px h-0.5 w-6 rounded-full bg-current transition-opacity duration-200 ease-in-out"
                :class="mobileOpen ? 'opacity-0' : 'opacity-100'"></span>
          <span class="absolute left-0 top-1/2 -mt-px h-0.5 w-6 rounded-full bg-current transition-transform duration-300 ease-in-out"
                :class="mobileOpen ? '-rotate-45' : 'translate-y-[7px]'"></span>
        </span>
      </button>
    </div>
  </div>

  <!-- Mobile Nav (also reachable pre-scroll on desktop via the burger above,
       since the inline nav only appears once `scrolled` is true) -->
  <div x-show="mobileOpen" x-cloak
       x-transition:enter="transition ease-out duration-300"
       x-transition:enter-start="opacity-0 -translate-y-3"
       x-transition:enter-end="opacity-100 translate-y-0"
       x-transition:leave="transition ease-in duration-200"
       x-transition:leave-start="opacity-100 translate-y-0"
       x-transition:leave-end="opacity-0 -translate-y-3"
       class="border-t border-slate-100 bg-white max-h-[80vh] overflow-y-auto">
    <nav class="px-4 py-3 flex flex-col text-sm font-semibold text-slate-700">
      <a href="/" class="nav-row py-2.5" style="animation-delay:60ms" x-on:click="mobileOpen=false">{{ __('nav.home') }}</a>

      <details class="nav-row py-2.5" style="animation-delay:90ms">
        <summary class="cursor-pointer">{{ __('nav.about') }}</summary>
        <div class="pl-4 pt-2 flex flex-col gap-2 font-normal">
          <a href="/page/about-us/overview">{{ __('nav.overview') }}</a>
          <a href="/page/about-us/vision-mission">{{ __('nav.vision') }}</a>
        </div>
      </details>

      <details class="nav-row py-2.5" style="animation-delay:120ms">
        <summary class="cursor-pointer">{{ __('nav.business') }}</summary>
        <div class="pl-4 pt-2 flex flex-col gap-2 font-normal">
          <a href="/page/our-business/logistic">{{ __('nav.business.logistic') }}</a>
          <a href="/page/our-business/agribusiness-forestry">{{ __('nav.business.agribusiness') }}</a>
          <a href="/page/our-business/energy">{{ __('nav.business.energy') }}</a>
          <a href="/page/our-business/technology-services">{{ __('nav.business.technology') }}</a>
          <a href="/page/our-business/education">{{ __('nav.business.education') }}</a>
          <a href="/page/our-business/consumer">{{ __('nav.business.consumer') }}</a>
          <a href="/page/our-business/investment">{{ __('nav.business.investment') }}</a>
        </div>
      </details>

      <details class="nav-row py-2.5" style="animation-delay:150ms">
        <summary class="cursor-pointer">{{ __('nav.csr.full') }}</summary>
        <div class="pl-4 pt-2 flex flex-col gap-2 font-normal">
          <a href="/page/csr/csr-overview">{{ __('nav.overview') }}</a>
          <a href="/page/csr/csr-activity">{{ __('nav.csr.activity') }}</a>
        </div>
      </details>

      <details class="nav-row py-2.5" style="animation-delay:180ms">
        <summary class="cursor-pointer">{{ __('nav.she.full') }}</summary>
        <div class="pl-4 pt-2 flex flex-col gap-2 font-normal">
          <a href="/page/she/overview-1">{{ __('nav.overview') }}</a>
          <a href="/page/she/she-activity">{{ __('nav.she.activity') }}</a>
        </div>
      </details>

      <details class="nav-row py-2.5" style="animation-delay:210ms">
        <summary class="cursor-pointer">{{ __('nav.career') }}</summary>
        <div class="pl-4 pt-2 flex flex-col gap-2 font-normal">
          <a href="/page/career/our-vacancy">{{ __('nav.career.vacancy') }}</a>
        </div>
      </details>

      <details class="nav-row py-2.5" style="animation-delay:240ms">
        <summary class="cursor-pointer">{{ __('nav.publication') }}</summary>
        <div class="pl-4 pt-2 flex flex-col gap-2 font-normal">
          <a href="/page/publication/latest-update">{{ __('nav.publication.latest') }}</a>
          <a href="/page/publication/news">{{ __('nav.publication.news') }}</a>
          <a href="/page/publication/press-release">{{ __('nav.publication.press') }}</a>
          <a href="/page/publication/multimedia">{{ __('nav.publication.multimedia') }}</a>
          <a href="/page/publication/gallery">{{ __('nav.publication.gallery') }}</a>
        </div>
      </details>

      <a href="{{ \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getLocalizedURL(app()->getLocale(), url('/cari')) }}" class="nav-row px-4 py-2.5 rounded-md hover:bg-slate-50" style="animation-delay:255ms" x-on:click="mobileOpen=false">{{ __('nav.search') }}</a>
      <a href="/page/contact/contact-us" class="nav-row mt-2 text-center px-4 py-2.5 rounded-md bg-primary text-white" style="animation-delay:270ms" x-on:click="mobileOpen=false">{{ __('nav.contact') }}</a>

      <x-language-switcher variant="mobile" />
    </nav>
  </div>
</header>
