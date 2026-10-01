@extends('layouts.app')

@section('title', __('legal.privacy.title'))
@section('description', __('legal.privacy.meta_description'))

@section('content')
<x-page-hero
    :breadcrumb="[
        ['label' => __('nav.home'), 'url' => \App\Support\Localization::url('/')],
        ['label' => __('legal.privacy.title'), 'url' => null],
    ]"
    :title="__('legal.privacy.title')"
    :description="__('legal.privacy.hero_description')" />

<section class="py-16 sm:py-20">
  <div class="prose-hasnur mx-auto max-w-3xl space-y-6 px-4 leading-relaxed text-slate-700 sm:px-6 lg:px-8">

    {{-- Tiga hal di bawah adalah keputusan perusahaan, bukan keputusan teknis,
         dan belum bisa diisi dari kode. Kotak ini harus hilang sebelum situs
         diakses publik. --}}
    <div role="note" class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-sm">
      <p class="font-bold text-amber-900">{{ __('legal.privacy.draft_title') }}</p>
      <p class="mt-2 text-amber-800">
        {!! __('legal.privacy.draft_body') !!}
      </p>
    </div>

    <h2>{{ __('legal.privacy.controller_heading') }}</h2>
    <p>
      {!! __('legal.privacy.controller_body') !!}
    </p>

    <h2>{{ __('legal.privacy.collected_heading') }}</h2>
    <p>{{ __('legal.privacy.collected_intro') }}</p>
    <ul>
      <li>{!! __('legal.privacy.collected_item_1') !!}</li>
      <li>{!! __('legal.privacy.collected_item_2') !!}</li>
    </ul>
    <p>
      {{ __('legal.privacy.collected_note') }}
    </p>

    <h2>{{ __('legal.privacy.purpose_heading') }}</h2>
    <p>
      {{ __('legal.privacy.purpose_body') }}
    </p>
    <p>{!! __('legal.privacy.purpose_basis') !!}</p>

    <h2>{{ __('legal.privacy.access_heading') }}</h2>
    <p>
      {{ __('legal.privacy.access_body_1') }}
    </p>
    <p>
      {{ __('legal.privacy.access_body_2') }}
    </p>

    <h2>{{ __('legal.privacy.analytics_heading') }}</h2>
    <p>
      {{ __('legal.privacy.analytics_body_1') }}
    </p>
    <p>
      {!! __('legal.privacy.analytics_body_2') !!}
    </p>
    <p>
      {{ __('legal.privacy.analytics_body_3') }}
    </p>

    <h2>{{ __('legal.privacy.cookie_heading') }}</h2>
    <p>
      {{ __('legal.privacy.cookie_body') }}
    </p>

    <h2>{{ __('legal.privacy.retention_heading') }}</h2>
    <p>{!! __('legal.privacy.retention_body') !!}</p>

    <h2>{{ __('legal.privacy.rights_heading') }}</h2>
    <p>
      {{ __('legal.privacy.rights_body') }}
    </p>
    <p>
      {{ __('legal.privacy.rights_request_before') }}
      <a href="@lurl('/page/contact/contact-us')">{{ __('legal.privacy.rights_form_link') }}</a>
      {{ __('legal.privacy.rights_request_after') }}
    </p>

    <h2>{{ __('legal.privacy.security_heading') }}</h2>
    <p>
      {{ __('legal.privacy.security_body') }}
    </p>

    <h2>{{ __('legal.privacy.changes_heading') }}</h2>
    <p>
      {{ __('legal.privacy.changes_body') }}
    </p>

    <p class="text-sm text-slate-500">{{ __('legal.privacy.last_updated') }}</p>
  </div>
</section>
@endsection
