{{--
  Penyemat video untuk tipe Video.

  Tiga kemungkinan, dan yang ketiga yang paling sering dilupakan: tautan dari
  penyedia yang tidak dikenali. Menanamnya tetap sebagai iframe hanya
  menghasilkan kotak kosong tanpa galat; menawarkan tautan biasa setidaknya
  membawa pembaca ke videonya.
--}}
@php($tanam = $post->video_embed_url)

@if ($tanam)
  <div class="mt-2 overflow-hidden rounded-2xl bg-slate-900 shadow-lg">
    <div class="relative w-full" style="padding-top:56.25%">
      <iframe src="{{ $tanam }}"
              title="{{ $post->title }}"
              class="absolute inset-0 h-full w-full"
              loading="lazy"
              referrerpolicy="strict-origin-when-cross-origin"
              allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowfullscreen></iframe>
    </div>
  </div>
@elseif (\App\Support\VideoEmbed::isBerkas($post->video_url))
  <div class="mt-2 overflow-hidden rounded-2xl bg-slate-900 shadow-lg">
    <video controls preload="metadata" class="h-auto w-full"
           @if ($post->cover_url) poster="{{ $post->cover_url }}" @endif>
      <source src="{{ $post->video_url }}">
      Peramban Anda tidak dapat memutar video ini.
    </video>
  </div>
@else
  <a href="{{ $post->video_url }}" target="_blank" rel="noopener" data-cta="tonton-video"
     class="mt-2 inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 font-semibold text-white transition hover:bg-primary/90">
    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
      <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/>
    </svg>
    Tonton video
  </a>
@endif
