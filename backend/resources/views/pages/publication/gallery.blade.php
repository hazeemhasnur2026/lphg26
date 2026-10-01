@extends('layouts.app')

@section('title', __('nav.publication.gallery'))
@section('description', __('publication.gallery.meta_description'))

@section('content')
<x-page-hero
    :breadcrumb="[
            ['label' => __('nav.home'), 'url' => \App\Support\Localization::url('/')],
            ['label' => __('nav.publication'), 'url' => \App\Support\Localization::url('/page/publication/latest-update')],
            ['label' => __('nav.publication.gallery'), 'url' => null]
        ]"
    :title="__('nav.publication.gallery')"
    :description="__('publication.gallery.hero_description')"
    :image="['src' => '/assets/img/pages/publication-gallery/banner.jpg', 'alt' => __('nav.publication.gallery').', Hasnur Group']"
    :tabs="$publicationTabs" />

<section class="bg-slate-50 border-b border-slate-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-wrap gap-2">
    @foreach ($publicationTabs as $tab)
      <a href="{{ $tab['url'] }}" class="px-5 py-2.5 rounded-full text-sm font-semibold transition {{ $tab['active'] ? 'bg-primary text-white' : 'bg-white text-slate-600 hover:text-primary border border-slate-200' }}">{{ $tab['label'] }}</a>
    @endforeach
  </div>
</section>

<section class="py-16 sm:py-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <p class="reveal text-sm text-slate-500 mb-10">{{ __('publication.gallery.intro') }}</p>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <a href="/assets/img/gallery/01-hasnur-group-she-activity.jpg" target="_blank" rel="noopener" class="reveal group relative aspect-square rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition block">
        <x-img src="/assets/img/gallery/01-hasnur-group-she-activity.jpg" alt="{{ __('publication.gallery.photo_1') }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" loading="lazy" sizes="(min-width: 1024px) 400px, (min-width: 640px) 50vw, 100vw" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/0 to-black/0 flex items-end p-4">
          <p class="text-white text-sm font-semibold leading-snug">{{ __('publication.gallery.photo_1') }}</p>
        </div>
      </a>
      <a href="/assets/img/gallery/02-hasnur-group-csr-activity.jpg" target="_blank" rel="noopener" class="reveal group relative aspect-square rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition block">
        <x-img src="/assets/img/gallery/02-hasnur-group-csr-activity.jpg" alt="{{ __('publication.gallery.photo_2') }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" loading="lazy" sizes="(min-width: 1024px) 400px, (min-width: 640px) 50vw, 100vw" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/0 to-black/0 flex items-end p-4">
          <p class="text-white text-sm font-semibold leading-snug">{{ __('publication.gallery.photo_2') }}</p>
        </div>
      </a>
      <a href="/assets/img/gallery/03-pt-hasnur-internasional-shipping-beri-ba.jpg" target="_blank" rel="noopener" class="reveal group relative aspect-square rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition block">
        <x-img src="/assets/img/gallery/03-pt-hasnur-internasional-shipping-beri-ba.jpg" alt="{{ __('publication.gallery.photo_3') }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" loading="lazy" sizes="(min-width: 1024px) 400px, (min-width: 640px) 50vw, 100vw" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/0 to-black/0 flex items-end p-4">
          <p class="text-white text-sm font-semibold leading-snug">{{ __('publication.gallery.photo_3') }}</p>
        </div>
      </a>
      <a href="/assets/img/gallery/04-hasnur-group-memberikan-bantuan-banjir-k.jpg" target="_blank" rel="noopener" class="reveal group relative aspect-square rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition block">
        <x-img src="/assets/img/gallery/04-hasnur-group-memberikan-bantuan-banjir-k.jpg" alt="{{ __('publication.gallery.photo_4') }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" loading="lazy" sizes="(min-width: 1024px) 400px, (min-width: 640px) 50vw, 100vw" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/0 to-black/0 flex items-end p-4">
          <p class="text-white text-sm font-semibold leading-snug">{{ __('publication.gallery.photo_4') }}</p>
        </div>
      </a>
      <a href="/assets/img/gallery/05-yayasan-hasnur-centre-menyerahkan-bantua.jpg" target="_blank" rel="noopener" class="reveal group relative aspect-square rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition block">
        <x-img src="/assets/img/gallery/05-yayasan-hasnur-centre-menyerahkan-bantua.jpg" alt="{{ __('publication.gallery.photo_5') }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" loading="lazy" sizes="(min-width: 1024px) 400px, (min-width: 640px) 50vw, 100vw" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/0 to-black/0 flex items-end p-4">
          <p class="text-white text-sm font-semibold leading-snug">{{ __('publication.gallery.photo_5') }}</p>
        </div>
      </a>
      <a href="/assets/img/gallery/06-pt-hasnur-resources-terminal-peduli-benc.jpg" target="_blank" rel="noopener" class="reveal group relative aspect-square rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition block">
        <x-img src="/assets/img/gallery/06-pt-hasnur-resources-terminal-peduli-benc.jpg" alt="{{ __('publication.gallery.photo_6') }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" loading="lazy" sizes="(min-width: 1024px) 400px, (min-width: 640px) 50vw, 100vw" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/0 to-black/0 flex items-end p-4">
          <p class="text-white text-sm font-semibold leading-snug">{{ __('publication.gallery.photo_6') }}</p>
        </div>
      </a>
      <a href="/assets/img/gallery/07-hasnur-group-memberikan-bantuan-penangan.jpg" target="_blank" rel="noopener" class="reveal group relative aspect-square rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition block">
        <x-img src="/assets/img/gallery/07-hasnur-group-memberikan-bantuan-penangan.jpg" alt="{{ __('publication.gallery.photo_7') }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" loading="lazy" sizes="(min-width: 1024px) 400px, (min-width: 640px) 50vw, 100vw" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/0 to-black/0 flex items-end p-4">
          <p class="text-white text-sm font-semibold leading-snug">{{ __('publication.gallery.photo_7') }}</p>
        </div>
      </a>
      <a href="/assets/img/gallery/08-hasnur-group-memberikan-bantuan-penangan.jpg" target="_blank" rel="noopener" class="reveal group relative aspect-square rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition block">
        <x-img src="/assets/img/gallery/08-hasnur-group-memberikan-bantuan-penangan.jpg" alt="{{ __('publication.gallery.photo_8') }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" loading="lazy" sizes="(min-width: 1024px) 400px, (min-width: 640px) 50vw, 100vw" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/0 to-black/0 flex items-end p-4">
          <p class="text-white text-sm font-semibold leading-snug">{{ __('publication.gallery.photo_8') }}</p>
        </div>
      </a>
      <a href="/assets/img/gallery/09-she-hasnur-group-tanggap-darurat-di-sbu.jpg" target="_blank" rel="noopener" class="reveal group relative aspect-square rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition block">
        <x-img src="/assets/img/gallery/09-she-hasnur-group-tanggap-darurat-di-sbu.jpg" alt="{{ __('publication.gallery.photo_9') }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" loading="lazy" sizes="(min-width: 1024px) 400px, (min-width: 640px) 50vw, 100vw" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/0 to-black/0 flex items-end p-4">
          <p class="text-white text-sm font-semibold leading-snug">{{ __('publication.gallery.photo_9') }}</p>
        </div>
      </a>
    </div>
  </div>
</section>
@endsection
