#!/usr/bin/env python3
"""Single source of truth for the banner on every /page/ subpage.

This site is plain static HTML. No React, no build step, no template engine,
so a literal <PageHero /> component is not available. The equivalent that still
serves a real <h1> in the delivered markup is a generator: the hero is defined
once, here, and written into each page between the PAGE_HERO markers. Editing a
hero by hand in a page file is pointless; it gets overwritten on the next run.

When the project moves to Laravel (see docs/spesifikasi.md §7),
PAGES maps straight onto <x-page-hero> props and this file goes away.

    python3 tools/build_page_hero.py [--check]
"""

import argparse
import html
import pathlib
import re
import sys

ROOT = pathlib.Path(__file__).resolve().parent.parent
START, END = "<!-- PAGE_HERO:START", "<!-- PAGE_HERO:END -->"


# --------------------------------------------------------------------------
# the component
# --------------------------------------------------------------------------
def page_hero(*, depth, breadcrumb, title, image, description=None, tags=(), badge=None, tabs=()):
    """Render the banner.

    breadcrumb  [{label, href?}]  last entry (no href) renders as the active crumb
    description str|None          omitted entirely when absent. Never an empty <p>
    tags        [{icon, label}]   pill row; omitted entirely when empty
    badge       {icon, title, subtitle}|None   floating card on the photo corner
    tabs        [{label, href, active?}]       sibling-page switcher, omitted when empty
    """
    up = "../" * depth
    e = html.escape

    crumbs = []
    for i, c in enumerate(breadcrumb):
        last = i == len(breadcrumb) - 1
        sep = "" if last else '<li aria-hidden="true" class="text-white/35">/</li>'
        if last:
            crumbs.append(
                f'<li><span aria-current="page" class="font-semibold text-logo-yellow">{e(c["label"])}</span></li>')
        else:
            crumbs.append(
                f'<li><a href="{e(up + c["href"])}" class="text-white/65 transition hover:text-white">{e(c["label"])}</a></li>{sep}')
    crumb_html = "\n            ".join(crumbs)

    desc_html = ""
    if description:
        desc_html = (
            f'\n      <p class="mt-4 max-w-[460px] leading-relaxed text-white/80">{e(description)}</p>')

    tags_html = ""
    if tags:
        pills = "\n          ".join(
            f'<li class="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-white/10 px-3 py-1 text-xs font-semibold">'
            f'<span aria-hidden="true">{t["icon"]}</span>{e(t["label"])}</li>'
            for t in tags)
        tags_html = f'\n      <ul class="mt-6 flex flex-wrap gap-2">\n          {pills}\n      </ul>'

    tabs_html = ""
    if tabs:
        items = "\n          ".join(
            f'<a href="{e(up + t["href"])}" class="rounded-full px-4 py-1.5 text-sm font-semibold transition '
            + ('bg-white text-logo-blue shadow-sm"' if t.get("active")
               else 'border border-white/25 text-white/80 hover:bg-white/10"')
            + f'>{e(t["label"])}</a>'
            for t in tabs)
        tabs_html = f'\n      <nav class="mt-6 flex flex-wrap gap-2" aria-label="Sub-halaman">\n          {items}\n      </nav>'

    badge_html = ""
    if badge:
        badge_html = (
            '\n        <div class="absolute -bottom-6 left-6 flex items-center gap-3 rounded-xl bg-white px-4 py-3 shadow-xl">'
            f'\n          <span class="text-xl" aria-hidden="true">{badge["icon"]}</span>'
            '\n          <span class="leading-tight">'
            f'\n            <span class="block text-sm font-bold text-slate-900">{e(badge["title"])}</span>'
            f'\n            <span class="block text-xs text-slate-500">{e(badge["subtitle"])}</span>'
            "\n          </span>\n        </div>")

    # Navy comes from the logo-blue / logo-blue-dark tokens in the Tailwind
    # config, so the brand colour is defined in one place, not per page.
    return f"""{START}. Dihasilkan oleh tools/build_page_hero.py, jangan diedit manual -->
<section class="relative overflow-hidden bg-gradient-to-br from-logo-blue via-logo-blue to-logo-blue-dark pt-[72px] text-white">
  <div class="h-1.5 w-full" style="background:linear-gradient(90deg,#154F91,#FFE80A,#2A9941,#E03633,#154F91)"></div>
  <div class="absolute inset-0 opacity-10 hero-pattern"></div>
  <div class="pointer-events-none absolute -right-16 -top-24 h-72 w-72 rounded-full border border-white/10"></div>
  <div class="pointer-events-none absolute -right-44 -top-6 h-96 w-96 rounded-full border border-white/[0.07]"></div>

  <div class="relative mx-auto grid max-w-7xl items-stretch gap-10 px-4 py-8 sm:px-6 lg:grid-cols-2 lg:px-8 lg:py-14">
    <div class="reveal flex flex-col justify-center">
      <nav aria-label="Breadcrumb" class="mb-3 text-xs">
        <ol class="flex flex-wrap items-center gap-x-1.5">
            {crumb_html}
        </ol>
      </nav>
      <h1 class="text-4xl font-extrabold leading-tight md:text-5xl">{e(title)}</h1>{desc_html}{tags_html}{tabs_html}
    </div>

    <div class="reveal relative">
      <img src="{e(up + image['src'])}" alt="{e(image['alt'])}" loading="eager"
           class="h-full w-full rounded-2xl object-cover shadow-2xl">{badge_html}
    </div>
  </div>
</section>
{END}"""


