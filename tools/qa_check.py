#!/usr/bin/env python3
"""Sapuan QA sebelum cutover: status, SEO per halaman, tautan, dan URL lama.

Yang diperiksa, semuanya terhadap situs yang benar-benar berjalan:

1. Setiap URL di sitemap balas 200.
2. Tiap halaman punya satu <h1>, <title>, meta description, dan canonical yang
   menunjuk dirinya sendiri. Duplikat title/description dilaporkan, karena
   itulah keluhan paling umum Search Console setelah migrasi CMS. Duplikat
   dibandingkan di dalam satu bahasa saja: /en dan /ja memang menyajikan judul
   yang sama dengan versi Indonesia selama isinya belum diterjemahkan, dan
   hubungan antar-bahasa itu justru yang dijawab hreflang, bukan dianggap
   duplikat.
3. Setiap tautan internal di seluruh halaman benar-benar bisa dibuka.
4. Setiap URL artikel situs lama (data/discovered-urls.json) balas 200, 410, atau
   pengalihan, bukan 404. 410 berarti artikelnya sengaja dihapus permanen dan
   Google diberi tahu; 404 berarti URL terindeks yang jatuh tanpa penjelasan.
5. Gambar tanpa atribut alt, dan setiap URL gambar, termasuk tiap kandidat di
   srcset. Benar-benar bisa diambil. Path gambar tersimpan di database dan
   pernah berganti bentuk sekali; halaman tetap 200 walau gambarnya semua mati.
6. Halaman daftar yang tidak memuat satu artikel pun. Daftar kosong berisiko
   dinilai soft 404 oleh Google, dan lebih buruk lagi kalau URL artikel yang
   sudah terindeks dialihkan ke sana. Pengalihannya ikut jadi soft 404.

    python3 tools/qa_check.py [--base http://127.0.0.1:8123]

Keluar dengan status 1 bila ada temuan, jadi bisa dipakai sebagai gerbang.
"""

import argparse
import collections
import json
import pathlib
import re
import sys
import urllib.error
import urllib.parse
import urllib.request

ROOT = pathlib.Path(__file__).resolve().parent.parent

TITLE = re.compile(r"<title>(.*?)</title>", re.S)
DESCRIPTION = re.compile(r'<meta name="description" content="(.*?)"', re.S)
CANONICAL = re.compile(r'<link rel="canonical" href="(.*?)"', re.S)
H1 = re.compile(r"<h1\b", re.I)
LINK = re.compile(r'<a\b[^>]*\bhref="([^"]+)"', re.I)
IMG = re.compile(r"<img\b[^>]*>", re.I)
IMG_SRC = re.compile(r'<img\b[^>]*\bsrc="(/[^"]+)"', re.I)
SRCSET = re.compile(r'\bsrcset="([^"]+)"', re.I)
# Harus ketat: ':alt="' mengandung 'alt="' sebagai substring, jadi pengecekan
# polos meloloskan gambar tanpa alt di sebelah gambar yang alt-nya diikat Alpine.
ALT = re.compile(r'(?<![-:\w])alt="', re.I)
BOUND_ALT = re.compile(r'(?::|x-bind:)alt="', re.I)
LOC = re.compile(r"<loc>(.*?)</loc>")
# Halaman daftar artikel, dibedakan dari halaman berisi konten tetap.
LISTING_PATH = re.compile(r"(?:/[a-z]{2})?/page/(?:publication|csr|she)/[a-z-]*(?:update|news|release|activity)")

# Prefiks bahasa di awal path; tanpa prefiks berarti bahasa Indonesia.
LOCALE_PREFIX = re.compile(r"^/([a-z]{2})(?:/|$)")


def locale_of(path: str) -> str:
    match = LOCALE_PREFIX.match(path)
    return match.group(1) if match else "id"


