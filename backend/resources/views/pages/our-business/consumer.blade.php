@extends('layouts.app')

@section('title', __('nav.business.consumer'))
@section('description', __('business.consumer.meta_description'))

@section('content')
<x-page-hero
    :breadcrumb="[
            ['label' => __('nav.home'), 'url' => \App\Support\Localization::url('/')],
            ['label' => __('nav.business'), 'url' => \App\Support\Localization::url('/page/our-business/logistic')],
            ['label' => __('nav.business.consumer'), 'url' => null]
        ]"
    :title="__('nav.business.consumer')"
    :description="__('business.consumer.hero_description')"
    :image="['src' => '/assets/img/pages/our-business-consumer/banner.png', 'alt' => __('nav.business.consumer').', Hasnur Group']"
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
    <p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.consumer.paragraph_1') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.consumer.paragraph_2') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.consumer.paragraph_3') }}</p>
<x-img src="/assets/img/pages/our-business-consumer/content-1.jpg" alt="{{ __('nav.business.consumer') }}" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.consumer.paragraph_4') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.consumer.paragraph_5') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.consumer.paragraph_6') }}</p>
<ul class="reveal list-disc pl-5 space-y-2 mb-6"><li class="text-slate-600 leading-relaxed">{{ __('business.consumer.list_1') }}</li><li class="text-slate-600 leading-relaxed">{{ __('business.consumer.list_2') }}</li><li class="text-slate-600 leading-relaxed">{{ __('business.consumer.list_3') }}</li><li class="text-slate-600 leading-relaxed">{{ __('business.consumer.list_4') }}</li><li class="text-slate-600 leading-relaxed">{{ __('business.consumer.list_5') }}</li></ul>
<x-img src="/assets/img/pages/our-business-consumer/content-2.jpg" alt="{{ __('nav.business.consumer') }}" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.consumer.paragraph_7') }}</p>

    <x-sbu-website-link :unit="$unit ?? null" />
  </div>
</section>
@endsection