# --------------------------------------------------------------------------
# per-page data
# --------------------------------------------------------------------------
HOME = {"label": "Beranda", "href": "index.html"}

GROUPS = {
    "about": ("Tentang Kami", [
        ("about-us/overview.html", "Ikhtisar"),
        ("about-us/vision-mission.html", "Visi Misi"),
        ("about-us/core-value.html", "Nilai Inti")]),
    "csr": ("Tanggung Jawab Sosial Perusahaan", [
        ("csr/csr-overview.html", "Ikhtisar"),
        ("csr/csr-activity.html", "Aktifitas CSR")]),
    "she": ("Keselamatan, Kesehatan, & Lingkungan", [
        ("she/overview-1.html", "Ikhtisar"),
        ("she/she-activity.html", "Aktivitas SHE")]),
    "business": ("Bisnis Kami", [
        ("our-business/logistic.html", "Logistik"),
        ("our-business/agribusiness-forestry.html", "Agribisnis & Kehutanan"),
        ("our-business/energy.html", "Energi"),
        ("our-business/technology-services.html", "Teknologi & Jasa"),
        ("our-business/education.html", "Pendidikan"),
        ("our-business/consumer.html", "Kebutuhan Pelanggan"),
        ("our-business/investment.html", "Investasi")]),
    "publication": ("Publikasi", [
        ("publication/latest-update.html", "Update Terbaru"),
        ("publication/news.html", "Berita"),
        ("publication/press-release.html", "Press Release"),
        ("publication/multimedia.html", "Multimedia"),
        ("publication/gallery.html", "Galeri")]),
    "career": ("Karir", [("career/our-vacancy.html", "Lowongan Kami")]),
}

