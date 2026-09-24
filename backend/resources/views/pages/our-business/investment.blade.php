@extends('layouts.app')

@section('title', 'Investasi')
@section('description', 'SBU Investasi Hasnur Group, pengembangan bisnis melalui investasi internal dan eksternal.')

@section('content')
<x-page-hero
    :breadcrumb="[
            ['label' => 'Beranda', 'url' => '/'],
            ['label' => 'Bisnis Kami', 'url' => '/page/our-business/logistic'],
            ['label' => 'Investasi', 'url' => null]
        ]"
    title="Investasi"
    description="Mendukung pengembangan berbagai bisnis grup melalui investasi internal maupun eksternal."
    :image="['src' => '/assets/img/pages/our-business-investment/banner.png', 'alt' => 'Investasi, Hasnur Group']"
    :tabs="[
            ['label' => 'Logistik', 'url' => '/page/our-business/logistic', 'active' => false],
            ['label' => 'Agribisnis & Kehutanan', 'url' => '/page/our-business/agribusiness-forestry', 'active' => false],
            ['label' => 'Energi', 'url' => '/page/our-business/energy', 'active' => false],
            ['label' => 'Teknologi & Jasa', 'url' => '/page/our-business/technology-services', 'active' => false],
            ['label' => 'Pendidikan', 'url' => '/page/our-business/education', 'active' => false],
            ['label' => 'Kebutuhan Pelanggan', 'url' => '/page/our-business/consumer', 'active' => false],
            ['label' => 'Investasi', 'url' => '/page/our-business/investment', 'active' => true]
        ]" />

<section class="py-16 sm:py-20">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <p class="reveal text-slate-600 leading-relaxed mb-4">Melalui SBU Investasi, Hasnur Group terus mengembangkan berbagai bisnis melalui dukungan investasi, baik dari berbagai entitas di Hasnur Group dan juga eksternal.</p>

    <x-sbu-website-link :unit="$unit ?? null" />
  </div>
</section>
@endsection
