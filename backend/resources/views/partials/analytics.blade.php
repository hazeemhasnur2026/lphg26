{{--
  Analytics (CMS-10).

  Dua penerima, satu daftar peristiwa.

  Yang pertama platform luar — GA4 atau GTM — dan ia tidak menyala sampai ada
  id yang diisi di .env. Sengaja begitu: skrip pengukuran pihak ketiga yang ikut
  terpasang di lingkungan pengembangan dan di server uji akan mencampur
  kunjungan tim sendiri ke dalam angka yang dipakai mengambil keputusan, dan
  tidak ada yang menyadarinya sampai angkanya dipertanyakan. TOR menyebut
  "approved analytics platform" tanpa menyebut yang mana; dua yang paling
  mungkin disiapkan keduanya, isi salah satu, bukan dua-duanya.

  Yang kedua situs ini sendiri: peristiwa yang sama dikirim ke rute
  analytics.track dan tersimpan di tabel analytics_events, terbaca di panel pada
  menu Analitik. Ini yang membuat keempat peristiwa TOR punya angka hari ini,
  tanpa menunggu platform mana yang disetujui. Ia tidak menggantikan platform
  luar — tidak ada atribusi kampanye, funnel, atau perbandingan lintas situs di
  sini — tapi pertanyaan "tombol mana yang sebenarnya diklik orang" tidak perlu
  menunggu rapat.
--}}
@php
    $ga = config('services.analytics.ga_id');
    $gtm = config('services.analytics.gtm_id');
    $internal = (bool) config('services.analytics.internal');
@endphp

@if ($gtm)
<script>
(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});
var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';
j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','{{ $gtm }}');
</script>
@elseif ($ga)
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga }}"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '{{ $ga }}');
</script>
@endif

