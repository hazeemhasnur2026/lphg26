@extends('layouts.app')

@section('title', __('nav.business.education'))
@section('description', __('business.education.meta_description'))

@section('content')
<x-page-hero
    :breadcrumb="[
            ['label' => __('nav.home'), 'url' => \App\Support\Localization::url('/')],
            ['label' => __('nav.business'), 'url' => \App\Support\Localization::url('/page/our-business/logistic')],
            ['label' => __('nav.business.education'), 'url' => null]
        ]"
    :title="__('nav.business.education')"
    :description="__('business.education.hero_description')"
    :image="['src' => '/assets/img/pages/our-business-education/banner.jpg', 'alt' => __('nav.business.education').', Hasnur Group']"
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
    <p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.education.paragraph_1') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.education.paragraph_2') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.education.paragraph_3') }}</p>
<ul class="reveal list-disc pl-5 space-y-2 mb-6"><li class="text-slate-600 leading-relaxed">{{ __('business.education.list_1_1') }}</li><li class="text-slate-600 leading-relaxed">{{ __('business.education.list_1_2') }}</li></ul>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.education.paragraph_4') }}</p>
<x-img src="/assets/img/pages/our-business-education/content-1.jpg" alt="{{ __('nav.business.education') }}" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.education.paragraph_5') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.education.paragraph_6') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.education.paragraph_7') }}</p>
<ul class="reveal list-disc pl-5 space-y-2 mb-6"><li class="text-slate-600 leading-relaxed">{{ __('business.education.list_2_1') }}</li><li class="text-slate-600 leading-relaxed">{{ __('business.education.list_2_2') }}</li><li class="text-slate-600 leading-relaxed">{{ __('business.education.list_2_3') }}</li><li class="text-slate-600 leading-relaxed">{{ __('business.education.list_2_4') }}</li><li class="text-slate-600 leading-relaxed">{{ __('business.education.list_2_5') }}</li><li class="text-slate-600 leading-relaxed">{{ __('business.education.list_2_6') }}</li></ul>
<x-img src="/assets/img/pages/our-business-education/content-2.jpg" alt="{{ __('nav.business.education') }}" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.education.paragraph_8') }}</p>
<ul class="reveal list-disc pl-5 space-y-2 mb-6"><li class="text-slate-600 leading-relaxed">{{ __('business.education.list_3_1') }}</li><li class="text-slate-600 leading-relaxed">{{ __('business.education.list_3_2') }}</li><li class="text-slate-600 leading-relaxed">{{ __('business.education.list_3_3') }}</li><li class="text-slate-600 leading-relaxed">{{ __('business.education.list_3_4') }}</li><li class="text-slate-600 leading-relaxed">{{ __('business.education.list_3_5') }}</li></ul>
<x-img src="/assets/img/pages/our-business-education/content-3.jpg" alt="{{ __('nav.business.education') }}" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.education.paragraph_9') }}</p>
<x-img src="/assets/img/pages/our-business-education/content-4.jpg" alt="{{ __('nav.business.education') }}" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.education.paragraph_10') }}</p>
<x-img src="/assets/img/pages/our-business-education/content-5.jpg" alt="{{ __('nav.business.education') }}" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.education.paragraph_11') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.education.paragraph_12') }}</p>
<x-img src="/assets/img/pages/our-business-education/content-6.png" alt="{{ __('nav.business.education') }}" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.education.paragraph_13') }}</p>
<x-img src="/assets/img/pages/our-business-education/content-7.jpg" alt="{{ __('nav.business.education') }}" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.education.paragraph_14') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.education.paragraph_15') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.education.paragraph_16') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.education.paragraph_17') }}</p>

    <x-sbu-website-link :unit="$unit ?? null" />
  </div>
</section>
@endsection
