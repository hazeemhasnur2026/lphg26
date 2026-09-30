@extends('layouts.app')

@section('title', 'Lowongan Kami')
@section('description', 'Lowongan karir di Hasnur Group, bergabung dengan bisnis terintegrasi terdepan di Indonesia.')

@section('content')
<x-page-hero
    :breadcrumb="[
            ['label' => 'Beranda', 'url' => '/'],
            ['label' => 'Karir', 'url' => '/page/career/our-vacancy'],
            ['label' => 'Lowongan Kami', 'url' => null]
        ]"
    title="Lowongan Kami"
    description="Hasnur Group mencari orang-orang dengan bakat terbaik untuk bisnis terintegrasi terdepan di Indonesia."
    :image="['src' => '/assets/img/pages/career-our-vacancy/banner.jpg', 'alt' => 'Lowongan Kami, Hasnur Group']"
     />

<section class="py-16 sm:py-20">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <p class="reveal text-slate-600 leading-relaxed mb-4">Hasnur Group mencari orang-orang dengan bakat terbaik untuk bisnis terintegrasi terdepan di Indonesia, mulai dari sektor kehutanan, pertambangan batubara, jalan dan terminal khusus batubara, agribisnis, transportasi, pelayaran, konstruksi, klub sepakbola profesional, dan pendidikan sekolah olahraga bahkan media massa pernah dalam cakupan.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">Dengan lebih dari 4000 orang tergabung di lebih dari 30 unit bisnis, Hasnur Group terus berupaya untuk menjadi perusahaan yang terdepan di Indonesia dan Asia Tenggara secara regional.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">Bagi anda yang selalu bertekad untuk dapat mengubah sebuah gagasan menjadi kenyataan, kami mengajak untuk bekerjasama secara harmonis dalam menjelajahi tantangan global dan memantapkan solusi inovatif untuk sebuah bisnis komprehensif yang niscaya akan segera terjadi.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">Kami menyambut orang-orang seperti anda untuk bergabung dengan mengembangkan peluang tak terbatas dalam karir bersama kami. Silakan kirim resume lengkap Anda ke: <a href="mailto:{{ config('mail.recruitment_recipient') }}" class="text-primary font-semibold underline">{{ config('mail.recruitment_recipient') }}</a></p>
  </div>
</section>

{{-- Situs lama tidak pernah memuat satu lowongan pun, hanya alamat email di
     atas. Daftarnya tetap dibangun supaya lowongan yang diisi dari admin
     langsung tampil, dan halaman ini jujur menyatakan keadaannya saat kosong
     ketimbang menampilkan lowongan karangan. --}}
<section class="pb-16 sm:pb-20">
  <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
    <h2 class="reveal text-2xl font-extrabold text-primary">Posisi yang Dibuka</h2>

    @forelse ($vacancies as $vacancy)
      <article class="reveal mt-6 rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-bold text-slate-800">{{ $vacancy->title }}</h3>

        <p class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-500">
          @if ($vacancy->department)<span>{{ $vacancy->department }}</span>@endif
          @if ($vacancy->location)<span>{{ $vacancy->location }}</span>@endif
          @if ($vacancy->deadline)
            <span>Ditutup {{ $vacancy->deadline->translatedFormat('d F Y') }}</span>
          @endif
        </p>

        @if ($vacancy->description)
          <div class="mt-4 text-sm leading-relaxed text-slate-600">{{ $vacancy->description }}</div>
        @endif

        @if ($vacancy->requirements)
          <p class="mt-4 text-sm font-semibold text-slate-700">Persyaratan</p>
          <div class="mt-1 text-sm leading-relaxed text-slate-600">{{ $vacancy->requirements }}</div>
        @endif

        <a href="mailto:{{ config('mail.recruitment_recipient') }}?subject={{ rawurlencode('Lamaran, '.$vacancy->title) }}"
           data-cta="kirim-lamaran"
           class="mt-5 inline-block rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark">
          Kirim Lamaran
        </a>
      </article>
    @empty
      <p class="reveal mt-6 rounded-2xl bg-slate-50 p-8 text-slate-500">
        Saat ini belum ada posisi yang dibuka. Anda tetap dapat mengirimkan resume
        ke <a href="mailto:{{ config('mail.recruitment_recipient') }}" class="font-semibold text-primary underline">{{ config('mail.recruitment_recipient') }}</a>
        untuk disimpan dalam basis data kami.
      </p>
    @endforelse
  </div>
</section>
@endsection
