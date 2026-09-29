{{--
  Galeri foto untuk tipe Galeri.

  Alt text diambil dari pustaka media, bukan ditulis ulang di sini. Itu alasan
  pustakanya ada: satu foto yang muncul di tiga galeri punya satu keterangan
  yang ditulis sekali, dan memperbaikinya di satu tempat memperbaiki ketiganya.

  Lightbox-nya Alpine, yang memang sudah dimuat situs ini. Tanpa itu, membuka
  foto berarti meninggalkan halaman menuju berkas gambar telanjang — pembaca
  kehilangan keterangan fotonya sekaligus jalan kembali.
--}}
<div x-data="{ buka: false, indeks: 0,
               foto: [
                 @foreach ($post->media as $berkas)
                 { src: @js($berkas->url), alt: @js($berkas->alt_text ?? ''), teks: @js($berkas->caption ?? $berkas->title ?? '') },
                 @endforeach
               ],
               tampilkan(i) { this.indeks = i; this.buka = true },
               maju(n) { this.indeks = (this.indeks + n + this.foto.length) % this.foto.length } }"
     @keydown.escape.window="buka = false"
     @keydown.arrow-right.window="buka && maju(1)"
     @keydown.arrow-left.window="buka && maju(-1)"
     class="mt-2">

  <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
    @foreach ($post->media as $i => $berkas)
      <button type="button" @click="tampilkan({{ $i }})"
              class="group relative aspect-square overflow-hidden rounded-xl bg-slate-100 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
        <x-img :src="$berkas->url" :alt="$berkas->alt_text ?? ''" loading="lazy"
               sizes="(min-width: 640px) 240px, 45vw"
               class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
        @if ($berkas->caption || $berkas->title)
          <span class="pointer-events-none absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-2 text-left text-[11px] leading-tight text-white opacity-0 transition group-hover:opacity-100">
            {{ $berkas->caption ?: $berkas->title }}
          </span>
        @endif
      </button>
    @endforeach
  </div>

  <p class="mt-3 text-xs text-slate-400">{{ $post->media->count() }} foto</p>

  {{-- Lightbox --}}
  <div x-show="buka" x-cloak x-transition.opacity
       class="fixed inset-0 z-[60] flex items-center justify-center bg-black/90 p-4"
       role="dialog" aria-modal="true" aria-label="Pratinjau foto">

    <button type="button" @click="buka = false" aria-label="Tutup"
            class="absolute right-4 top-4 rounded-full bg-white/10 p-2 text-white hover:bg-white/20">
      <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>

    <template x-if="foto.length > 1">
      <button type="button" @click="maju(-1)" aria-label="Foto sebelumnya"
              class="absolute left-4 rounded-full bg-white/10 p-3 text-white hover:bg-white/20">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
        </svg>
      </button>
    </template>

    <figure class="max-h-full max-w-4xl text-center">
      <img :src="foto[indeks]?.src" :alt="foto[indeks]?.alt"
           class="mx-auto max-h-[78vh] w-auto rounded-lg object-contain">
      <figcaption x-show="foto[indeks]?.teks" x-text="foto[indeks]?.teks"
                  class="mt-3 text-sm text-white/70"></figcaption>
    </figure>

    <template x-if="foto.length > 1">
      <button type="button" @click="maju(1)" aria-label="Foto berikutnya"
              class="absolute right-4 rounded-full bg-white/10 p-3 text-white hover:bg-white/20">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
        </svg>
      </button>
    </template>
  </div>
</div>
