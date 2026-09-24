/**
 * Hasnur Group. Landing Page Prototype (Phase 1: Frontend / Beranda)
 *
 * Nav dropdowns and mobile menu are handled inline with Alpine.js in
 * index.html. This file adds:
 *   1. createCarousel(). Shared carousel logic behind gallerySlider()
 *      (see below)
 *   2. the scroll-reveal enhancement (spec §5 UI/UX polish) so sections
 *      fade in as the visitor scrolls, with a safe no-op fallback
 *   3. a lightweight scroll-parallax effect for [data-parallax] elements
 */

/**
 * Shared carousel logic. Alpine component factory.
 *
 * Content-agnostic on purpose: each slide mirrors the `SlideBanner` model
 * in the rejuvenation spec (image, title, subtitle, order). Arrows/dots/
 * autoplay all no-op automatically when there's only one slide (guarded by
 * `slides.length > 1`), so this same factory works for any number of
 * slides without template changes.
 */
function createCarousel(slides, opts) {
  opts = opts || {};
  return {
    active: 0,
    slides: slides,
    timer: null,
    touchStartX: 0,

    init() {
      this.play();
    },
    play() {
      this.stop();
      if (this.slides.length <= 1) return;
      this.timer = setInterval(() => this.next(), opts.intervalMs || 6000);
    },
    stop() {
      if (this.timer) {
        clearInterval(this.timer);
        this.timer = null;
      }
    },
    next() {
      this.active = (this.active + 1) % this.slides.length;
    },
    prev() {
      this.active = (this.active - 1 + this.slides.length) % this.slides.length;
    },
    goTo(i) {
      this.active = i;
      this.play(); // restart the autoplay clock after a manual jump
    },
    onTouchStart(e) {
      this.touchStartX = e.changedTouches[0].clientX;
    },
    onTouchEnd(e) {
      const dx = e.changedTouches[0].clientX - this.touchStartX;
      if (Math.abs(dx) < 40) return; // ignore taps / tiny drags
      dx > 0 ? this.prev() : this.next();
      this.play();
    },
  };
}

// Gallery slider below the hero. Placeholder content using existing
// documentation photos (assets/img/gallery/) until a dedicated
// SlideBanner-style feed exists for this section.
function gallerySlider() {
  return createCarousel(
    [
      {
        src: 'assets/img/gallery/01-hasnur-group-she-activity.jpg',
        alt: 'Aktivitas SHE Hasnur Group',
        title: 'Aktivitas SHE',
        subtitle: 'Komitmen keselamatan, kesehatan, dan lingkungan di seluruh unit operasional.',
      },
      {
        src: 'assets/img/gallery/02-hasnur-group-csr-activity.jpg',
        alt: 'Aktivitas CSR Hasnur Group',
        title: 'Aktivitas CSR',
        subtitle: 'Program tanggung jawab sosial perusahaan untuk masyarakat sekitar.',
      },
      {
        src: 'assets/img/gallery/04-hasnur-group-memberikan-bantuan-banjir-k.jpg',
        alt: 'Bantuan penanganan banjir',
        title: 'Tanggap Bencana',
        subtitle: 'Bantuan penanganan banjir bagi masyarakat terdampak.',
      },
      {
        src: 'assets/img/gallery/05-yayasan-hasnur-centre-menyerahkan-bantua.jpg',
        alt: 'Yayasan Hasnur Centre menyerahkan bantuan',
        title: 'Yayasan Hasnur Centre',
        subtitle: 'Penyaluran bantuan sosial melalui yayasan yang didirikan founder.',
      },
      {
        src: 'assets/img/gallery/09-she-hasnur-group-tanggap-darurat-di-sbu.jpg',
        alt: 'SHE tanggap darurat di SBU',
        title: 'Tanggap Darurat',
        subtitle: 'Kesiapsiagaan tim SHE di seluruh Strategic Business Unit.',
      },
    ],
    { intervalMs: 5000 }
  );
}
window.gallerySlider = gallerySlider;

(function () {
  if (!('IntersectionObserver' in window)) {
    document.documentElement.classList.add('no-observer');
    return;
  }

  var observer = new IntersectionObserver(
    function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.15, rootMargin: '0px 0px -40px 0px' }
  );

  // .reveal is the generic content fade+rise; .tl-node / .tl-line are the
  // timeline's own entrance animations (see assets/css/style.css). They all
  // reveal the same way. By gaining .is-visible once scrolled into view.
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.reveal, .tl-node, .tl-line, .tl-turn').forEach(function (el) {
      observer.observe(el);
    });
  });
})();

/**
 * Scroll parallax. Moves any [data-parallax] element vertically at a
 * fraction of scroll speed, based on its position relative to the viewport
 * center. Works on plain in-flow elements (images, decorative blobs) with
 * no extra markup beyond the attribute, and is skipped entirely when the
 * visitor prefers reduced motion or no elements opt in.
 *
 * Elements that already carry their own CSS transform animation (e.g. the
 * hero's ".animate-blob" drift) must put data-parallax on a wrapping div
 * instead of the animated element itself. Both this script and a CSS
 * animation target `transform`, and the animation always wins on the
 * element it's declared on.
 */
