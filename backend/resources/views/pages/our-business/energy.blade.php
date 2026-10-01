@extends('layouts.app')

@section('title', __('nav.business.energy'))
@section('description', __('business.energy.meta_description'))

@section('content')
<x-page-hero
    :breadcrumb="[
            ['label' => __('nav.home'), 'url' => \App\Support\Localization::url('/')],
            ['label' => __('nav.business'), 'url' => \App\Support\Localization::url('/page/our-business/logistic')],
            ['label' => __('nav.business.energy'), 'url' => null]
        ]"
    :title="__('nav.business.energy')"
    :description="__('business.energy.hero_description')"
    :image="['src' => '/assets/img/pages/our-business-energy/banner.png', 'alt' => __('nav.business.energy').', Hasnur Group']"
    :tabs="$businessTabs" />

<section class="bg-slate-50 border-b border-slate-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-wrap gap-2">
    @foreach ($businessTabs as $tab)
      <a href="{{ $tab['url'] }}" class="px-5 py-2.5 rounded-full text-sm font-semibold transition {{ $tab['active'] ? 'bg-primary text-white' : 'bg-white text-slate-600 hover:text-primary border border-slate-200' }}">{{ $tab['label'] }}</a>
    @endforeach
  </div>
</section>

<section class="py-16 sm:py-20">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.energy.paragraph_1') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.energy.paragraph_2') }}</p>
<x-img src="/assets/img/pages/our-business-energy/content-1.jpg" alt="{{ __('nav.business.energy') }}" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<x-img src="/assets/img/pages/our-business-energy/content-2.jpg" alt="{{ __('nav.business.energy') }}" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.energy.paragraph_3') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.energy.paragraph_4') }}</p>
<x-img src="/assets/img/pages/our-business-energy/content-3.jpg" alt="{{ __('nav.business.energy') }}" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.energy.paragraph_5') }}</p>

    <x-sbu-website-link :unit="$unit ?? null" />
  </div>
</section>
@endsection
