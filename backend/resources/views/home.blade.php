@extends('layouts.app')

@section('title', 'Welcome to Hasnur Group')
@section('description', 'Hasnur Group adalah grup usaha yang didirikan oleh H. Abdussamad Sulaiman HB dan Hj. Nurhayati, pengusaha asal Kalimantan. Grow Together Develop the Future.')
{{-- Mengikuti bahasa yang dibuka; url('/') selalu menunjuk versi Indonesia --}}
@section('canonical', \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getLocalizedURL(app()->getLocale(), url('/')))

@push('head')
<script type="application/ld+json">
{!! \App\Support\StructuredData::organization() !!}
</script>
@endpush

@section('content')
<!-- ============ HERO (logo lockup + founder quote, digabung) ============ -->
<!-- Dominant palette sampled straight from assets/img/logo.png: blue #0F2747,
     yellow #C9A227, green #007A53, red #E03633. White up top so the
     transparent-PNG lockup and founder photo sit cleanly on the page, then
     fading into a soft brand-blue tint toward the bottom for depth instead
     of a flat white all the way down. -->
<section id="beranda" class="relative overflow-hidden bg-gradient-to-b from-white via-white via-70% to-blue-100 text-slate-800">

  <!-- Seated below the 65px sticky header rather than at top-0: anchored to
       the literal corner, 72% of it sat behind the navbar and read as a
       clipped strip instead of a corner accent. z-10 lifts both corners
       above the blurred blobs (one sits on this exact corner and was
       muddying the pattern) but still under the z-50 header. -->
  <x-img src="/assets/img/keyvisual-corner.png" alt="" aria-hidden="true"
       width="1593" height="770" draggable="false"
       class="pointer-events-none select-none absolute top-16 left-0 -translate-x-1/4 -translate-y-1/4
              hidden sm:block sm:w-36 lg:w-72 h-auto z-10" sizes="(min-width: 1024px) 288px, 144px" :dimensions="false" />
  <x-img src="/assets/img/keyvisual-corner.png" alt="" aria-hidden="true"
       width="1593" height="770" draggable="false" loading="lazy"
       class="pointer-events-none select-none absolute bottom-0 right-0 translate-x-1/4 translate-y-1/4 rotate-180
              hidden sm:block sm:w-36 lg:w-72 h-auto z-10" sizes="(min-width: 1024px) 288px, 144px" :dimensions="false" />

  <div class="absolute -top-24 -left-16" data-parallax="0.12">
    <div class="w-80 h-80 rounded-full bg-logo-yellow/15 blur-3xl animate-blob"></div>
  </div>
  <div class="absolute -top-24 -right-16" data-parallax="0.12">
    <div class="w-80 h-80 rounded-full bg-logo-green/15 blur-3xl animate-blob" style="animation-delay:-3s"></div>
  </div>
  <div class="absolute -bottom-16 left-1/3" data-parallax="0.07">
    <div class="w-64 h-64 rounded-full bg-logo-red/10 blur-3xl animate-blob" style="animation-delay:-6s"></div>
  </div>
  <div class="absolute bottom-10 right-1/4" data-parallax="0.07">
    <div class="w-64 h-64 rounded-full bg-logo-blue/10 blur-3xl animate-blob" style="animation-delay:-1.5s"></div>
  </div>

  <!-- Decorative background: drifting brand-color blobs, wrapped in a
       parallax layer each (data-parallax, driven by assets/js/main.js),
       the wrapper handles scroll-parallax translation while the inner div
       keeps its own CSS "blob" drift animation, so the two transforms
       don't fight over the same element. -->

  <!-- Two-column row: brand logo lockup (left) + founder photo & quote
       (right), vertically centered per column. On mobile they stack
       (logo first, founder quote below). -->
  <div class="relative max-w-7xl mx-auto lg:grid lg:grid-cols-2 lg:gap-16 lg:items-center px-6 sm:px-10 lg:px-8 pt-24 sm:pt-28 lg:pt-16">

    <!-- LEFT: official 60th anniversary lockup (transparent PNG, from the
         brand's own keyvisual/guide assets), shown at full size directly
         on the white background. No card, no crop needed. -->
    <div class="reveal">
      <!-- The lockup is the page's visible headline, so it carries the h1 and
           its alt text supplies the heading. Wrapping rather than adding a
           hidden heading keeps one headline instead of two competing ones. -->
      <h1 class="m-0">
        <x-img src="/assets/img/logo-60th.png" alt="Hasnur Group, 60 Tahun Berkarya, Stronger Values, More Sustainable Future" class="w-full max-w-xl mx-auto lg:mx-0 h-auto" loading="eager" sizes="(min-width: 1024px) 576px, 100vw" />
      </h1>
    </div>

    <!-- RIGHT: founder photo + quote, moved here from its own section so
         it reads as one hero moment with the logo. -->
    <div class="relative flex flex-col justify-center px-2 sm:px-6 lg:px-0 py-10 lg:py-16 text-center lg:text-left reveal">
      <x-img src="/assets/img/founder.png" alt="H. Abdussamad Sulaiman HB & Hj. Nurhayati" class="w-64 sm:w-80 lg:w-96 h-auto mx-auto lg:mx-0 object-contain drop-shadow-lg mb-6" loading="lazy" data-parallax="0.05" sizes="(min-width: 1024px) 384px, 320px" />
      <svg class="w-8 h-8 mx-auto lg:mx-0 text-accent mb-3" fill="currentColor" viewBox="0 0 32 32"><path d="M9.352 4C4.456 7.456 1 13.12 1 19.36 1 24.88 4.288 28 8.464 28c3.88 0 6.552-3.16 6.552-6.616 0-3.16-2.176-5.976-5.336-5.976-.6 0-1.336.104-1.512.208.408-2.744 2.984-5.976 5.568-7.6L9.352 4zm16.512 0C20.968 7.456 17.512 13.12 17.512 19.36c0 5.52 3.288 8.64 7.464 8.64 3.88 0 6.552-3.16 6.552-6.616 0-3.16-2.176-5.976-5.336-5.976-.6 0-1.336.104-1.512.208.408-2.744 2.984-5.976 5.568-7.6L25.864 4z"/></svg>
      <p class="text-lg sm:text-xl font-semibold text-slate-800 leading-relaxed">
        Komitmen pendiri, H. Abdussamad Sulaiman HB dan Hj. Nurhayati, untuk terus mengabdi dan membangun bersama masyarakat Kalimantan Selatan, "Banua", menjadi fondasi perjalanan Hasnur Group hingga hari ini.
      </p>
      <p class="mt-5 font-bold text-primary">H. Abdussamad Sulaiman HB &amp; Hj. Nurhayati</p>
      <p class="text-sm text-slate-500">Pendiri Hasnur Group</p>
    </div>
  </div>

  <!-- Supporting copy + CTA -->
  <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-16 sm:pb-20 text-center reveal">
    <p class="text-lg text-slate-600">
      Merayakan 60 tahun perjalanan Hasnur Group, nilai-nilai perusahaan yang semakin kuat dalam melangkah menuju masa depan yang berkelanjutan.
    </p>
    <div class="mt-8 flex flex-wrap gap-4 justify-center">
      <a href="/page/about-us/overview" data-cta="beranda-tentang-kami" class="px-6 py-3 rounded-md bg-primary text-white font-bold hover:bg-primary-dark transition">Tentang Kami</a>
      <a href="#bisnis-kami" data-cta="beranda-lihat-bisnis" class="px-6 py-3 rounded-md border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 transition">Lihat Bisnis Kami</a>
    </div>
    <a href="#tentang-kami" class="hidden sm:inline-flex mt-10 text-slate-400 hover:text-primary transition animate-bounce" aria-label="Gulir ke bawah">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
    </a>
  </div>
</section>


<!-- ============ NILAI INTI ============ -->
{{-- Dipindahkan ke beranda dari menu Tentang Kami. Halaman rincinya tetap ada
     di /page/about-us/core-value. URL itu sudah terindeks, jadi tidak dihapus,
     hanya tidak lagi dipasang di navigasi.

     Warna tiap kartu diambil langsung dari piksel pita gambar rodanya, bukan
     dicocokkan dengan mata, supaya kartu dan segmen roda benar-benar sewarna. --}}
@php
    $coreValues = [
        ['name' => 'Pantang Menyerah', 'color' => '#DA272C', 'description' => 'Selalu berusaha maksimal dan tuntas dalam menghadapi setiap tantangan, serta memiliki sikap ulet, tangguh dan tidak mudah menyerah untuk mewujudkan setiap tantangan/tugas menjadi kenyataan.'],
        ['name' => 'Keadilan', 'color' => '#83C034', 'description' => 'Memberikan perlakuan yang seimbang antara hak dan kewajiban yang dimiliki karyawan dan perusahaan, serta keinginan untuk memberikan manfaat yang maksimal bagi seluruh pemangku kepentingan perusahaan.'],
        ['name' => 'Kebersamaan', 'color' => '#F9EE15', 'description' => 'Memiliki perasaan senasib dan sepenanggungan, serta saling memiliki, saling peduli, saling memperhatikan, dan saling mengingatkan dalam ikatan kekeluargaan sehingga tercipta hubungan personal yang erat.'],
        ['name' => 'Bijaksana', 'color' => '#1891D1', 'description' => 'Selalu mempertimbangkan berbagai aspek, menggunakan akal dan nurani dalam pengambilan keputusan guna mendapatkan manfaat yang terbaik bagi seluruh pemangku kepentingan.'],
        ['name' => 'Kesatuan Sikap', 'color' => '#8E5343', 'description' => 'Keserasian pandangan, keyakinan dan tindakan segenap anggota perusahaan dengan tetap menjunjung tinggi nilai-nilai luhur serta mengutamakan keutuhan prinsip moral dan etika demi tercapainya tujuan bersama.'],
        ['name' => 'Dapat Dipercaya', 'color' => '#E77820', 'description' => 'Bekerja sepenuh hati, jujur, memegang teguh nilai etika dan moral, dan dapat diandalkan oleh seluruh pemangku kepentingan dalam menghadapi setiap penugasan maupun tantangan.'],
        ['name' => 'Disiplin', 'color' => '#964377', 'description' => 'Mengarahkan diri sendiri dan orang lain untuk melaksanakan tugas dan kewajiban secara terus-menerus sesuai dengan norma dan peraturan yang berlaku.'],
    ];
@endphp

<section id="nilai-inti" class="relative bg-slate-50" data-core-value>
  {{-- Jalur gulir: tingginya yang menentukan berapa jauh halaman harus digulir
       sebelum seluruh kartu selesai muncul. Hanya berlaku dari lg ke atas,
       di layar kecil tidak ada ruang untuk roda dan kartu berdampingan, jadi
       semuanya ditumpuk biasa tanpa ditahan. --}}
  <div class="core-value-track relative lg:h-[300vh]">
    <div class="core-value-panel lg:sticky lg:top-0 lg:flex lg:min-h-screen lg:items-center">
      <div class="mx-auto w-full max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8 lg:py-6">
        <div class="mx-auto max-w-2xl text-center">
          <h2 class="text-2xl font-extrabold text-primary sm:text-3xl">Tujuh Nilai yang Kami Pegang</h2>
        </div>

        <div class="mt-6 grid items-center gap-8 lg:grid-cols-[16rem_minmax(0,1fr)]">
          <div class="core-value-stage flex justify-center lg:justify-start">
            <div class="core-value-wheel relative aspect-square w-full max-w-[min(70vw,16rem)]">
              <x-img src="/assets/img/nilai_inti/core-value-outer.png"
                     alt="Roda nilai inti Hasnur Group: Pantang Menyerah, Keadilan, Kebersamaan, Bijaksana, Kesatuan Sikap, Dapat Dipercaya, dan Disiplin"
                     sizes="(min-width: 1024px) 256px, 70vw" loading="lazy"
                     class="core-value-ring absolute inset-0 h-full w-full" />

              {{-- Lubang cincinnya terukur 83,1% dari lebar gambar; emblem
                   dipasang 78% supaya tersisa sela tipis. --}}
              <x-img src="/assets/img/nilai_inti/core-value-inner.png"
                     alt="" aria-hidden="true"
                     sizes="(min-width: 1024px) 200px, 55vw" loading="lazy"
                     class="absolute left-1/2 top-1/2 w-[78%] -translate-x-1/2 -translate-y-1/2" />
            </div>
          </div>

          {{-- Kartunya selalu ada di HTML. Yang disembunyikan hanya tampilannya,
               dan itu pun cuma setelah skrip menyatakan dirinya berjalan lewat
               kelas core-value-scripted. Tanpa JavaScript semuanya tetap
               terbaca, begitu pula bagi mesin pencari. --}}
          <ul class="grid gap-3 sm:grid-cols-2">
            @foreach ($coreValues as $index => $value)
              <li class="core-value-card rounded-2xl border border-slate-100 bg-white p-4 shadow-sm" data-index="{{ $index }}">
                <span class="block h-1.5 w-10 rounded-full" style="background:{{ $value['color'] }}"></span>
                <h3 class="mt-2.5 text-xs font-bold uppercase tracking-wide text-slate-800">{{ $value['name'] }}</h3>
                <p class="mt-1.5 text-[13px] leading-relaxed text-slate-600">{{ $value['description'] }}</p>
              </li>
            @endforeach
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ GALERI SLIDER (konten sementara, foto seadanya) ============ -->
<!-- Foto dokumentasi lama masih dipakai sebagai isi sementara, tapi sumbernya
     kini tabel slide_banners. Penggantinya bisa diunggah dari admin tanpa
     menyunting berkas JavaScript. -->
{{-- Slide datang dari tabel slide_banners. createCarousel() dipanggil langsung
     dengan datanya, bukan lewat gallerySlider() yang membawa arraynya sendiri,
     factory itu tetap ada di main.js karena prototipe statis masih memakainya. --}}
<section class="relative py-16 sm:py-20 bg-white overflow-hidden"
  x-data="createCarousel(@js($slides), { intervalMs: 5000 })" x-init="init()"
  x-on:mouseenter="stop()" x-on:mouseleave="play()">

  <div class="absolute -top-24 -right-16" data-parallax="0.12">
    <div class="w-80 h-80 rounded-full bg-logo-blue/15 blur-3xl animate-blob" style="animation-delay:-2s"></div>
  </div>
  <div class="absolute -bottom-24 -left-16" data-parallax="0.07">
    <div class="w-64 h-64 rounded-full bg-logo-yellow/10 blur-3xl animate-blob" style="animation-delay:-5s"></div>
  </div>


  <div class="relative max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center reveal">
    <span class="text-sm font-bold uppercase tracking-widest text-accent">Galeri Kegiatan</span>
    <h2 class="mt-3 text-3xl sm:text-4xl font-extrabold text-primary">Momen Hasnur Group</h2>
    <p class="mt-4 text-slate-600 text-sm">Konten sementara memakai dokumentasi yang tersedia, akan digantikan feed dinamis dari CMS Publikasi.</p>
  </div>

  <!-- Full-bleed slide frame. No max-width wrapper, no rounded border,
       no shadow card; the photo runs edge to edge like the hero slider. -->
  <div class="relative mt-12 reveal">
    <div class="relative w-full aspect-[16/9] sm:aspect-[21/9] lg:aspect-[28/9]">
      <template x-for="(s, i) in slides" :key="i">
        <img :src="s.src" :alt="s.alt" x-show="active === i" x-cloak
             x-transition:enter="transition ease-out duration-700"
             x-transition:enter-start="opacity-0 scale-105"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="absolute inset-0 w-full h-full object-cover" loading="lazy">
      </template>

      <template x-for="(s, i) in slides" :key="'g-caption-' + i">
        <div x-show="active === i" x-cloak class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent p-5 sm:p-8">
          <h3 class="text-white font-bold text-lg sm:text-xl" x-text="s.title"></h3>
          <p class="text-white/80 text-sm mt-1" x-text="s.subtitle"></p>
        </div>
      </template>

      <button x-on:click="prev()" class="absolute left-3 sm:left-6 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/20 hover:bg-white/30 backdrop-blur ring-1 ring-white/40 flex items-center justify-center transition" aria-label="Slide sebelumnya">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
      </button>
      <button x-on:click="next()" class="absolute right-3 sm:right-6 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/20 hover:bg-white/30 backdrop-blur ring-1 ring-white/40 flex items-center justify-center transition" aria-label="Slide berikutnya">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      </button>
    </div>

    <div class="mt-5 flex justify-center gap-2">
      <template x-for="(s, i) in slides" :key="'g-dot-' + i">
        <button x-on:click="goTo(i)" class="h-2 rounded-full transition-all" :class="active === i ? 'w-6 bg-primary' : 'w-2 bg-primary/20'" :aria-label="'Slide ' + (i + 1)"></button>
      </template>
    </div>
  </div>
</section>

<!-- ============ TENTANG KAMI ============ -->
<section id="tentang-kami" class="relative py-20 sm:py-28 overflow-hidden">

  <div class="absolute -top-24 -left-16" data-parallax="0.12">
    <div class="w-80 h-80 rounded-full bg-logo-green/15 blur-3xl animate-blob" style="animation-delay:-4s"></div>
  </div>
  <div class="absolute -bottom-24 -right-16" data-parallax="0.07">
    <div class="w-64 h-64 rounded-full bg-logo-yellow/10 blur-3xl animate-blob" style="animation-delay:-1s"></div>
  </div>
  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 gap-14 items-center">
    <div class="reveal">
      <x-img src="/assets/img/image-about.png" alt="Tentang Hasnur Group" class="rounded-2xl shadow-lg w-full h-auto" loading="lazy" data-parallax="0.05" sizes="(min-width: 1024px) 600px, 100vw" />
    </div>
    <div class="reveal">
      <span class="text-sm font-bold uppercase tracking-widest text-accent">Tentang Kami</span>
      <h2 class="mt-3 text-3xl sm:text-4xl font-extrabold text-primary">Berawal dari Sungai Barito, 1966</h2>
      <p class="mt-6 text-slate-600 leading-relaxed">
        Hasnur Group didirikan oleh pengusaha asli Kalimantan, H. Abdussamad Sulaiman HB, bersama istrinya Hj. Nurhayati, yang memulai usaha di bidang angkutan sungai pada tahun 1966.
      </p>
      <p class="mt-4 text-slate-600 leading-relaxed">
        Nama <span class="font-semibold text-primary">"HASNUR"</span> merupakan gabungan inisial nama keduanya, <span class="font-semibold">HAS</span> (H. Abdussamad Sulaiman) + <span class="font-semibold">NUR</span> (Nurhayati).
      </p>
      <a href="/page/about-us/overview" class="mt-8 inline-flex items-center gap-2 font-bold text-primary hover:text-primary-dark transition">
        Selengkapnya
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
      </a>
    </div>
  </div>
</section>

<!-- ============ TIMELINE SEJARAH (serpentine photo path) ============ -->
<!-- Reference: an "A Day in the Life"-style winding infographic (circular
     photo nodes on a continuous path, alternating direction row by row).
     Adapted with our own 15 milestones/photos. Desktop/tablet (sm+): 3
     rows x 5 nodes (wide layout, not centered/cramped), each row
     alternating left→right / right→left via `order-*` utilities
     (source/DOM order stays chronological for accessibility. Only visual
     placement reverses). A horizontal connector runs through each row and
     a vertical connector drops into the next row at the alternating edge.
     Every node/line carries an inline transition-delay so the whole path
     animates in strict chronological order. Nodes cascade in one after
     another (not all at once) and each row's connector "draws itself"
     (scale 0→1 from the correct edge) right as its nodes appear. Below sm:
     a simple single-column vertical list. The serpentine layout doesn't
     have room to breathe on a phone. -->
<section class="relative py-20 sm:py-28 bg-slate-50 overflow-hidden">

  <div class="absolute -top-24 -right-16" data-parallax="0.12">
    <div class="w-80 h-80 rounded-full bg-logo-yellow/15 blur-3xl animate-blob" style="animation-delay:-3s"></div>
  </div>
  <div class="absolute -bottom-24 -left-16" data-parallax="0.07">
    <div class="w-64 h-64 rounded-full bg-logo-green/10 blur-3xl animate-blob" style="animation-delay:-6s"></div>
  </div>

  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="relative text-center max-w-2xl mx-auto reveal">
      <span class="text-sm font-bold uppercase tracking-widest text-accent">Perjalanan Kami</span>
      <h2 class="mt-3 text-3xl sm:text-4xl font-extrabold text-primary">Timeline Sejarah Perusahaan</h2>
      <p class="mt-4 text-slate-600">Setiap tonggak perjalanan Hasnur Group dari tahun ke tahun, 1966–2023.</p>
    </div>

    <!-- ===== Desktop/tablet: serpentine grid (hidden below sm) ===== -->
    <div class="hidden lg:block mt-20 px-12 space-y-16">

      <!-- Row 1: 1966 → 1982 → 1988 → 1992 → 2000 (left to right) -->
      <div class="relative z-0 grid grid-cols-5 gap-6">
        <div class="tl-line origin-left absolute top-16 left-[10%] right-[10%] h-4 rounded-l-full bg-accent -z-10" style="transition-delay:0ms"></div>
        <div class="tl-turn absolute top-16 -right-12 w-[calc(10%_+_3rem)] h-[388px] -z-10">
          <div class="w-full h-full border-[16px] border-l-0 border-accent" style="border-radius:0 100% 100% 0 / 0 194px 194px 0;transition-delay:340ms"></div>
        </div>

        <div class="tl-node flex flex-col items-center text-center" style="transition-delay:0ms">
          <div class="tl-dot relative w-36 h-36 rounded-full ring-[6px] ring-white bg-gradient-to-br from-primary-light to-primary shadow-[0_14px_30px_-10px_rgba(76,88,196,0.6)] flex items-center justify-center">
            <span class="text-white text-3xl font-extrabold tracking-tight">1966</span>
          </div>
          <p class="mt-5 text-sm text-slate-600 leading-relaxed max-w-[12rem] min-h-[9rem]">Mulai usaha angkutan sungai.</p>
        </div>

        <div class="tl-node flex flex-col items-center text-center" style="transition-delay:80ms">
          <div class="tl-dot relative w-36 h-36 rounded-full ring-[6px] ring-white bg-gradient-to-br from-primary-light to-primary shadow-[0_14px_30px_-10px_rgba(76,88,196,0.6)] flex items-center justify-center">
            <span class="text-white text-3xl font-extrabold tracking-tight">1982</span>
          </div>
          <p class="mt-5 text-sm text-slate-600 leading-relaxed max-w-[12rem] min-h-[9rem]">Mulai usaha kehutanan.</p>
        </div>

        <div class="tl-node flex flex-col items-center text-center" style="transition-delay:160ms">
          <div class="tl-dot relative w-36 h-36 rounded-full ring-[6px] ring-white bg-gradient-to-br from-primary-light to-primary shadow-[0_14px_30px_-10px_rgba(76,88,196,0.6)] flex items-center justify-center">
            <span class="text-white text-3xl font-extrabold tracking-tight">1988</span>
          </div>
          <p class="mt-5 text-sm text-slate-600 leading-relaxed max-w-[12rem] min-h-[9rem]">Pendirian klub sepak bola PS Barito Putera.</p>
        </div>

        <div class="tl-node flex flex-col items-center text-center" style="transition-delay:240ms">
          <div class="tl-dot relative w-36 h-36 rounded-full ring-[6px] ring-white bg-gradient-to-br from-primary-light to-primary shadow-[0_14px_30px_-10px_rgba(76,88,196,0.6)] flex items-center justify-center">
            <span class="text-white text-3xl font-extrabold tracking-tight">1992</span>
          </div>
          <p class="mt-5 text-sm text-slate-600 leading-relaxed max-w-[12rem] min-h-[9rem]">Mulai usaha pertambangan dan perkebunan kelapa sawit.</p>
        </div>

        <div class="tl-node flex flex-col items-center text-center" style="transition-delay:320ms">
          <div class="tl-dot relative w-36 h-36 rounded-full ring-[6px] ring-white bg-gradient-to-br from-primary-light to-primary shadow-[0_14px_30px_-10px_rgba(76,88,196,0.6)] flex items-center justify-center">
            <span class="text-white text-3xl font-extrabold tracking-tight">2000</span>
          </div>
          <p class="mt-5 text-sm text-slate-600 leading-relaxed max-w-[12rem] min-h-[9rem]">Memiliki jalan hauling dan pelabuhan batubara di Tapin, Kalsel.</p>
        </div>
      </div>

      <!-- Row 2: 2006 → 2008 → 2009 → 2010 → 2011, visually right to left
           (order reversed, DOM/reading + delay order stays chronological) -->
      <div class="relative z-0 grid grid-cols-5 gap-6">
        <div class="tl-line origin-right absolute top-16 left-[10%] right-[10%] h-4 bg-accent -z-10" style="transition-delay:400ms"></div>
        <div class="tl-turn absolute top-16 -left-12 w-[calc(10%_+_3rem)] h-[388px] -z-10">
          <div class="w-full h-full border-[16px] border-r-0 border-accent" style="border-radius:100% 0 0 100% / 194px 0 0 194px;transition-delay:740ms"></div>
        </div>

        <div class="tl-node order-5 flex flex-col items-center text-center" style="transition-delay:400ms">
          <div class="tl-dot relative w-36 h-36 rounded-full ring-[6px] ring-white bg-gradient-to-br from-primary to-logo-blue shadow-[0_14px_30px_-10px_rgba(47,59,162,0.6)] flex items-center justify-center">
            <span class="text-white text-3xl font-extrabold tracking-tight">2006</span>
          </div>
          <p class="mt-5 text-sm text-slate-600 leading-relaxed max-w-[12rem] min-h-[9rem]">Ekspansi ke bidang jasa/services.</p>
        </div>

        <div class="tl-node order-4 flex flex-col items-center text-center" style="transition-delay:480ms">
          <div class="tl-dot relative w-36 h-36 rounded-full ring-[6px] ring-white bg-gradient-to-br from-primary to-logo-blue shadow-[0_14px_30px_-10px_rgba(47,59,162,0.6)] flex items-center justify-center">
            <span class="text-white text-3xl font-extrabold tracking-tight">2008</span>
          </div>
          <p class="mt-5 text-sm text-slate-600 leading-relaxed max-w-[12rem] min-h-[9rem]">Mulai usaha di bidang media.</p>
        </div>

        <div class="tl-node order-3 flex flex-col items-center text-center" style="transition-delay:560ms">
          <div class="tl-dot relative w-36 h-36 rounded-full ring-[6px] ring-white bg-gradient-to-br from-primary to-logo-blue shadow-[0_14px_30px_-10px_rgba(47,59,162,0.6)] flex items-center justify-center">
            <span class="text-white text-3xl font-extrabold tracking-tight">2009</span>
          </div>
          <p class="mt-5 text-sm text-slate-600 leading-relaxed max-w-[12rem] min-h-[9rem]">Ekspansi perkebunan sawit dan pengangkutan batubara via laut.</p>
        </div>

        <div class="tl-node order-2 flex flex-col items-center text-center" style="transition-delay:640ms">
          <div class="tl-dot relative w-36 h-36 rounded-full ring-[6px] ring-white bg-gradient-to-br from-primary to-logo-blue shadow-[0_14px_30px_-10px_rgba(47,59,162,0.6)] flex items-center justify-center">
            <span class="text-white text-3xl font-extrabold tracking-tight">2010</span>
          </div>
          <p class="mt-5 text-sm text-slate-600 leading-relaxed max-w-[12rem] min-h-[9rem]">Pendirian Yayasan Hasnur Centre, tambahan lahan sawit di Barito Kuala.</p>
        </div>

        <div class="tl-node order-1 flex flex-col items-center text-center" style="transition-delay:720ms">
          <div class="tl-dot relative w-36 h-36 rounded-full ring-[6px] ring-white bg-gradient-to-br from-primary to-logo-blue shadow-[0_14px_30px_-10px_rgba(47,59,162,0.6)] flex items-center justify-center">
            <span class="text-white text-3xl font-extrabold tracking-tight">2011</span>
          </div>
          <p class="mt-5 text-sm text-slate-600 leading-relaxed max-w-[12rem] min-h-[9rem]">Ekspansi ke bidang teknologi informasi.</p>
        </div>
      </div>

      <!-- Row 3: 2013 → 2015 → 2020 → 2021 → 2023 (left to right, final row) -->
      <div class="relative z-0 grid grid-cols-5 gap-6">
        <div class="tl-line origin-left absolute top-16 left-[10%] right-[10%] h-4 rounded-r-full bg-accent -z-10" style="transition-delay:800ms"></div>

        <div class="tl-node flex flex-col items-center text-center" style="transition-delay:800ms">
          <div class="tl-dot relative w-36 h-36 rounded-full ring-[6px] ring-white bg-gradient-to-br from-logo-blue to-logo-blue-dark shadow-[0_14px_30px_-10px_rgba(21,79,145,0.6)] flex items-center justify-center">
            <span class="text-white text-3xl font-extrabold tracking-tight">2013</span>
          </div>
          <p class="mt-5 text-sm text-slate-600 leading-relaxed max-w-[12rem] min-h-[9rem]">Peresmian pabrik kelapa sawit di Tapin dan pelabuhan batubara di Barito Selatan.</p>
        </div>

        <div class="tl-node flex flex-col items-center text-center" style="transition-delay:880ms">
          <div class="tl-dot relative w-36 h-36 rounded-full ring-[6px] ring-white bg-gradient-to-br from-logo-blue to-logo-blue-dark shadow-[0_14px_30px_-10px_rgba(21,79,145,0.6)] flex items-center justify-center">
            <span class="text-white text-3xl font-extrabold tracking-tight">2015</span>
          </div>
          <p class="mt-5 text-sm text-slate-600 leading-relaxed max-w-[12rem] min-h-[9rem]">Ekspansi ke jasa konstruksi.</p>
        </div>

        <div class="tl-node flex flex-col items-center text-center" style="transition-delay:960ms">
          <div class="tl-dot relative w-36 h-36 rounded-full ring-[6px] ring-white bg-gradient-to-br from-logo-blue to-logo-blue-dark shadow-[0_14px_30px_-10px_rgba(21,79,145,0.6)] flex items-center justify-center">
            <span class="text-white text-3xl font-extrabold tracking-tight">2020</span>
          </div>
          <p class="mt-5 text-sm text-slate-600 leading-relaxed max-w-[12rem] min-h-[9rem]">Penguatan bisnis logistik dan energi terbarukan.</p>
        </div>

        <div class="tl-node flex flex-col items-center text-center" style="transition-delay:1040ms">
          <div class="tl-dot relative w-36 h-36 rounded-full ring-[6px] ring-white bg-gradient-to-br from-logo-blue to-logo-blue-dark shadow-[0_14px_30px_-10px_rgba(21,79,145,0.6)] flex items-center justify-center">
            <span class="text-white text-3xl font-extrabold tracking-tight">2021</span>
          </div>
          <p class="mt-5 text-sm text-slate-600 leading-relaxed max-w-[12rem] min-h-[9rem]">Anak perusahaan PT Hasnur International Shipping IPO di BEI (kode saham: HAIS), 1 September 2021.</p>
        </div>

        <div class="tl-node flex flex-col items-center text-center" style="transition-delay:1120ms">
          <div class="tl-dot tl-ping relative w-36 h-36 rounded-full ring-[6px] ring-accent bg-gradient-to-br from-accent to-[#bd8526] shadow-[0_14px_34px_-8px_rgba(217,164,65,0.7)] flex items-center justify-center">
            <span class="text-logo-blue text-3xl font-extrabold tracking-tight">2023</span>
          </div>
          <p class="mt-5 text-sm text-slate-600 leading-relaxed max-w-[12rem] min-h-[9rem]">Restrukturisasi menjadi 7 Strategic Business Unit (SBU): Logistic, Agro Forestry, Energy, Technology &amp; Services, Education, Consumer, Investment.</p>
        </div>
      </div>

    </div>

    <!-- ===== Mobile: simple vertical list (sm and up hides this) ===== -->
    <div class="lg:hidden relative mt-14">
      <div class="absolute left-12 top-2 bottom-2 w-[6px] -translate-x-1/2 rounded-full bg-accent/70"></div>
      <div class="space-y-8">

        <div class="tl-node relative flex gap-4 items-center" style="transition-delay:0ms">
          <div class="tl-dot relative z-10 shrink-0 w-24 h-24 rounded-full ring-[6px] ring-white bg-gradient-to-br from-primary-light to-primary shadow-[0_10px_22px_-8px_rgba(76,88,196,0.6)] flex items-center justify-center">
          <span class="text-white text-xl font-extrabold">1966</span>
        </div>
          <div>
            <p class="text-sm text-slate-600 leading-relaxed">Mulai usaha angkutan sungai.</p>
          </div>
        </div>

        <div class="tl-node relative flex gap-4 items-center" style="transition-delay:70ms">
          <div class="tl-dot relative z-10 shrink-0 w-24 h-24 rounded-full ring-[6px] ring-white bg-gradient-to-br from-primary-light to-primary shadow-[0_10px_22px_-8px_rgba(76,88,196,0.6)] flex items-center justify-center">
          <span class="text-white text-xl font-extrabold">1982</span>
        </div>
          <div>
            <p class="text-sm text-slate-600 leading-relaxed">Mulai usaha kehutanan.</p>
          </div>
        </div>

        <div class="tl-node relative flex gap-4 items-center" style="transition-delay:140ms">
          <div class="tl-dot relative z-10 shrink-0 w-24 h-24 rounded-full ring-[6px] ring-white bg-gradient-to-br from-primary-light to-primary shadow-[0_10px_22px_-8px_rgba(76,88,196,0.6)] flex items-center justify-center">
          <span class="text-white text-xl font-extrabold">1988</span>
        </div>
          <div>
            <p class="text-sm text-slate-600 leading-relaxed">Pendirian klub sepak bola PS Barito Putera.</p>
          </div>
        </div>

        <div class="tl-node relative flex gap-4 items-center" style="transition-delay:210ms">
          <div class="tl-dot relative z-10 shrink-0 w-24 h-24 rounded-full ring-[6px] ring-white bg-gradient-to-br from-primary-light to-primary shadow-[0_10px_22px_-8px_rgba(76,88,196,0.6)] flex items-center justify-center">
          <span class="text-white text-xl font-extrabold">1992</span>
        </div>
          <div>
            <p class="text-sm text-slate-600 leading-relaxed">Mulai usaha pertambangan dan perkebunan kelapa sawit.</p>
          </div>
        </div>

        <div class="tl-node relative flex gap-4 items-center" style="transition-delay:0ms">
          <div class="tl-dot relative z-10 shrink-0 w-24 h-24 rounded-full ring-[6px] ring-white bg-gradient-to-br from-primary-light to-primary shadow-[0_10px_22px_-8px_rgba(76,88,196,0.6)] flex items-center justify-center">
          <span class="text-white text-xl font-extrabold">2000</span>
        </div>
          <div>
            <p class="text-sm text-slate-600 leading-relaxed">Memiliki jalan hauling dan pelabuhan batubara di Tapin, Kalsel.</p>
          </div>
        </div>

        <div class="tl-node relative flex gap-4 items-center" style="transition-delay:70ms">
          <div class="tl-dot relative z-10 shrink-0 w-24 h-24 rounded-full ring-[6px] ring-white bg-gradient-to-br from-primary to-logo-blue shadow-[0_10px_22px_-8px_rgba(47,59,162,0.6)] flex items-center justify-center">
          <span class="text-white text-xl font-extrabold">2006</span>
        </div>
          <div>
            <p class="text-sm text-slate-600 leading-relaxed">Ekspansi ke bidang jasa/services.</p>
          </div>
        </div>

        <div class="tl-node relative flex gap-4 items-center" style="transition-delay:140ms">
          <div class="tl-dot relative z-10 shrink-0 w-24 h-24 rounded-full ring-[6px] ring-white bg-gradient-to-br from-primary to-logo-blue shadow-[0_10px_22px_-8px_rgba(47,59,162,0.6)] flex items-center justify-center">
          <span class="text-white text-xl font-extrabold">2008</span>
        </div>
          <div>
            <p class="text-sm text-slate-600 leading-relaxed">Mulai usaha di bidang media.</p>
          </div>
        </div>

        <div class="tl-node relative flex gap-4 items-center" style="transition-delay:210ms">
          <div class="tl-dot relative z-10 shrink-0 w-24 h-24 rounded-full ring-[6px] ring-white bg-gradient-to-br from-primary to-logo-blue shadow-[0_10px_22px_-8px_rgba(47,59,162,0.6)] flex items-center justify-center">
          <span class="text-white text-xl font-extrabold">2009</span>
        </div>
          <div>
            <p class="text-sm text-slate-600 leading-relaxed">Ekspansi perkebunan sawit dan pengangkutan batubara via laut.</p>
          </div>
        </div>

        <div class="tl-node relative flex gap-4 items-center" style="transition-delay:0ms">
          <div class="tl-dot relative z-10 shrink-0 w-24 h-24 rounded-full ring-[6px] ring-white bg-gradient-to-br from-primary to-logo-blue shadow-[0_10px_22px_-8px_rgba(47,59,162,0.6)] flex items-center justify-center">
          <span class="text-white text-xl font-extrabold">2010</span>
        </div>
          <div>
            <p class="text-sm text-slate-600 leading-relaxed">Pendirian Yayasan Hasnur Centre, tambahan lahan sawit di Barito Kuala.</p>
          </div>
        </div>

        <div class="tl-node relative flex gap-4 items-center" style="transition-delay:70ms">
          <div class="tl-dot relative z-10 shrink-0 w-24 h-24 rounded-full ring-[6px] ring-white bg-gradient-to-br from-primary to-logo-blue shadow-[0_10px_22px_-8px_rgba(47,59,162,0.6)] flex items-center justify-center">
          <span class="text-white text-xl font-extrabold">2011</span>
        </div>
          <div>
            <p class="text-sm text-slate-600 leading-relaxed">Ekspansi ke bidang teknologi informasi.</p>
          </div>
        </div>

        <div class="tl-node relative flex gap-4 items-center" style="transition-delay:140ms">
          <div class="tl-dot relative z-10 shrink-0 w-24 h-24 rounded-full ring-[6px] ring-white bg-gradient-to-br from-logo-blue to-logo-blue-dark shadow-[0_10px_22px_-8px_rgba(21,79,145,0.6)] flex items-center justify-center">
          <span class="text-white text-xl font-extrabold">2013</span>
        </div>
          <div>
            <p class="text-sm text-slate-600 leading-relaxed">Peresmian pabrik kelapa sawit di Tapin dan pelabuhan batubara di Barito Selatan.</p>
          </div>
        </div>

        <div class="tl-node relative flex gap-4 items-center" style="transition-delay:210ms">
          <div class="tl-dot relative z-10 shrink-0 w-24 h-24 rounded-full ring-[6px] ring-white bg-gradient-to-br from-logo-blue to-logo-blue-dark shadow-[0_10px_22px_-8px_rgba(21,79,145,0.6)] flex items-center justify-center">
          <span class="text-white text-xl font-extrabold">2015</span>
        </div>
          <div>
            <p class="text-sm text-slate-600 leading-relaxed">Ekspansi ke jasa konstruksi.</p>
          </div>
        </div>

        <div class="tl-node relative flex gap-4 items-center" style="transition-delay:0ms">
          <div class="tl-dot relative z-10 shrink-0 w-24 h-24 rounded-full ring-[6px] ring-white bg-gradient-to-br from-logo-blue to-logo-blue-dark shadow-[0_10px_22px_-8px_rgba(21,79,145,0.6)] flex items-center justify-center">
          <span class="text-white text-xl font-extrabold">2020</span>
        </div>
          <div>
            <p class="text-sm text-slate-600 leading-relaxed">Penguatan bisnis logistik dan energi terbarukan.</p>
          </div>
        </div>

        <div class="tl-node relative flex gap-4 items-center" style="transition-delay:70ms">
          <div class="tl-dot relative z-10 shrink-0 w-24 h-24 rounded-full ring-[6px] ring-white bg-gradient-to-br from-logo-blue to-logo-blue-dark shadow-[0_10px_22px_-8px_rgba(21,79,145,0.6)] flex items-center justify-center">
          <span class="text-white text-xl font-extrabold">2021</span>
        </div>
          <div>
            <p class="text-sm text-slate-600 leading-relaxed">Anak perusahaan PT Hasnur International Shipping IPO di BEI (kode saham: HAIS), 1 September 2021.</p>
          </div>
        </div>

        <div class="tl-node relative flex gap-4 items-center" style="transition-delay:140ms">
          <div class="tl-dot tl-ping relative z-10 shrink-0 w-24 h-24 rounded-full ring-[6px] ring-accent bg-gradient-to-br from-accent to-[#bd8526] shadow-[0_10px_26px_-6px_rgba(217,164,65,0.7)] flex items-center justify-center">
          <span class="text-logo-blue text-xl font-extrabold">2023</span>
        </div>
          <div>
            <p class="text-sm text-slate-600 leading-relaxed">Restrukturisasi menjadi 7 Strategic Business Unit (SBU): Logistic, Agro Forestry, Energy, Technology &amp; Services, Education, Consumer, Investment.</p>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>

<!-- ============ BISNIS KAMI (7 SBU) ============ -->
<!-- Each card carries its own SBU colour, sampled from that unit's own icon
     disc in assets/img/bisnis/. The photo fills the card and a tinted curve,
     the same colour as the card. Is painted over its left side, which is what
     gives the sweep without needing a per-card clipped image. -->
<section id="bisnis-kami" class="relative py-20 sm:py-28 overflow-hidden">

  <div class="absolute -top-24 -left-16" data-parallax="0.12">
    <div class="w-80 h-80 rounded-full bg-logo-blue/15 blur-3xl animate-blob" style="animation-delay:-2s"></div>
  </div>
  <div class="absolute -bottom-24 -right-16" data-parallax="0.07">
    <div class="w-64 h-64 rounded-full bg-logo-yellow/10 blur-3xl animate-blob" style="animation-delay:-5s"></div>
  </div>
  <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="reveal mx-auto max-w-2xl text-center">
      <span class="text-sm font-bold uppercase tracking-widest text-accent">7 Strategic Business Unit</span>
      <h2 class="mt-3 text-3xl font-extrabold text-primary sm:text-4xl">Bisnis Kami</h2>
    </div>

    <!-- 4 cards then 3, as in the reference, once there is room for it (xl);
         two-up below that, so the text column never gets squeezed. -->
    <div class="mt-14 grid gap-6 sm:grid-cols-2 xl:grid-cols-12">

      @foreach ($businessUnits as $unit)
      <a href="{{ $unit->url }}"
         class="reveal group relative flex min-h-[21rem] flex-col overflow-hidden rounded-3xl shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl xl:col-span-{{ $unit->columnSpan($loop->index) }}"
         style="background:{{ $unit->tint_color }};transition-delay:{{ $loop->index * 70 }}ms">
        <x-img :src="$unit->image_url" :alt="$unit->image_alt" loading="lazy"
             class="absolute inset-0 h-full w-full object-cover brightness-110 transition-transform duration-500 group-hover:scale-105" sizes="(min-width: 1280px) 430px, (min-width: 640px) 50vw, 100vw" />
        <!-- Tinted sweep painted over the photo: same colour as the card, so the
             photo reads as if it were cut by the curve. Held at 80% so the
             photo stays faintly visible through it. Which is why the caption
             below runs slate-700 rather than slate-600: over the darkest
             photo (Teknologi & Jasa) slate-600 measured 4.38:1, under the
             4.5:1 AA floor. Scales with the card. -->
        <svg class="absolute inset-0 h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
          <path d="M0,0 L70,0 C64,30 74,62 54,100 L0,100 Z" fill-opacity="0.8" fill="{{ $unit->tint_color }}"/>
        </svg>

        <div class="relative flex flex-1 flex-col p-6 pr-[42%] sm:p-7 sm:pr-[42%]">
          <span class="h-14 w-14 shrink-0 overflow-hidden rounded-full">
            <x-img :src="$unit->icon_url" alt="" aria-hidden="true" loading="lazy"
                 class="h-full w-full object-cover object-top" sizes="56px" />
          </span>
          <h3 class="mt-5 text-lg font-extrabold leading-snug text-slate-900">{{ $unit->name }}</h3>
          <p class="mt-3 text-sm leading-relaxed text-slate-700">{{ $unit->short_description }}</p>
          <span class="mt-auto pt-6">
            <span class="flex h-10 w-10 items-center justify-center rounded-full text-white shadow-sm"
                  style="background:{{ $unit->accent_color }}"><svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0l-6-6m6 6l-6 6"/></svg></span>
          </span>
        </div>
      </a>
    @endforeach

    </div>
  </div>
</section>

<!-- ============ AKTIVITAS KAMI ============ -->
<section class="relative py-20 sm:py-28 bg-slate-50 overflow-hidden">

  <div class="absolute -top-24 -right-16" data-parallax="0.12">
    <div class="w-80 h-80 rounded-full bg-logo-red/15 blur-3xl animate-blob" style="animation-delay:-4s"></div>
  </div>
  <div class="absolute -bottom-24 -left-16" data-parallax="0.07">
    <div class="w-64 h-64 rounded-full bg-logo-blue/10 blur-3xl animate-blob" style="animation-delay:-1s"></div>
  </div>
  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-end justify-between gap-4 reveal">
      <div>
        <span class="text-sm font-bold uppercase tracking-widest text-accent">Publikasi</span>
        <h2 class="mt-3 text-3xl sm:text-4xl font-extrabold text-primary">Aktivitas Kami</h2>
      </div>
      <a href="/page/publication/latest-update" data-cta="beranda-lihat-semua-aktivitas" class="font-bold text-primary hover:text-primary-dark transition inline-flex items-center gap-2">
        Lihat Semua
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
      </a>
    </div>

    <div class="mt-12 grid gap-8 md:grid-cols-3">
      @forelse ($latestPosts as $post)
        <article class="reveal group overflow-hidden rounded-2xl bg-white shadow-sm transition hover:shadow-xl">
          <a href="{{ $post->url }}">
            @if ($post->cover_image)
              <div class="aspect-video overflow-hidden">
                <x-img :src="$post->cover_url" :alt="$post->title" loading="lazy"
                       sizes="(min-width: 768px) 400px, 100vw"
                       class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
              </div>
            @endif
            <div class="p-6">
              <span class="inline-block rounded-full bg-primary/10 px-3 py-1 text-xs font-bold uppercase tracking-wide text-primary">
                {{ str_replace('-', ' ', $post->category) }}
              </span>
              <h3 class="mt-4 text-lg font-bold leading-snug text-slate-800 transition group-hover:text-primary">
                {{ $post->title }}
              </h3>
              <p class="mt-2 line-clamp-3 text-sm text-slate-500">{{ $post->excerpt }}</p>
              @if ($post->published_at)
                <p class="mt-4 text-xs font-semibold text-slate-400">{{ $post->published_at->translatedFormat('d F Y') }}</p>
              @endif
            </div>
          </a>
        </article>
      @empty
        <p class="text-slate-500">Belum ada publikasi. Jalankan <code>php artisan import:articles</code>.</p>
      @endforelse
    </div>
  </div>
</section>
@endsection
