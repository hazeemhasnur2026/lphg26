@extends('layouts.app')

@section('title', __('nav.business.investment'))
@section('description', __('business.investment.meta_description'))

@section('content')
<x-page-hero
    :breadcrumb="[
            ['label' => __('nav.home'), 'url' => \App\Support\Localization::url('/')],
            ['label' => __('nav.business'), 'url' => \App\Support\Localization::url('/page/our-business/logistic')],
            ['label' => __('nav.business.investment'), 'url' => null]
        ]"
    :title="__('nav.business.investment')"
    :description="__('business.investment.hero_description')"
    :image="['src' => '/assets/img/pages/our-business-investment/banner.png', 'alt' => __('nav.business.investment').', Hasnur Group']"
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
    <p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('business.investment.paragraph_1') }}</p>

    <x-sbu-website-link :unit="$unit ?? null" />
  </div>
</section>
@endsection
