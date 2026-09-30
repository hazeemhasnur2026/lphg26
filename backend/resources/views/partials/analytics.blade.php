{{--
  Analytics (CMS-10).

  Tidak menyala sampai ada id yang diisi di .env, dan sengaja begitu. Skrip
  pengukuran yang ikut terpasang di lingkungan pengembangan dan di server uji
  akan mencampur kunjungan tim sendiri ke dalam angka yang dipakai mengambil
  keputusan, dan tidak ada yang menyadarinya sampai angkanya dipertanyakan.

  TOR menyebut "approved analytics platform" tanpa menyebut yang mana. Dua yang
  paling mungkin disiapkan keduanya: isi salah satu, bukan dua-duanya.
--}}
@php
    $ga = config('services.analytics.ga_id');
    $gtm = config('services.analytics.gtm_id');
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

@if ($ga || $gtm)
{{--
  Peristiwa yang diminta TOR: CTA, unduhan, pencarian, dan keterlibatan isi.

  Dipasang lewat satu pendengar di document, bukan atribut onclick di tiap
  tombol. Tombol datang dari belasan view dan sebagian dirender Alpine setelah
  halaman dimuat; atribut yang ditempel satu per satu akan terlewat persis di
  tombol yang baru ditambahkan orang.
--}}
<script>
(function () {
  var kirim = function (nama, data) {
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push(Object.assign({ event: nama }, data || {}));
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

  // Keterlibatan isi: dicatat sekali saat pembaca benar-benar sampai ke bawah
  // artikel, bukan saat halaman dibuka. Pembaca yang membuka lalu langsung
  // menutup bukan pembaca yang terlibat.
  var artikel = document.querySelector('article');
  if (artikel && 'IntersectionObserver' in window) {
    var sudah = false;
    new IntersectionObserver(function (entries, obs) {
      if (entries[0].isIntersecting && !sudah) {
        sudah = true;
        kirim('content_read', { content_title: document.title });
        obs.disconnect();
      }
    }, { threshold: 0.9 }).observe(artikel);
  }
})();
</script>
@endif