(function () {
  var prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var layers = Array.prototype.slice.call(document.querySelectorAll('[data-parallax]'));
  if (prefersReduced || !layers.length) return;

  var ticking = false;
  function apply() {
    var vh = window.innerHeight;
    layers.forEach(function (el) {
      var speed = parseFloat(el.getAttribute('data-parallax')) || 0.1;
      var rect = el.getBoundingClientRect();
      var offset = (rect.top + rect.height / 2 - vh / 2) * speed;
      el.style.transform = 'translate3d(0,' + offset.toFixed(1) + 'px,0)';
    });
    ticking = false;
  }
  function onScroll() {
    if (!ticking) {
      ticking = true;
      window.requestAnimationFrame(apply);
    }
  }

  document.addEventListener('DOMContentLoaded', apply);
  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onScroll);
})();

/**
 * Nilai inti: animasi yang digerakkan gulir, bukan oleh tombol.
 *
 * Bagiannya ditahan di layar selama jalur gulirnya, dan posisi gulir di dalam
 * jalur itu menjadi satu angka 0..1. Angka itu yang menggeser roda ke kiri dan
 * memunculkan kartu satu per satu. Setelah 1, halaman melanjut ke section
 * bawahnya seperti biasa.
 *
 * Hanya berlaku dari lg ke atas: di layar kecil tidak ada ruang untuk roda dan
 * kartu berdampingan, jadi keduanya ditumpuk biasa tanpa ditahan. Kalau sistem
 * operasinya meminta gerakan dikurangi, seluruh efeknya dilewati. Kartunya
 * tampil semua sejak awal.
 */
(function () {
  var section = document.querySelector('[data-core-value]');
  if (!section) return;

  var desktop = window.matchMedia('(min-width: 1024px)');
  var calmer = window.matchMedia('(prefers-reduced-motion: reduce)');

  var track = section.querySelector('.core-value-track');
  var stage = section.querySelector('.core-value-stage');
  var wheel = section.querySelector('.core-value-wheel');
  var cards = Array.prototype.slice.call(section.querySelectorAll('.core-value-card'));
  if (!track || !stage || !wheel || !cards.length) return;

  var ticking = false;

  function clamp(value) {
    return value < 0 ? 0 : value > 1 ? 1 : value;
  }

  // Perbesaran roda saat berada di tengah. 1,6 kali ukuran akhirnya, yang
  // mengembalikan besarnya ke ukuran sebelum panel ini dirapatkan.
  var BIG = 1.6;

  var shift = 0;
  var measuredAt = -1;

  /**
   * Jarak yang membuat roda tampak berada di tengah seluruh lebar bagian ini.
   *
   * Diukur dengan geserannya dinolkan lebih dulu, dan hasilnya disimpan.
   * getBoundingClientRect() melaporkan posisi setelah transform, jadi mengukur
   * sambil menggeser berarti hasil ukurnya sudah mengandung geseran frame
   * sebelumnya. Umpan balik yang membuat roda bergetar dan hanya sampai
   * separuh jarak seharusnya.
   *
   * Diukur ulang hanya saat lebar jendela berubah. Tinggi jendela tidak dipakai
   * sebagai pemicu: di ponsel ia berubah-ubah sendiri saat bilah alamat muncul
   * dan hilang.
   */
  function measureShift() {
    if (measuredAt === window.innerWidth) return;

    stage.style.setProperty('--core-value-shift', '0px');
    stage.style.setProperty('--core-value-scale', '1');
    var area = stage.parentElement.getBoundingClientRect();
    var box = wheel.getBoundingClientRect();

    shift = area.left + area.width / 2 - (box.left + box.width / 2);
    measuredAt = window.innerWidth;
  }

  function apply() {
    ticking = false;

    if (!desktop.matches || calmer.matches) {
      section.classList.remove('core-value-scripted');
      stage.style.removeProperty('--core-value-shift');
      stage.style.removeProperty('--core-value-scale');
      measuredAt = -1;
      cards.forEach(function (card) { card.classList.add('is-revealed'); });
      return;
    }

    section.classList.add('core-value-scripted');
    measureShift();

    var rect = track.getBoundingClientRect();
    var distance = rect.height - window.innerHeight;
    var progress = distance > 0 ? clamp(-rect.top / distance) : 0;

    // Roda selesai bergeser di sepertiga pertama, supaya sisa gulirannya
    // sepenuhnya milik kartu. Kalau keduanya berjalan sampai akhir, roda masih
    // bergerak ketika kartu terakhir muncul dan keduanya saling menarik mata.
    var settling = 1 - clamp(progress / 0.3);
    stage.style.setProperty('--core-value-shift', (shift * settling).toFixed(1) + 'px');

    // Besar saat masih di tengah, mengecil sambil menepi. Dipasang sebagai skala
    // dan bukan lebar: lebar memicu tata letak dihitung ulang tiap frame dan ikut
    // menggeser kartu di sebelahnya.
    stage.style.setProperty('--core-value-scale', (1 + (BIG - 1) * settling).toFixed(3));

    cards.forEach(function (card, index) {
      // Kartu pertama mulai muncul begitu roda hampir sampai; yang terakhir
      // selesai sebelum gulirannya habis, jadi ada jeda sebelum halaman lanjut.
      var at = 0.28 + (index / cards.length) * 0.6;
      card.classList.toggle('is-revealed', progress >= at);
    });
  }

  function remeasure() {
    measuredAt = -1;
    schedule();
  }

  function schedule() {
    if (ticking) return;
    ticking = true;
    window.requestAnimationFrame(apply);
  }

  window.addEventListener('scroll', schedule, { passive: true });
  window.addEventListener('resize', remeasure);
  desktop.addEventListener('change', remeasure);
  calmer.addEventListener('change', remeasure);
  apply();
})();
