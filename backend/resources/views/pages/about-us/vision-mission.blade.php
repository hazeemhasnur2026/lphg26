@extends('layouts.app')

@section('title', __('about.vision.title'))
@section('description', __('about.vision.meta_description'))

@section('content')
<x-page-hero
    :breadcrumb="[
            ['label' => __('nav.home'), 'url' => \App\Support\Localization::url('/')],
            ['label' => __('nav.about'), 'url' => \App\Support\Localization::url('/page/about-us/overview')],
            ['label' => __('nav.vision'), 'url' => null]
        ]"
    :title="__('about.vision.title')"
    :description="__('about.vision.hero_description')"
    :image="['src' => '/assets/img/pages/about-us-vision-mission/banner.jpg', 'alt' => __('about.vision.title').', Hasnur Group']"
    :tabs="[
            ['label' => __('nav.overview'), 'url' => \App\Support\Localization::url('/page/about-us/overview'), 'active' => false],
            ['label' => __('nav.vision'), 'url' => \App\Support\Localization::url('/page/about-us/vision-mission'), 'active' => true],
            ['label' => __('about.core_value_tab'), 'url' => \App\Support\Localization::url('/page/about-us/core-value'), 'active' => false]
        ]" />

<section class="bg-slate-50 border-b border-slate-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-wrap gap-2">
    <a href="@lurl('/page/about-us/overview')" class="px-5 py-2.5 rounded-full text-sm font-semibold transition bg-white text-slate-600 hover:text-primary border border-slate-200">{{ __('nav.overview') }}</a><a href="@lurl('/page/about-us/vision-mission')" class="px-5 py-2.5 rounded-full text-sm font-semibold transition bg-primary text-white">{{ __('nav.vision') }}</a><a href="@lurl('/page/about-us/core-value')" class="px-5 py-2.5 rounded-full text-sm font-semibold transition bg-white text-slate-600 hover:text-primary border border-slate-200">{{ __('about.core_value_tab') }}</a>
  </div>
</section>

<section class="py-16 sm:py-20">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <h2 class="reveal text-2xl font-extrabold text-primary mt-10 mb-4 first:mt-0">{{ __('about.vision.vision_heading') }}</h2>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('about.vision.vision_text') }}</p>
<h2 class="reveal text-2xl font-extrabold text-primary mt-10 mb-4 first:mt-0">{{ __('about.vision.mission_heading') }}</h2>
<ul class="reveal list-disc pl-5 space-y-2 mb-6"><li class="text-slate-600 leading-relaxed">{{ __('about.vision.mission_1') }}</li><li class="text-slate-600 leading-relaxed">{{ __('about.vision.mission_2') }}</li><li class="text-slate-600 leading-relaxed">{{ __('about.vision.mission_3') }}</li><li class="text-slate-600 leading-relaxed">{{ __('about.vision.mission_4') }}</li></ul>
  </div>
</section>
@endsection
