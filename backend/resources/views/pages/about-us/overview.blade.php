@extends('layouts.app')

@section('title', __('about.overview.title'))
@section('description', __('about.overview.meta_description'))

@section('content')
<x-page-hero
    :breadcrumb="[
            ['label' => __('nav.home'), 'url' => \App\Support\Localization::url('/')],
            ['label' => __('nav.about'), 'url' => \App\Support\Localization::url('/page/about-us/overview')],
            ['label' => __('nav.overview'), 'url' => null]
        ]"
    :title="__('about.overview.title')"
    :description="__('about.overview.hero_description')"
    :image="['src' => '/assets/img/pages/about-us-overview/banner.jpg', 'alt' => __('about.overview.title').', Hasnur Group']"
    :tabs="[
            ['label' => __('nav.overview'), 'url' => \App\Support\Localization::url('/page/about-us/overview'), 'active' => true],
            ['label' => __('nav.vision'), 'url' => \App\Support\Localization::url('/page/about-us/vision-mission'), 'active' => false],
            ['label' => __('about.core_value_tab'), 'url' => \App\Support\Localization::url('/page/about-us/core-value'), 'active' => false]
        ]" />

<section class="bg-slate-50 border-b border-slate-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-wrap gap-2">
    <a href="@lurl('/page/about-us/overview')" class="px-5 py-2.5 rounded-full text-sm font-semibold transition bg-primary text-white">{{ __('nav.overview') }}</a><a href="@lurl('/page/about-us/vision-mission')" class="px-5 py-2.5 rounded-full text-sm font-semibold transition bg-white text-slate-600 hover:text-primary border border-slate-200">{{ __('nav.vision') }}</a><a href="@lurl('/page/about-us/core-value')" class="px-5 py-2.5 rounded-full text-sm font-semibold transition bg-white text-slate-600 hover:text-primary border border-slate-200">{{ __('about.core_value_tab') }}</a>
  </div>
</section>

<section class="py-16 sm:py-20">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <h2 class="reveal text-2xl font-extrabold text-primary mt-10 mb-4 first:mt-0">{{ __('about.overview.heading') }}</h2>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('about.overview.paragraph_1') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('about.overview.paragraph_2') }}</p>
<x-img src="/assets/img/pages/about-us-overview/content-1.jpg" alt="{{ __('about.overview.title') }}" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('about.overview.paragraph_3') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('about.overview.paragraph_4') }}</p>
  </div>
</section>
@endsection
