@extends('layouts.app')

@section('title', __('csr.overview.title'))
@section('description', __('csr.overview.meta_description'))

@section('content')
<x-page-hero
    :breadcrumb="[
            ['label' => __('nav.home'), 'url' => \App\Support\Localization::url('/')],
            ['label' => __('nav.csr.full'), 'url' => \App\Support\Localization::url('/page/csr/csr-overview')],
            ['label' => __('nav.overview'), 'url' => null]
        ]"
    :title="__('nav.overview')"
    :description="__('csr.overview.hero_description')"
    :image="['src' => '/assets/img/pages/csr-csr-overview/banner.jpg', 'alt' => __('nav.overview').', Hasnur Group']"
    :tabs="[
            ['label' => __('nav.overview'), 'url' => \App\Support\Localization::url('/page/csr/csr-overview'), 'active' => true],
            ['label' => __('nav.csr.activity'), 'url' => \App\Support\Localization::url('/page/csr/csr-activity'), 'active' => false]
        ]" />

<section class="bg-slate-50 border-b border-slate-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-wrap gap-2">
    <a href="@lurl('/page/csr/csr-overview')" class="px-5 py-2.5 rounded-full text-sm font-semibold transition bg-primary text-white">{{ __('nav.overview') }}</a><a href="@lurl('/page/csr/csr-activity')" class="px-5 py-2.5 rounded-full text-sm font-semibold transition bg-white text-slate-600 hover:text-primary border border-slate-200">{{ __('nav.csr.activity') }}</a>
  </div>
</section>

<section class="py-16 sm:py-20">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <h2 class="reveal text-2xl font-extrabold text-primary mt-10 mb-4 first:mt-0">{{ __('csr.overview.heading') }}</h2>
<x-img src="/assets/img/pages/csr-csr-overview/content-1.jpg" alt="{{ __('csr.overview.title') }}" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('csr.overview.paragraph_1') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('csr.overview.paragraph_2') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('csr.overview.paragraph_3') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('csr.overview.paragraph_4') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('csr.overview.paragraph_5') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('csr.overview.paragraph_6') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('csr.overview.paragraph_7') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('csr.overview.paragraph_8') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('csr.overview.paragraph_9') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('csr.overview.paragraph_10') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('csr.overview.paragraph_11') }}</p>
  </div>
</section>
@endsection
