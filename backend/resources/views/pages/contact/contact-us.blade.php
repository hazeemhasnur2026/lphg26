@extends('layouts.app')

@section('title', 'Hubungi Kami')
@section('description', 'Kirim pertanyaan, usulan kerja sama, atau permintaan informasi kepada Hasnur Group melalui formulir ini.')

@section('content')
<x-page-hero
    :breadcrumb="[
        ['label' => 'Beranda', 'url' => '/'],
        ['label' => 'Hubungi Kami', 'url' => null],
    ]"
    title="Hubungi Kami"
    description="Sampaikan pertanyaan, usulan kerja sama, atau permintaan informasi melalui formulir di bawah ini." />

<section class="py-16 sm:py-20">
  <div class="mx-auto grid max-w-6xl gap-12 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">

    <div class="lg:col-span-2">
      @if (session('contact.sent'))
        {{-- Ditulis sebagai status, bukan sekadar warna hijau: pembaca layar
             mengumumkannya, dan fokus tidak perlu dipindah paksa. --}}
        <div role="status" class="reveal mb-8 rounded-2xl border border-green-200 bg-green-50 p-6">
          <p class="font-bold text-green-800">Pesan Anda sudah terkirim.</p>
          <p class="mt-1 text-sm text-green-700">Terima kasih. Tim kami akan menghubungi Anda melalui alamat surel yang dicantumkan.</p>
        </div>
      @endif

      @if ($errors->any())
        <div role="alert" class="reveal mb-8 rounded-2xl border border-red-200 bg-red-50 p-6">
          <p class="font-bold text-red-800">Pesan belum terkirim.</p>
          <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('contact.store') }}" class="reveal space-y-6">
        @csrf

        {{-- Perangkap robot. Disembunyikan dari mata sekaligus dari pembaca
             layar, dan tabindex negatif menjaganya tidak pernah terkena Tab. --}}
        <div class="hidden" aria-hidden="true">
          <label for="website">Jangan isi kolom ini</label>
          <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
          <div>
            <label for="name" class="block text-sm font-semibold text-slate-700">Nama <span class="text-red-500" aria-hidden="true">*</span></label>
            <input type="text" id="name" name="name" required autocomplete="name" maxlength="120"
                   value="{{ old('name') }}"
                   @error('name') aria-invalid="true" aria-describedby="name-error" @enderror
                   class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30">
            @error('name')<p id="name-error" class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
          </div>

          <div>
            <label for="email" class="block text-sm font-semibold text-slate-700">Alamat surel <span class="text-red-500" aria-hidden="true">*</span></label>
            <input type="email" id="email" name="email" required autocomplete="email" maxlength="255"
                   value="{{ old('email') }}"
                   @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                   class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30">
            @error('email')<p id="email-error" class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
          </div>

          <div>
            <label for="phone" class="block text-sm font-semibold text-slate-700">Nomor telepon <span class="font-normal text-slate-400">(opsional)</span></label>
            <input type="tel" id="phone" name="phone" autocomplete="tel" maxlength="40"
                   value="{{ old('phone') }}"
                   class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30">
          </div>

          <div>
            <label for="subject" class="block text-sm font-semibold text-slate-700">Perihal <span class="text-red-500" aria-hidden="true">*</span></label>
            <input type="text" id="subject" name="subject" required maxlength="160"
                   value="{{ old('subject') }}"
                   @error('subject') aria-invalid="true" aria-describedby="subject-error" @enderror
                   class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30">
            @error('subject')<p id="subject-error" class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
          </div>
        </div>

        <div>
          <label for="message" class="block text-sm font-semibold text-slate-700">Pesan <span class="text-red-500" aria-hidden="true">*</span></label>
          <textarea id="message" name="message" rows="7" required minlength="20" maxlength="5000"
                    @error('message') aria-invalid="true" aria-describedby="message-error" @enderror
                    class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30">{{ old('message') }}</textarea>
          @error('message')<p id="message-error" class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        {{-- Pemberitahuan di titik pengumpulan, bukan hanya di halaman terpisah:
             di sinilah orang benar-benar menyerahkan datanya. --}}
        <p class="text-sm leading-relaxed text-slate-500">
          Dengan mengirim pesan ini, nama, alamat surel, nomor telepon bila diisi, isi
          pesan, dan alamat IP Anda kami simpan untuk menanggapi permintaan Anda.
          Penjelasan lengkap ada di
          <a href="/page/legal/kebijakan-privasi" class="font-semibold text-primary underline">Kebijakan Privasi</a>.
        </p>

        <button type="submit" data-cta="kirim-pesan" class="rounded-full bg-primary px-8 py-3.5 text-sm font-semibold text-white transition hover:bg-primary-dark">
          Kirim Pesan
        </button>
      </form>
    </div>

    <aside class="reveal lg:pt-2">
      @if ($headOffice)
        <p class="text-sm font-bold uppercase tracking-widest text-accent">{{ $headOffice->name }}</p>
        <p class="mt-4 text-sm leading-relaxed text-slate-600">
          @foreach ($headOffice->addressLines() as $line)
            {{ $line }}@if (! $loop->last)<br>@endif
          @endforeach
        </p>
        @if ($headOffice->phone)
          <p class="mt-4 text-sm text-slate-600">Telp: {{ $headOffice->phone }}</p>
        @endif
        @if ($headOffice->fax)
          <p class="text-sm text-slate-600">Faks: {{ $headOffice->fax }}</p>
        @endif
      @endif

      {{-- Alamat surelnya sengaja tidak ditulis sebagai tautan mailto di mana
           pun: alamat yang terpampang di HTML adalah yang pertama dipanen
           pengirim spam, dan lewat formulir pesannya sekaligus tersimpan. --}}
      <p class="mt-6 rounded-2xl bg-slate-50 p-5 text-sm leading-relaxed text-slate-500">
        Semua pesan dari formulir ini diteruskan ke kotak masuk resmi Hasnur Group dan dijawab melalui surel.
      </p>
    </aside>
  </div>
</section>
@endsection
