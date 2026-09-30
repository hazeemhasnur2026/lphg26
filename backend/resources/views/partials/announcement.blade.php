@if ($announcement ?? null)
{{--
  Pengumuman yang muncul sekali per pengunjung.

  "Sekali" disimpan di localStorage peramban, bukan di sesi atau cookie. Sesi
  berakhir dan pengumumannya muncul lagi tiap hari; cookie ikut terkirim pada
  setiap permintaan termasuk gambar, dan menambah beban untuk hal yang tidak
  pernah dibaca server.

  Kuncinya memuat tanda tangan yang berubah tiap pengumuman disunting, jadi
  memperbaiki salah ketik akan memunculkannya kembali kepada orang yang sudah
  menutupnya. Itu memang yang diinginkan.

  Muncul setelah jeda sebentar, bukan seketika: dialog yang menutupi halaman
  sebelum halamannya sempat terlihat terasa seperti iklan, dan yang pertama
  dicari orang adalah tombol tutup.
--}}
<div
  x-data="{
    tampil: false,
    kunci: 'pengumuman:{{ $announcement->signature }}',
    init() {
      let sudah = false;
      try { sudah = localStorage.getItem(this.kunci) === 'tutup' } catch (e) {}
      if (sudah) return;
      setTimeout(() => { this.tampil = true; this.$nextTick(() => this.$refs.tutup?.focus()) }, 900);
    },
    tutup() {
      this.tampil = false;
      try { localStorage.setItem(this.kunci, 'tutup') } catch (e) {}
    },
  }"
  x-show="tampil"
  x-cloak
  @keydown.escape.window="tutup()"
  class="fixed inset-0 z-[80] flex items-center justify-center p-4"
  role="dialog"
  aria-modal="true"
  {{-- Dialog wajib punya nama yang bisa dibacakan. Saat judulnya tampil,
       namanya diambil dari elemen itu; saat mode poster menyembunyikannya,
       judul yang sama dipakai sebagai atribut. Tanpa salah satunya, pembaca
       layar hanya mengumumkan "dialog". --}}
  @if ($announcement->show_text) aria-labelledby="judul-pengumuman"
  @else aria-label="{{ $announcement->title }}" @endif
>
  <div x-show="tampil" x-transition.opacity @click="tutup()"
       class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

  <div x-show="tampil"
       x-transition:enter="transition ease-out duration-300"
       x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
       x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
       class="relative w-full max-w-lg">

    {{-- Tombol tutup duduk di luar kartu, bukan di atas isinya.
         
         Di dalam kartu ia menimpa apa pun yang kebetulan ada di pojok kanan
         atas — pada selebaran yang dikirim redaksi, itu biasanya logo atau
         label. Kartu ini overflow-hidden, jadi tombolnya harus berada di
         pembungkus supaya sudutnya tidak ikut terpotong. --}}
    <button type="button" x-ref="tutup" @click="tutup()" aria-label="Tutup pengumuman"
            class="absolute -right-3 -top-3 z-30 rounded-full bg-white p-2 text-slate-600 shadow-lg ring-1 ring-slate-900/10 transition hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary">
      <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>

    <div class="relative flex max-h-[90vh] w-full flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">

    {{-- Pita merek. Motif yang sama dibawa hero tiap halaman, jadi dialog ini
         terbaca sebagai bagian situs, bukan jendela asing yang menimpanya.
         
         Dilewat pada mode poster: selebaran biasanya membawa pita atau bingkai
         warnanya sendiri, dan dua pita bertumpuk terbaca sebagai kesalahan
         cetak. --}}
    @if ($announcement->show_text)
      <x-brand-strip />
    @endif

    @if ($announcement->image_url)
      {{-- Gambar tampil utuh, tidak dipotong.
           
           Sebelumnya kotaknya dikunci 16:9 dengan object-cover, dan itu selalu
           memangkas: foto potret kehilangan kepala dan kaki, foto lanskap
           kehilangan sisi kiri dan kanannya. Yang mengunggah pengumuman
           memilih gambarnya dengan alasan, dan bukan tugas dialog ini menebak
           bagian mana yang boleh hilang.
           
           Rasio gambar yang menentukan kotaknya, bukan sebaliknya. w-auto,
           bukan w-full: dengan w-full kotak <img> tetap selebar kartu walau
           gambarnya sempit, dan foto potret duduk di tengah bilah kosong yang
           lebarnya ikut terhitung sebagai bagian gambar.
           
           max-h menjaga foto potret yang sangat tinggi tidak mendorong tombol
           keluar layar. Pada kasus itu latar Navy terlihat di kiri dan kanan —
           konsekuensi yang disengaja: lebih baik ada bilah daripada ada kepala
           yang hilang. --}}
      <div class="flex shrink-0 justify-center bg-primary">
        <x-img :src="$announcement->image_url" :alt="$announcement->image_alt ?? ''"
               sizes="(min-width: 640px) 512px, 100vw"
               :dimensions="false"
               class="{{ $announcement->show_text ? 'max-h-[52vh]' : 'max-h-[82vh]' }} h-auto w-auto max-w-full object-contain" />
      </div>
    @elseif ($announcement->show_text)
      {{-- Tanpa gambar, kepala kartunya diisi pola titik yang sama dengan hero
           halaman dalam, supaya pengumuman teks saja tidak tampil kosong. --}}
      <div class="relative h-20 w-full shrink-0 overflow-hidden bg-primary">
        <div class="absolute inset-0 opacity-20 hero-pattern"></div>
      </div>
    @endif

    {{-- Key visual sudut, aset yang sama dengan sudut beranda. Diputar dan
         dipojokkan, di bawah teks, tidak bisa diklik, dan disembunyikan dari
         pembaca layar: ia hiasan, bukan isi. --}}
    <img src="/assets/img/keyvisual-corner.png" alt="" aria-hidden="true" draggable="false"
         class="pointer-events-none absolute -bottom-6 -right-10 z-0 w-40 rotate-180 select-none opacity-[0.07]">

    @php($adaTombol = $announcement->cta_label && $announcement->cta_url)

    {{-- Pada mode poster tanpa tombol, blok ini tidak dirender sama sekali:
         kartunya menjadi gambar utuh dengan tombol tutup mengambang, dan tidak
         ada pita putih kosong di kakinya. --}}
    @if ($announcement->show_text || $adaTombol)
    <div class="relative z-10 overflow-y-auto {{ $announcement->show_text ? 'p-6 sm:p-8' : 'p-4' }}">
      @if ($announcement->show_text)
        <h2 id="judul-pengumuman" class="text-xl font-extrabold leading-snug text-primary sm:text-2xl">
          {{ $announcement->title }}
        </h2>

        @if (filled($announcement->body))
          <div class="prose-hasnur mt-3 space-y-3 text-sm leading-relaxed text-slate-600">
            {!! $announcement->body_html !!}
          </div>
        @endif
      @endif

      <div class="flex flex-wrap gap-3 {{ $announcement->show_text ? 'mt-6' : '' }}">
        @if ($announcement->cta_label && $announcement->cta_url)
          <a href="{{ $announcement->cta_url }}" data-cta="pengumuman"
             @click="tutup()"
             class="rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark">
            {{ $announcement->cta_label }}
          </a>
        @endif
        @if ($announcement->show_text)
          <button type="button" @click="tutup()"
                  class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
            Tutup
          </button>
        @endif
      </div>
    </div>
    @endif
    </div>
  </div>
</div>
@endif
