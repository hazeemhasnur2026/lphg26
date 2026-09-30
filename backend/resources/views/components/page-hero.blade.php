{{--
  The banner every subpage shares. This is the Blade form of what
  tools/build_page_hero.py generated for the static prototype. Same props,
  same markup. With Blade the template genuinely exists once instead of being
  written into twenty files.

  @props
    breadcrumb  [['label' => string, 'url' => ?string]]  last entry renders active
    title       string
    description ?string   omitted entirely when absent. Never an empty <p>
    tags        [['icon' => string, 'label' => string]]  omitted when empty
    image       ['src' => string, 'alt' => string]
    badge       ?['icon' => string, 'title' => string, 'subtitle' => string]
    tabs        [['label' => string, 'url' => string, 'active' => bool]]
--}}
@props([
    'breadcrumb' => [],
    'title' => '',
    'description' => null,
    'tags' => [],
    'image' => null,
    'badge' => null,
    'tabs' => [],
])

<section class="relative overflow-hidden bg-gradient-to-br from-logo-blue via-logo-blue to-logo-blue-dark pt-[72px] text-white">
  <x-brand-strip />
  <div class="absolute inset-0 opacity-10 hero-pattern"></div>
  <div class="pointer-events-none absolute -right-16 -top-24 h-72 w-72 rounded-full border border-white/10"></div>
  <div class="pointer-events-none absolute -right-44 -top-6 h-96 w-96 rounded-full border border-white/[0.07]"></div>

  <div class="relative mx-auto grid max-w-7xl items-stretch gap-10 px-4 py-8 sm:px-6 lg:grid-cols-2 lg:px-8 lg:py-14">
    <div class="reveal flex flex-col justify-center">
      @if ($breadcrumb)
        <nav aria-label="Breadcrumb" class="mb-3 text-xs">
          <ol class="flex flex-wrap items-center gap-x-1.5">
            @foreach ($breadcrumb as $i => $crumb)
              @if ($loop->last)
                <li><span aria-current="page" class="font-semibold text-logo-yellow">{{ $crumb['label'] }}</span></li>
              @else
                <li><a href="{{ $crumb['url'] }}" class="text-white/65 transition hover:text-white">{{ $crumb['label'] }}</a></li>
                <li aria-hidden="true" class="text-white/35">/</li>
              @endif
            @endforeach
          </ol>
        </nav>
      @endif

      <h1 class="text-4xl font-extrabold leading-tight md:text-5xl">{{ $title }}</h1>

      @if ($description)
        <p class="mt-4 max-w-[460px] leading-relaxed text-white/80">{{ $description }}</p>
      @endif

      @if ($tags)
        <ul class="mt-6 flex flex-wrap gap-2">
          @foreach ($tags as $tag)
            <li class="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-white/10 px-3 py-1 text-xs font-semibold">
              <span aria-hidden="true">{!! $tag['icon'] !!}</span>{{ $tag['label'] }}
            </li>
          @endforeach
        </ul>
      @endif

      @if ($tabs)
        <nav class="mt-6 flex flex-wrap gap-2" aria-label="Sub-halaman">
          @foreach ($tabs as $tab)
            <a href="{{ $tab['url'] }}"
               class="rounded-full px-4 py-1.5 text-sm font-semibold transition {{ ($tab['active'] ?? false) ? 'bg-white text-logo-blue shadow-sm' : 'border border-white/25 text-white/80 hover:bg-white/10' }}">
              {{ $tab['label'] }}
            </a>
          @endforeach
        </nav>
      @endif
    </div>

    @if ($image)
      <div class="reveal relative">
        <x-img src="{{ $image['src'] }}" alt="{{ $image['alt'] ?? '' }}" loading="eager"
             class="h-full w-full rounded-2xl object-cover shadow-2xl" sizes="(min-width: 1024px) 600px, 100vw" />
        @if ($badge)
          <div class="absolute -bottom-6 left-6 flex items-center gap-3 rounded-xl bg-white px-4 py-3 shadow-xl">
            <span class="text-xl" aria-hidden="true">{!! $badge['icon'] !!}</span>
            <span class="leading-tight">
              <span class="block text-sm font-bold text-slate-900">{{ $badge['title'] }}</span>
              <span class="block text-xs text-slate-500">{{ $badge['subtitle'] }}</span>
            </span>
          </div>
        @endif
      </div>
    @endif
  </div>
</section>
