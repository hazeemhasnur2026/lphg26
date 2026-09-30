{{--
  Tombol unduh untuk tipe Publikasi dan Laporan.

  Tidak memakai data-cta. Pendengar analytics sudah menangkap tautan berakhiran
  .pdf sebagai file_download; menambahkan penanda CTA di sini akan membuat satu
  klik terhitung dua kali sebagai dua peristiwa berbeda.
--}}
<a href="{{ $post->document_url }}"
   target="_blank" rel="noopener"
   class="group mt-2 flex items-center gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-5 transition hover:border-primary hover:bg-white hover:shadow-lg">

  <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary" aria-hidden="true">
    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round"
            d="M19.5 14.25v2.625a3 3 0 01-3 3h-9a3 3 0 01-3-3V14.25M12 15V3m0 12l-3.75-3.75M12 15l3.75-3.75"/>
    </svg>
  </span>

  <span class="min-w-0 flex-1">
    <span class="block font-bold text-slate-800 group-hover:text-primary">
      Unduh {{ $post->content_type->getLabel() }}
    </span>
    <span class="mt-0.5 block truncate text-sm text-slate-500">
      {{ $post->title }}
      {{-- Direktifnya berdiri sendiri, tidak menempel pada kata sebelumnya:
           Blade tidak mengenali @if yang didahului huruf, sehingga hanya
           @endif yang terkompilasi dan seluruh view gagal dirender. --}}
      <span class="text-slate-400">
        · PDF
        @if ($post->document_size) · {{ $post->document_size }} @endif
      </span>
    </span>
  </span>

  <svg class="h-5 w-5 shrink-0 text-slate-300 group-hover:text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12l-7.5 7.5M21 12H3"/>
  </svg>
</a>
