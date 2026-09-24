#!/usr/bin/env python3
"""Pastikan tiap kelas yang dipakai halaman benar-benar ada di CSS hasil build.

Dibuat karena satu kelemahan khas pindah dari Tailwind Play CDN ke build: CDN
membangun CSS di browser dari kelas yang benar-benar ada di DOM, sedangkan build
memindai berkas sumber. Kelas yang namanya disusun saat render, di proyek ini
`xl:col-span-{{ ... }}` pada kartu SBU. Tidak pernah terlihat oleh pemindai,
jadi kelasnya tidak ikut dibangun. Halamannya tetap balas 200 dan tetap tampak
wajar sekilas; yang hilang hanya satu aturan tata letak.

Kelas dikumpulkan dari HTML yang sudah dirender, termasuk atribut x-transition
milik Alpine yang menambahkan kelasnya saat berjalan, lalu dicocokkan ke seluruh
stylesheet yang dimuat halaman.

    python3 tools/check_css_coverage.py [--base http://127.0.0.1:8123]

Keluar dengan status 1 bila ada kelas tanpa aturan.
"""

import argparse
import pathlib
import re
import sys
import urllib.parse
import urllib.request

ROOT = pathlib.Path(__file__).resolve().parent.parent

CLASS_ATTR = re.compile(r'\sclass="([^"]*)"')
TRANSITION_ATTR = re.compile(r'\sx-transition:[a-z-]+="([^"]*)"')
STYLESHEET = re.compile(r'<link[^>]+rel="stylesheet"[^>]+href="([^"]+)"')
LOC = re.compile(r"<loc>(.*?)</loc>")

# Kelas yang memang tidak punya aturan CSS dan tidak seharusnya punya.
IGNORED = {
    # Penanda untuk varian group-hover/peer-*, bukan utilitas.
    "group",
    "peer",
    # Kelas milik markup situs lama yang ikut terbawa di badan artikel hasil
    # crawl. Bukan kelas kami; gayanya diurus .prose-hasnur. Sengaja disebut
    # satu per satu, bukan dipola: kalau crawl berikutnya membawa kelas asing
    # baru, laporannya muncul dan seseorang memutuskan.
    "article-content-body__item-content",
    "article-content-body__item-page",
    "bottom-of-page",
}


def fetch(url: str) -> str:
    with urllib.request.urlopen(url, timeout=30) as response:
        return response.read().decode("utf-8", "replace")


def escape(token: str) -> str:
    """Selektor CSS untuk satu nama kelas, seperti yang ditulis Tailwind."""
    out = []
    for char in token:
        out.append("\\" + char if char in ':/[]().,%#!<>+*~=&' else char)
    return "." + "".join(out)


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--base", default="http://127.0.0.1:8123")
    args = parser.parse_args()
    base = args.base.rstrip("/")

    pages = LOC.findall(fetch(f"{base}/sitemap.xml"))
    print(f"{len(pages)} halaman dari sitemap")

    used: dict[str, str] = {}
    sheets: set[str] = set()

    for page in pages:
        html = fetch(page)
        path = urllib.parse.urlparse(page).path or "/"

        for match in list(CLASS_ATTR.finditer(html)) + list(TRANSITION_ATTR.finditer(html)):
            for token in match.group(1).split():
                used.setdefault(token, path)

        for href in STYLESHEET.findall(html):
            sheets.add(urllib.parse.urljoin(page, href))

    css = "".join(fetch(sheet) for sheet in sorted(sheets))
    print(f"{len(sheets)} stylesheet, {len(css) / 1024:.1f} KB mentah")
    print(f"{len(used)} nama kelas dipakai")

    missing = {
        token: path
        for token, path in sorted(used.items())
        if token not in IGNORED and escape(token) not in css
    }

    if not missing:
        print("\nsemua kelas punya aturan")
        return 0

    print(f"\n{len(missing)} kelas tanpa aturan CSS:")
    for token, path in missing.items():
        print(f"  - {token:34} dipakai di {path}")
    return 1


if __name__ == "__main__":
    sys.exit(main())