@if ($ga || $gtm || $internal)
{{--
  Peristiwa yang diminta TOR: CTA, unduhan, pencarian, dan keterlibatan isi.

  Dipasang lewat satu pendengar di document, bukan atribut onclick di tiap
  tombol. Tombol datang dari belasan view dan sebagian dirender Alpine setelah
  halaman dimuat; atribut yang ditempel satu per satu akan terlewat persis di
  tombol yang baru ditambahkan orang.
--}}
<script>
(function () {
  var titik = @js($internal ? route('analytics.track') : null);

  // Token cadangan: nilai saat halaman ini dirender.
  var tokenHalaman = @js(csrf_token());

  /*
   * Token CSRF diambil dari cookie XSRF-TOKEN, bukan dari tokenHalaman di atas.
   *
   * Keduanya bernilai sama saat halaman baru dibuka, lalu berpisah. Token yang
   * tertanam di HTML beku sejak halaman dirender; cookie-nya disegarkan pada
   * setiap respons, dari tab mana pun. Begitu sesi berganti — umurnya lewat,
   * pembaca membuka tab lain, atau APP_KEY berubah di mesin pengembang — token
   * beku itu tidak cocok lagi, dan sejak saat itu setiap peristiwa dari tab
   * tersebut dijawab 419 tanpa pernah tersimpan.
   *
   * Yang paling dirugikan justru peristiwa yang paling ingin dihitung: pembaca
   * yang sampai ke ujung bawah artikel panjang adalah pembaca yang tabnya sudah
   * lama terbuka.
   *
   * Laravel menerima cookie ini apa adanya lewat header X-XSRF-TOKEN dan
   * mendekripsinya sendiri; nilainya ter-URL-encode di document.cookie.
   */
  var tokenSekarang = function () {
    var cocok = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);

    if (!cocok) return null;

    try {
      return decodeURIComponent(cocok[1]);
    } catch (e) {
      return null;
    }
  };

  var kirim = function (nama, data) {
    data = data || {};

    // Platform luar membaca dataLayer. Didorong lebih dulu dan tanpa syarat:
    // bila GTM belum termuat, antreannya tetap terbaca begitu ia termuat, dan
    // bila tidak ada platform sama sekali yang terbentuk hanya sebuah array.
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push(Object.assign({ event: nama }, data));

    if (!titik) return;

    // Satu kolom label untuk keempat peristiwa. Tiap peristiwa hanya mengisi
    // satu di antaranya, jadi yang pertama terisi itulah isi peristiwanya.
    var label = data.cta_name || data.file_name || data.search_term ||
                data.content_title || data.link_url || null;

    var xsrf = tokenSekarang();
    var kepala = { 'Content-Type': 'application/json' };

    if (xsrf) {
      kepala['X-XSRF-TOKEN'] = xsrf;
    } else {
      // Cookie tidak terbaca: peramban memblokir cookie, atau umur sesinya
      // sudah lewat tanpa satu pun permintaan lain di antaranya. Token halaman
      // adalah satu-satunya yang tersisa untuk dicoba.
      kepala['X-CSRF-TOKEN'] = tokenHalaman;
    }

    try {
      fetch(titik, {
        method: 'POST',
        // keepalive: peristiwa unduhan dan tautan keluar terkirim tepat saat
        // halaman mulai ditinggalkan. Tanpa ini browser membatalkan
        // permintaannya di tengah jalan, dan yang hilang justru dua peristiwa
        // yang paling ingin dihitung.
        keepalive: true,
        credentials: 'same-origin',
        headers: kepala,
        body: JSON.stringify({
          nama: nama,
          label: label,
          jalur: window.location.pathname + window.location.search,
          bahasa: document.documentElement.lang || null
        })
      }).then(function (r) {
        // Peristiwa yang ditolak harus meninggalkan jejak di suatu tempat.
        // Jawaban 419 bukan penolakan promise, jadi tanpa baris ini seluruh
        // peristiwa bisa hilang tanpa satu pun gejala — dan itulah cara bug
        // pertama di jalur ini sempat lolos: papannya tetap menunjukkan angka,
        // hanya saja angka yang tidak pernah bertambah.
        if (!r.ok) {
          console.warn('[analitik] peristiwa ' + nama + ' ditolak: HTTP ' + r.status +
            (r.status === 419 ? ' — token kedaluwarsa, muat ulang halaman' : ''));
        }
      }).catch(function () {});
    } catch (e) {}
  };

  document.addEventListener('click', function (e) {
    var a = e.target.closest('a, button');
    if (!a) return;

    var href = a.getAttribute('href') || '';

    if (/\.(pdf|docx?|xlsx?|pptx?|zip)($|\?)/i.test(href)) {
      kirim('file_download', { file_url: href, file_name: href.split('/').pop() });
      return;
    }

    if (a.dataset.cta) {
      kirim('cta_click', { cta_name: a.dataset.cta, cta_text: (a.innerText || '').trim().slice(0, 80) });
      return;
    }

    if (href && a.hostname && a.hostname !== window.location.hostname) {
      kirim('outbound_click', { link_url: href });
    }
  });

  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (!form.matches('form[role="search"], form[data-search]')) return;
    var q = form.querySelector('input[name="q"]');
    kirim('search', { search_term: q ? q.value : '' });
  });

  // Keterlibatan isi: dicatat sekali saat pembaca benar-benar sampai ke ujung
  // bawah artikel, bukan saat halaman dibuka. Pembaca yang membuka lalu
  // langsung menutup bukan pembaca yang terlibat.
  //
  // Yang diamati penanda setinggi satu piksel di akhir artikel, bukan elemen
  // <article> itu sendiri. Ambang IntersectionObserver dihitung terhadap luas
  // targetnya: pada artikel yang lebih tinggi dari layar, "90% target terlihat"
  // tidak pernah tercapai sekali pun pembaca membaca tiap katanya.
  var artikel = document.querySelector('article');
  if (artikel && 'IntersectionObserver' in window) {
    var penanda = document.createElement('div');
    penanda.setAttribute('aria-hidden', 'true');
    penanda.style.height = '1px';
    artikel.appendChild(penanda);

    new IntersectionObserver(function (entries, obs) {
      if (!entries[0].isIntersecting) return;
      obs.disconnect();
      kirim('content_read', { content_title: document.title });
    }).observe(penanda);
  }
})();
</script>
@endif
