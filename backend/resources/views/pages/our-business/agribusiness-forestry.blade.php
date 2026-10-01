@extends('layouts.app')

@section('title', __('nav.business.agribusiness'))
@section('description', __('business.agribusiness.meta_description'))

@section('content')
<x-page-hero
    :breadcrumb="[
            ['label' => __('nav.home'), 'url' => \App\Support\Localization::url('/')],
            ['label' => __('nav.business'), 'url' => \App\Support\Localization::url('/page/our-business/logistic')],
            ['label' => __('nav.business.agribusiness'), 'url' => null]
        ]"
    :title="__('nav.business.agribusiness')"
    :description="__('business.agribusiness.hero_description')"
    :image="['src' => '/assets/img/pages/our-business-agribusiness-forestry/banner.png', 'alt' => __('nav.business.agribusiness').', Hasnur Group']"
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
    <p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.agribusiness.paragraph_1') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.agribusiness.paragraph_2') }}</p>
<x-img src="/assets/img/pages/our-business-agribusiness-forestry/content-1.png" alt="{{ __('nav.business.agribusiness') }}" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.agribusiness.paragraph_3') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.agribusiness.paragraph_4') }}</p>
<x-img src="/assets/img/pages/our-business-agribusiness-forestry/content-2.png" alt="{{ __('nav.business.agribusiness') }}" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.agribusiness.paragraph_5') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.agribusiness.paragraph_6') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.agribusiness.paragraph_7') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.agribusiness.paragraph_8') }}</p>
<x-img src="/assets/img/pages/our-business-agribusiness-forestry/content-3.png" alt="{{ __('nav.business.agribusiness') }}" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.agribusiness.paragraph_9') }}</p>

    <x-sbu-website-link :unit="$unit ?? null" />
  </div>
</section>
@endsection