# Descriptions are lifted from each page's own opening paragraph, trimmed to a
# sentence or two. Listing pages, whose opening line only describes the
# prototype, get a plain statement of what the page holds instead. Nothing here
# asserts anything about the company that the page did not already say.
DESCRIPTIONS = {
    "about-us/overview.html": "Grup usaha yang didirikan pengusaha asli Kalimantan, H. Abdussamad Sulaiman HB bersama Hj. Nurhayati, dan tumbuh dari usaha angkutan sungai sejak 1966.",
    "about-us/vision-mission.html": "Membangun perusahaan-perusahaan mandiri dengan reputasi internasional yang membawa kebanggaan bagi bangsa.",
    "about-us/core-value.html": "Filosofi “Borneo Blossom” dan nilai-nilai yang menjadi pegangan seluruh unit usaha Hasnur Group.",
    "csr/csr-overview.html": "Komitmen Hasnur Group untuk terus berkontribusi bagi masyarakat dan lingkungan di sekitar wilayah operasionalnya.",
    "csr/csr-activity.html": "Dokumentasi kegiatan tanggung jawab sosial perusahaan yang dijalankan Hasnur Group.",
    "she/overview-1.html": "Direktorat SHE bertujuan meminimalkan kecelakaan kerja serta menjaga keselamatan, kesehatan, dan lingkungan di seluruh unit usaha.",
    "she/she-activity.html": "Dokumentasi kegiatan keselamatan, kesehatan, dan lingkungan di lingkungan Hasnur Group.",
    "our-business/logistic.html": "SBU Logistik dikembangkan untuk mendukung kebutuhan logistik internal grup maupun mitra eksternal.",
    "our-business/agribusiness-forestry.html": "Berawal dari jasa penarikan rakit kayu di DAS Barito, kini mencakup usaha perkebunan dan kehutanan.",
    "our-business/energy.html": "SBU Energy menaungi sub holding di bidang pertambangan dengan kapasitas produksi yang terus tumbuh.",
    "our-business/technology-services.html": "Menaungi unit bisnis di bidang pengembangan teknologi dan jasa layanan.",
    "our-business/education.html": "Mengembangkan pendidikan bertaraf internasional dengan pendekatan kearifan lokal dan religius.",
    "our-business/consumer.html": "Membawahi usaha bisnis di bidang media, gaya hidup, dan kesehatan.",
    "our-business/investment.html": "Mendukung pengembangan berbagai bisnis grup melalui investasi internal maupun eksternal.",
    "publication/latest-update.html": "Kabar dan pembaruan terbaru dari seluruh unit usaha Hasnur Group.",
    "publication/news.html": "Pemberitaan mengenai Hasnur Group di berbagai media.",
    "publication/press-release.html": "Siaran pers resmi yang diterbitkan Hasnur Group.",
    "publication/multimedia.html": "Kumpulan materi video dan multimedia Hasnur Group.",
    "publication/gallery.html": "Galeri foto kegiatan Hasnur Group.",
    "career/our-vacancy.html": "Hasnur Group mencari orang-orang dengan bakat terbaik untuk bisnis terintegrasi terdepan di Indonesia.",
}


def pages():
    """Build the full prop set for every subpage from the nav structure."""
    for key, (group_label, members) in GROUPS.items():
        for path, label in members:
            depth = path.count("/") + 1          # page/<dir>/<file>
            slug = path.rsplit("/", 1)[0] + "-" + pathlib.Path(path).stem
            src = ROOT / "page" / path
            img_dir = ROOT / "assets/img/pages" / slug.replace("/", "-")
            banner = next((p for p in sorted(img_dir.glob("banner.*"))), None)
            yield dict(
                path=src,
                depth=depth,
                breadcrumb=[
                    {**HOME, "href": "index.html"},
                    {"label": group_label, "href": "page/" + members[0][0]},
                    {"label": label},
                ],
                title=label,
                description=DESCRIPTIONS.get(path),
                image={"src": f"assets/img/pages/{slug.replace('/', '-')}/{banner.name}" if banner else "",
                       "alt": f"{label}. Hasnur Group"},
                # Only groups with more than one member get a switcher; single-page
                # groups would render a lone tab that does nothing.
                tabs=[{"label": lbl, "href": "page/" + p, "active": p == path}
                      for p, lbl in members] if len(members) > 1 else [],
            )


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--check", action="store_true",
                    help="exit non-zero if any page is out of date instead of writing")
    args = ap.parse_args()

    stale, written = [], 0
    for d in pages():
        path = d.pop("path")
        text = path.read_text()
        hero = page_hero(**d)

        if START in text:
            i, j = text.index(START), text.index(END) + len(END)
            new = text[:i] + hero + text[j:]
        else:  # first run: replace the hand-written banner section
            m = re.search(r"<!-- =+ PAGE BANNER =+ -->\s*<section\b.*?</section>", text, re.S)
            if not m:
                sys.exit(f"tidak menemukan banner di {path}")
            new = text[:m.start()] + hero + text[m.end():]

        if new != text:
            if args.check:
                stale.append(path.relative_to(ROOT))
            else:
                path.write_text(new)
                written += 1

    if args.check:
        if stale:
            print("hero kedaluwarsa:\n  " + "\n  ".join(str(s) for s in stale))
            sys.exit(1)
        print("semua hero sinkron dengan tools/build_page_hero.py")
    else:
        print(f"{written} halaman diperbarui")


if __name__ == "__main__":
    main()