def fetch(url):
    """(status, body, lokasi-pengalihan). Pengalihan tidak diikuti."""

    class NoRedirect(urllib.request.HTTPRedirectHandler):
        def redirect_request(self, *args, **kwargs):
            return None

    opener = urllib.request.build_opener(NoRedirect)
    try:
        with opener.open(url, timeout
=30) as response:
            return response.status, response.read().decode("utf-8", "replace"), None
    except urllib.error.HTTPError as error:
        return error.code, "", error.headers.get("Location")
    except urllib.error.URLError as error:
        return 0, str(error.reason), None


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--base", default="http://127.0.0.1:8123")
    args = parser.parse_args()
    base = args.base.rstrip("/")

    findings = []

    status, sitemap, _ = fetch(f"{base}/sitemap.xml")
    if status != 200:
        print(f"sitemap.xml balas {status}. Situs jalan di {base}?", file=sys.stderr)
        return 1
    urls = LOC.findall(sitemap)
    print(f"sitemap: {len(urls)} URL")

    listing_articles = {}
    images = {}
    titles = collections.defaultdict(list)
    descriptions = collections.defaultdict(list)
    internal_links = collections.defaultdict(set)
    pages = {}

    for url in urls:
        status, body, _ = fetch(url)
        path = urllib.parse.urlparse(url).path or "/"
        pages[path] = status
        if status != 200:
            findings.append(f"{path}: status {status}")
            continue

        title = TITLE.search(body)
        description = DESCRIPTION.search(body)
        canonical = CANONICAL.search(body)
        heads = len(H1.findall(body))

        if not title:
            findings.append(f"{path}: tidak ada <title>")
        else:
            titles[(locale_of(path), title.group(1).strip())].append(path)
        if not description:
            findings.append(f"{path}: tidak ada meta description")
        else:
            descriptions[(locale_of(path), description.group(1).strip())].append(path)
        if not canonical:
            findings.append(f"{path}: tidak ada canonical")
        elif urllib.parse.urlparse(canonical.group(1)).path.rstrip("/") != path.rstrip("/"):
            findings.append(f"{path}: canonical menunjuk {canonical.group(1)}")
        if heads != 1:
            findings.append(f"{path}: {heads} buah <h1> (harus tepat satu)")

        for tag in IMG.findall(body):
            # Alt yang diikat Alpine tetap terisi saat dirender di browser.
            if not ALT.search(tag) and not BOUND_ALT.search(tag):
                findings.append(f"{path}: <img> tanpa alt, {tag[:70]}")

        if LISTING_PATH.fullmatch(path):
            listing_articles[path] = {h for h in LINK.findall(body) if "/post/" in h}

        for src in IMG_SRC.findall(body):
            images.setdefault(src, path)
        for srcset in SRCSET.findall(body):
            for candidate in srcset.split(","):
                if candidate.strip():
                    images.setdefault(candidate.strip().split()[0], path)

        for href in LINK.findall(body):
            target = urllib.parse.urljoin(url, href)
            if target.startswith(base) and "#" not in href:
                internal_links[urllib.parse.urlparse(target).path].add(path)

    for label, seen in (("title", titles), ("description", descriptions)):
        for (locale, value), paths in seen.items():
            if len(paths) > 1:
                findings.append(
                    f"{label} dipakai {len(paths)} halaman dalam bahasa {locale}: "
                    f"{value[:50]!r}, {', '.join(paths[:4])}"
                )

    print(f"tautan internal unik: {len(internal_links)}")
    for path, sources in sorted(internal_links.items()):
        if path in pages:
            continue
        status, _, _ = fetch(base + path)
        pages[path] = status
        if status >= 400:
            findings.append(f"tautan mati {path} (status {status}) di {', '.join(sorted(sources)[:3])}")

    # Halaman daftar harus benar-benar memuat artikel.
    for path, targets in listing_articles.items():
        if not targets:
            findings.append(f"daftar {path} tidak memuat satu artikel pun (risiko soft 404)")

    print(f"URL gambar unik: {len(images)}")
    for url, path in sorted(images.items()):
        status, _, _ = fetch(base + url)
        if status != 200:
            findings.append(f"gambar {url} balas {status} (di {path})")

    discovered = json.loads((ROOT / "data/discovered-urls.json").read_text())
    print(f"URL artikel situs lama: {len(discovered)}")
    unhandled, redirected, retired = [], 0, 0
    for row in discovered:
        path = urllib.parse.urlparse(row["url"]).path
        status, _, location = fetch(base + path)
        if status in (301, 302, 307, 308):
            redirected += 1
        elif status == 410:
            retired += 1
        elif status >= 400:
            unhandled.append(f"{path} -> {status}")
    live = len(discovered) - redirected - retired - len(unhandled)
    print(f"  masih hidup: {live}, dihapus permanen (410): {retired}, dialihkan: {redirected}")
    findings.extend(f"URL lama jatuh tanpa penjelasan: {u}" for u in unhandled)

    print()
    if not findings:
        print("tidak ada temuan")
        return 0

    print(f"{len(findings)} temuan:")
    for finding in findings:
        print(f"  - {finding}")
    return 1


if __name__ == "__main__":
    sys.exit(main())
