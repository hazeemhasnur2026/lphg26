@extends('layouts.app')

@section('title', 'Kebijakan Privasi')
@section('description', 'Penjelasan data pribadi yang dikumpulkan situs Hasnur Group, tujuan pemrosesan, masa simpan, dan cara mengajukan hak sebagai subjek data.')

@section('content')
<x-page-hero
    :breadcrumb="[
        ['label' => 'Beranda', 'url' => '/'],
        ['label' => 'Kebijakan Privasi', 'url' => null],
    ]"
    title="Kebijakan Privasi"
    description="Apa yang dikumpulkan situs ini, untuk apa, berapa lama disimpan, dan bagaimana Anda dapat menggunakan hak Anda." />

<section class="py-16 sm:py-20">
  <div class="prose-hasnur mx-auto max-w-3xl space-y-6 px-4 leading-relaxed text-slate-700 sm:px-6 lg:px-8">

    {{-- Tiga hal di bawah adalah keputusan perusahaan, bukan keputusan teknis,
         dan belum bisa diisi dari kode. Kotak ini harus hilang sebelum situs
         diakses publik. --}}
    <div role="note" class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-sm">
      <p class="font-bold text-amber-900">Dokumen ini belum final.</p>
      <p class="mt-2 text-amber-800">
        Bagian yang ditandai <strong>[perlu keputusan]</strong> menunggu konfirmasi dari
        manajemen dan penasihat hukum: dasar pemrosesan data, masa simpan, dan
        penanggung jawab pelindungan data. Keterangan teknis di luar bagian itu sudah
        sesuai dengan cara sistem bekerja saat ini.
      </p>
    </div>

    <h2>Siapa yang mengelola data Anda</h2>
    <p>
      Situs ini dikelola PT Hasnur Group, beralamat di Office 8 Building Lantai 7,
      Jl. Senopati No. 8B, Jakarta Selatan 12190. <strong>[perlu keputusan]</strong>
      Nama dan kontak penanggung jawab pelindungan data akan dicantumkan di sini.
    </p>

    <h2>Data yang kami kumpulkan</h2>
    <p>Situs ini hanya mengumpulkan data melalui satu tempat, yaitu formulir Hubungi Kami.</p>
    <ul>
      <li><strong>Yang Anda isi sendiri:</strong> nama, alamat surel, nomor telepon bila diisi, perihal, dan isi pesan.</li>
      <li><strong>Yang tercatat otomatis:</strong> alamat IP pengirim dan bahasa halaman yang sedang dibuka saat mengirim.</li>
    </ul>
    <p>
      Alamat IP dicatat untuk membatasi pengiriman berulang dan menelusuri penyalahgunaan
      formulir. Di luar formulir tersebut, menjelajahi situs ini tidak mengumpulkan data
      pribadi apa pun.
    </p>

    <h2>Untuk apa data itu dipakai</h2>
    <p>
      Menjawab pertanyaan, permintaan informasi, atau usulan kerja sama yang Anda kirim.
      Data dari formulir tidak dipakai untuk mengirim materi promosi dan tidak
      diperjualbelikan.
    </p>
    <p><strong>[perlu keputusan]</strong> Dasar pemrosesan menurut Pasal 20 UU No. 27 Tahun 2022 akan dicantumkan di sini.</p>

    <h2>Siapa yang dapat melihatnya</h2>
    <p>
      Pesan Anda tersimpan di basis data situs dan diteruskan sebagai surel ke satu
      alamat resmi Hasnur Group. Yang dapat membacanya hanya pengelola situs yang
      memiliki akun panel administrasi. Data tidak dibagikan ke pihak ketiga mana pun.
    </p>
    <p>
      Situs ini tidak memasang layanan analitik pihak ketiga, piksel pelacak, peta
      tertanam, maupun skrip dari domain lain. Seluruh gambar, huruf, dan skrip
      dilayani dari domain Hasnur Group sendiri, sehingga kunjungan Anda tidak
      dilaporkan ke penyedia lain.
    </p>

    <h2>Pengukuran penggunaan halaman</h2>
    <p>
      Untuk mengetahui bagian situs mana yang dibutuhkan pengunjung, situs ini mencatat
      sendiri empat hal: tombol ajakan yang diklik, berkas yang diunduh, kata kunci
      yang diketik pada halaman pencarian, dan artikel yang dibaca sampai bagian
      bawahnya. Yang tersimpan pada setiap catatan hanya jenis peristiwanya, nama
      tombol atau berkas atau kata kunci tersebut, halaman tempatnya terjadi, dan
      bahasa halaman.
    </p>
    <p>
      Alamat IP dan jenis peramban Anda <strong>tidak</strong> disimpan. Yang disimpan
      hanya sidik pendek hasil pengacakan kriptografis dari keduanya bersama tanggal
      hari itu: nilainya berganti setiap tengah malam, tidak dapat dikembalikan ke
      asalnya, dan tidak dapat dihubungkan dengan catatan hari sebelumnya. Kegunaannya
      satu, yaitu membedakan sepuluh klik dari satu orang dengan sepuluh klik dari
      sepuluh orang.
    </p>
    <p>
      Catatan ini tidak memakai cookie, tidak dipakai untuk mengenali Anda, tidak
      digabungkan dengan data dari formulir Hubungi Kami, dan tidak dikirim ke pihak
      mana pun di luar server situs ini. Catatan yang berumur lebih dari dua belas
      bulan dihapus otomatis.
    </p>

    <h2>Cookie</h2>
    <p>
      Situs ini menyimpan dua cookie teknis: satu penanda sesi dan satu token keamanan
      formulir. Keduanya diperlukan agar formulir dan halaman administrasi berfungsi,
      tidak dipakai untuk mengenali Anda antar kunjungan, dan tidak ada cookie iklan.
    </p>

    <h2>Berapa lama disimpan</h2>
    <p><strong>[perlu keputusan]</strong> Masa simpan pesan dari formulir akan dicantumkan di sini, beserta kapan penghapusan otomatis dijalankan.</p>

    <h2>Hak Anda</h2>
    <p>
      Berdasarkan UU No. 27 Tahun 2022 Anda berhak meminta akses atas data Anda,
      meminta koreksi bila keliru, meminta penghapusan, menarik persetujuan bila
      persetujuan menjadi dasar pemrosesan, dan mengajukan keberatan atas pemrosesan.
    </p>
    <p>
      Permintaan dapat diajukan melalui
      <a href="/page/contact/contact-us">formulir Hubungi Kami</a> dengan mencantumkan
      perihal "Permintaan Data Pribadi". Kami akan menanggapi melalui alamat surel yang
      Anda cantumkan.
    </p>

    <h2>Keamanan</h2>
    <p>
      Akses ke panel administrasi memerlukan akun dan kata sandi, dan kata sandi
      disimpan dalam bentuk teracak satu arah. Pengiriman formulir dibatasi lima kali
      per sepuluh menit dari satu alamat IP.
    </p>

    <h2>Perubahan kebijakan</h2>
    <p>
      Bila kebijakan ini berubah, versi terbarunya diterbitkan di halaman ini. Tanggal
      perubahan terakhir dicantumkan di bawah.
    </p>

    <p class="text-sm text-slate-500">Terakhir diperbarui: 24 September 2026.</p>
  </div>
</section>
@endsection
