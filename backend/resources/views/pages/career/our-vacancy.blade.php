@extends('layouts.app')

@section('title', __('career.vacancy.title'))
@section('description', __('career.vacancy.meta_description'))

@section('content')
<x-page-hero
    :breadcrumb="[
            ['label' => __('nav.home'), 'url' => \App\Support\Localization::url('/')],
            ['label' => __('nav.career'), 'url' => \App\Support\Localization::url('/page/career/our-vacancy')],
            ['label' => __('nav.career.vacancy'), 'url' => null]
        ]"
    :title="__('career.vacancy.title')"
    :description="__('career.vacancy.hero_description')"
    :image="['src' => '/assets/img/pages/career-our-vacancy/banner.jpg', 'alt' => __('career.vacancy.title').', Hasnur Group']"
     />

<section class="py-16 sm:py-20">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('career.vacancy.intro_1') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('career.vacancy.intro_2') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('career.vacancy.intro_3') }}</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">{{ __('career.vacancy.intro_4') }} <a href="mailto:{{ config('mail.recruitment_recipient') }}" class="text-primary font-semibold underline">{{ config('mail.recruitment_recipient') }}</a></p>
  </div>
</section>

{{-- Situs lama tidak pernah memuat satu lowongan pun, hanya alamat email di
     atas. Daftarnya tetap dibangun supaya lowongan yang diisi dari admin
     langsung tampil, dan halaman ini jujur menyatakan keadaannya saat kosong
     ketimbang menampilkan lowongan karangan. --}}
<section class="pb-16 sm:pb-20">
  <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
    <h2 class="reveal text-2xl font-extrabold text-primary">{{ __('career.vacancy.open_positions') }}</h2>

    @forelse ($vacancies as $vacancy)
      <article class="reveal mt-6 rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-bold text-slate-800">{{ $vacancy->title }}</h3>

        <p class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-500">
          @if ($vacancy->department)<span>{{ $vacancy->department }}</span>@endif
          @if ($vacancy->location)<span>{{ $vacancy->location }}</span>@endif
          @if ($vacancy->deadline)
            <span>{{ __('career.vacancy.closed_on', ['date' => $vacancy->deadline->translatedFormat('d F Y')]) }}</span>
          @endif
        </p>

        @if ($vacancy->description)
          <div class="mt-4 text-sm leading-relaxed text-slate-600">{{ $vacancy->description }}</div>
        @endif

        @if ($vacancy->requirements)
          <p class="mt-4 text-sm font-semibold text-slate-700">{{ __('career.vacancy.requirements') }}</p>
          <div class="mt-1 text-sm leading-relaxed text-slate-600">{{ $vacancy->requirements }}</div>
        @endif

        <a href="mailto:{{ config('mail.recruitment_recipient') }}?subject={{ rawurlencode(__('career.vacancy.email_subject', ['title' => $vacancy->title])) }}"
           data-cta="kirim-lamaran"
           class="mt-5 inline-block rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark">
          {{ __('career.vacancy.apply') }}
        </a>
      </article>
    @empty
      <p class="reveal mt-6 rounded-2xl bg-slate-50 p-8 text-slate-500">
        {{ __('career.vacancy.empty_before') }}
        <a href="mailto:{{ config('mail.recruitment_recipient') }}" class="font-semibold text-primary underline">{{ config('mail.recruitment_recipient') }}</a>
        {{ __('career.vacancy.empty_after') }}
      </p>
    @endforelse
  </div>
</section>
@endsection
