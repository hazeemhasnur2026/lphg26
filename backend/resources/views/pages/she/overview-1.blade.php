@extends('layouts.app')

@section('title', __('she.overview.title'))
@section('description', __('she.overview.meta_description'))

@section('content')
<x-page-hero
    :breadcrumb="[
            ['label' => __('nav.home'), 'url' => \App\Support\Localization::url('/')],
            ['label' => __('nav.she.full'), 'url' => \App\Support\Localization::url('/page/she/overview-1')],
            ['label' => __('nav.overview'), 'url' => null]
        ]"
    :title="__('nav.overview')"
    :description="__('she.overview.hero_description')"
    :image="['src' => '/assets/img/pages/she-overview-1/banner.jpg', 'alt' => __('nav.overview').', Hasnur Group']"
    :tabs="[
            ['label' => __('nav.overview'), 'url' => \App\Support\Localization::url('/page/she/overview-1'), 'active' => true],
            ['label' => __('nav.she.activity'), 'url' => \App\Support\Localization::url('/page/she/she-activity'), 'active' => false]
        ]" />

<section class="bg-slate-50 border-b border-slate-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-wrap gap-2">
    <a href="@lurl('/page/she/overview-1')" class="px-5 py-2.5 rounded-full text-sm font-semibold transition bg-primary text-white">{{ __('nav.overview') }}</a><a href="@lurl('/page/she/she-activity')" class="px-5 py-2.5 rounded-full text-sm font-semibold transition bg-white text-slate-600 hover:text-primary border border-slate-200">{{ __('nav.she.activity') }}</a>
  </div>
</section>

<section class="py-16 sm:py-20">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <h2 class="reveal text-2xl font-extrabold text-primary mt-10 mb-4 first:mt-0">{{ __('she.overview.heading') }}</h2>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('she.overview.paragraph_1') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('she.overview.paragraph_2') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('she.overview.paragraph_3') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('she.overview.paragraph_4') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('she.overview.paragraph_5') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('she.overview.paragraph_6') }}</p>
<x-img src="/assets/img/pages/she-overview-1/content-1.jpg" alt="{{ __('she.overview.title') }}" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<h2 class="reveal text-2xl font-extrabold text-primary mt-10 mb-4 first:mt-0">{{ __('she.overview.logo_heading') }}</h2>
<x-img src="/assets/img/pages/she-overview-1/content-2.png" alt="{{ __('she.overview.title') }}" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('she.overview.logo_meaning_intro') }}</p>
<ul class="reveal list-disc pl-5 space-y-2 mb-6"><li class="text-slate-600 leading-relaxed">{{ __('she.overview.logo_1') }}</li><li class="text-slate-600 leading-relaxed">{{ __('she.overview.logo_2') }}</li><li class="text-slate-600 leading-relaxed">{{ __('she.overview.logo_3') }}</li><li class="text-slate-600 leading-relaxed">{{ __('she.overview.logo_4') }}</li><li class="text-slate-600 leading-relaxed">{{ __('she.overview.logo_5') }}</li></ul>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('she.overview.logo_6') }}</p>
  </div>
</section>
@endsection
