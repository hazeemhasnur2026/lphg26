{{--
  Papan analitik. Seluruh angkanya datang dari widget di bawah; yang ditulis di
  halaman ini hanya satu hal: dari mana angka itu datang.

  Bukan hiasan. Papan analitik tanpa keterangan asal akan dibaca sebagai angka
  Google Analytics oleh siapa pun yang belum membaca TOR-nya — dan angka di sini
  memang tidak mengukur hal yang sama: ia tidak mengenal kunjungan yang datang
  dari mesin pencari, tidak memisahkan pengunjung baru dari yang kembali, dan
  ikut menghitung kunjungan tim sendiri.
--}}
<x-filament-panels::page>
    @if (($contoh = $this->jumlahContoh()) > 0)
        <x-filament::section>
            <x-slot name="heading">Papan ini memuat data contoh</x-slot>

            <p class="fi-text-sm">
                {{ number_format($contoh, 0, ',', '.') }} peristiwa di bawah dibuat oleh
                perintah <code>analitik:contoh</code>, bukan oleh pengunjung. Angkanya
                bercampur dengan peristiwa sungguhan dan tidak boleh dipakai mengambil
                keputusan.
            </p>

            <p class="fi-text-sm">
                Dibuang dengan <code>php artisan analitik:contoh --hapus</code>; peristiwa
                sungguhan tidak tersentuh.
            </p>
        </x-filament::section>
    @endif

    @if (! $this->pencatatanMenyala())
        <x-filament::section>
            <x-slot name="heading">Pencatatan sedang dimatikan</x-slot>

            <p class="fi-text-sm">
                ANALYTICS_INTERNAL disetel <code>false</code> di <code>.env</code>, jadi
                peristiwa baru tidak lagi disimpan. Angka di bawah adalah yang tercatat
                sebelum itu.
            </p>
        </x-filament::section>
    @endif

    <x-filament::section collapsible collapsed>
        <x-slot name="heading">Dari mana angka ini datang</x-slot>

        <x-slot name="description">
            Dihitung oleh situs ini sendiri, bukan oleh platform analitik.
        </x-slot>

        <div class="fi-text-sm" style="display: grid; gap: 0.75rem;">
            <p>
                Tiap klik CTA, unduhan berkas, pengiriman formulir pencarian, dan
                artikel yang dibaca sampai ujung bawahnya dicatat sebagai satu baris
                di basis data situs ini. Tidak ada data yang keluar dari server ini,
                dan tidak ada skrip pihak ketiga yang ikut termuat untuk
                menghitungnya.
            </p>

            <p>
                <strong>Yang tidak bisa dijawab halaman ini:</strong> dari mana
                pengunjung datang, kata kunci apa yang membawanya dari mesin pencari,
                pengunjung baru atau yang kembali, dan perbandingan dengan situs lain.
                Itu pekerjaan platform analitik, dan
                @if ($platform = $this->platformLuar())
                    {{ $platform }} sudah terpasang untuk menjawabnya — peristiwa yang
                    sama dikirim ke sana juga.
                @else
                    platform yang disetujui belum dipilih (CMS-10 pada TOR). Begitu
                    ANALYTICS_GA_ID atau ANALYTICS_GTM_ID diisi, peristiwa yang sama
                    mulai terkirim ke sana tanpa mengubah apa pun di halaman ini.
                @endif
            </p>

            <p>
                <strong>Kunjungan tim sendiri ikut terhitung.</strong> Tidak ada
                penyaring untuk itu, jadi angka pada hari kerja yang ramai penyuntingan
                cenderung sedikit lebih tinggi daripada perhatian pengunjung yang
                sebenarnya.
            </p>

            <p>
                <strong>Yang tidak disimpan:</strong> alamat IP dan User-Agent apa
                adanya. Kolom pengunjung berisi sidik pendek hasil hash keduanya
                bersama tanggal, yang berganti tiap tengah malam dan tidak bisa
                dikembalikan ke asalnya. Gunanya hanya membedakan sepuluh klik dari
                satu orang dengan sepuluh klik dari sepuluh orang.
            </p>

            @if ($this->masaSimpanBulan() > 0)
                <p>
                    Peristiwa yang lebih tua dari {{ $this->masaSimpanBulan() }} bulan
                    dihapus otomatis oleh penjadwal harian.
                </p>
            @else
                <p>
                    Pemangkasan otomatis dimatikan (ANALYTICS_RETENTION_MONTHS = 0),
                    jadi peristiwa disimpan tanpa batas waktu.
                </p>
            @endif
        </div>
    </x-filament::section>
</x-filament-panels::page>
