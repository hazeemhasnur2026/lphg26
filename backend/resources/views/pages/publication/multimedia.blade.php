@extends('layouts.app')

@section('title', __('nav.publication.multimedia'))
@section('description', __('publication.multimedia.meta_description'))

@section('content')
<x-page-hero
    :breadcrumb="[
            ['label' => __('nav.home'), 'url' => \App\Support\Localization::url('/')],
            ['label' => __('nav.publication'), 'url' => \App\Support\Localization::url('/page/publication/latest-update')],
            ['label' => __('nav.publication.multimedia'), 'url' => null]
        ]"
    :title="__('nav.publication.multimedia')"
    :description="__('publication.multimedia.hero_description')"
    :image="['src' => '/assets/img/pages/publication-multimedia/banner.jpg', 'alt' => __('nav.publication.multimedia').', Hasnur Group']"
    :tabs="$publicationTabs" />

<section class="bg-slate-50 border-b border-slate-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-wrap gap-2">
    @foreach ($publicationTabs as $tab)
      <a href="{{ $tab['url'] }}" class="px-5 py-2.5 rounded-full text-sm font-semibold transition {{ $tab['active'] ? 'bg-primary text-white' : 'bg-white text-slate-600 hover:text-primary border border-slate-200' }}">{{ $tab['label'] }}</a>
    @endforeach
  </div>
</section>

<section class="py-16 sm:py-20">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <p class="reveal text-sm text-slate-500 mb-10">{{ __('publication.multimedia.intro') }}</p>
    <div class="grid gap-8">
      <a href="https://www.youtube.com/watch?v=rpsLPpff8Wg" target="_blank" rel="noopener" class="reveal group relative rounded-2xl overflow-hidden shadow-lg block bg-black">
        <x-img src="/assets/img/pages/publication-multimedia/content-1.jpg" alt="{{ __('publication.multimedia.video_title') }}" class="w-full h-auto object-cover opacity-90 group-hover:opacity-70 transition" loading="lazy" sizes="(min-width: 1280px) 1280px, 100vw" />
        <div class="absolute inset-0 flex items-center justify-center">
          <div class="w-16 h-16 rounded-full bg-white/90 flex items-center justify-center group-hover:scale-110 transition">
            <svg class="w-6 h-6 text-primary ml-1" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
          </div>
        </div>
        <p class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/80 to-transparent text-white text-sm font-semibold p-4">{{ __('publication.multimedia.video_title') }}</p>
      </a>
    </div>
  </div>
</section>
@endsection
