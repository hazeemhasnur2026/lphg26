#!/usr/bin/env python3
"""Canonical + Open Graph + Twitter tags, JSON-LD, sitemap.xml and robots.txt.

Deliberately derives everything from what each page already declares. It reads
the page's own <title> and <meta name="description"> rather than keeping a
second copy of them here. Those descriptions are hand-written and page-specific;
a duplicate table would drift out of sync the first time someone edits one.

Everything it writes lives between the SEO markers in <head>, so re-running is
safe and the block can be regenerated without touching anything else.

    python3 tools/build_seo_meta.py [--check]

Set BASE_URL to the domain the site will actually be served from. Canonical and
og:url are absolute by definition, so this cannot be inferred from the files.
"""

import argparse
import datetime as dt
import html
import json
import pathlib
import re
import sys

ROOT = pathlib.Path(__file__).resolve().parent.parent
BASE_URL = "https://www.hasnurgroup.com"
START, END = "<!-- SEO:START", "<!-- SEO:END -->"

# Straight from the footer of index.html. Nothing invented.
ORGANISATION = {
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "Hasnur Group",
    "url": BASE_URL + "/",
    "logo": BASE_URL + "/assets/img/logo.png",
    "foundingDate": "1966",
    "email": "info@hasnurgroup.com",
    "telephone": "+622129343888",
    "address": {
        "@type": "PostalAddress",
        "streetAddress": "Office 8 Building Lantai 7, Jl. Senopati No. 8B",
        "addressLocality": "Jakarta Selatan",
        "addressRegion": "DKI Jakarta",
        "postalCode": "12190",
        "addressCountry": "ID",
    },
    "sameAs": [
        "https://www.linkedin.com/company/hasnurgroup",
        "https://www.instagram.com/hasnurgroup",
        "https://www.youtube.com/@hasnurgroup",
    ],
}


def pages():
    """index.html plus every subpage, with the image each should share as."""
    yield ROOT / "index.html", "index.html", "assets/img/logo-60th.png", 1.0
    for f in sorted((ROOT / "page").glob("*/*.html")):
        rel = f.relative_to(ROOT).as_posix()
        slug = f.parent.name + "-" + f.stem
        folder = ROOT / "assets/img/pages" / slug
        banner = next(iter(sorted(folder.glob("banner.*"))), None)
        img = (
            banner.relative_to(ROOT).as_posix()
            if banner
            else "assets/img/logo-60th.png"
        )
        yield f, rel, img, 0.8


def canonical_path(rel):
    """Map a prototype file to the URL production actually serves.

    The live site uses extension-less paths (verified while crawling:
    /page/csr/csr-activity, not .../csr-activity.html) and those are the URLs
    already indexed. Canonicalising to the .html filenames this prototype
    happens to use would point search engines at addresses that will not exist
    once the Laravel build takes over.
    """
    if rel == "index.html":
        return ""
    return rel[:-5] if rel.endswith(".html") else rel


def read_meta(text):
    title = re.search(r"<title>(.*?)</title>", text, re.S)
    desc = re.search(r'<meta\s+name="description"\s+content="([^"]*)"', text, re.I)
    return (
        html.unescape(title.group(1).strip()) if title else None,
        html.unescape(desc.group(1).strip()) if desc else None,
    )


def seo_block(*, url, title, description, image, is_home):
    e = html.escape
    tags = [
        f'<link rel="canonical" href="{e(url)}">',
        "",
        f'<meta property="og:type" content="{"website" if is_home else "article"}">',
        f'<meta property="og:site_name" content="Hasnur Group">',
        f'<meta property="og:locale" content="id_ID">',
        f'<meta property="og:url" content="{e(url)}">',
        f'<meta property="og:title" content="{e(title)}">',
        f'<meta property="og:description" content="{e(description)}">',
        f'<meta property="og:image" content="{e(image)}">',
        "",
        '<meta name="twitter:card" content="summary_large_image">',
        f'<meta name="twitter:title" content="{e(title)}">',
        f'<meta name="twitter:description" content="{e(description)}">',
        f'<meta name="twitter:image" content="{e(image)}">',
    ]
    if is_home:
        tags += [
            "",
            '<script type="application/ld+json">',
            json.dumps(ORGANISATION, indent=2, ensure_ascii=False),
            "</script>",
        ]
    body = "\n".join(("  " + t if t else "") for t in tags)
    return f"{START}. Dihasilkan oleh tools/build_seo_meta.py, jangan diedit manual -->\n{body}\n  {END}"


def apply(text, block):
    if START in text:
        i, j = text.index(START), text.index(END) + len(END)
        return text[:i] + block + text[j:]
    # first run: drop it in just before </head>
    i = text.lower().index("</head>")
    return text[:i] + block + "\n" + text[i:]


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--check", action="store_true")
    args = ap.parse_args()

    stale, written, missing, urls = [], 0, [], []

    for path, rel, image, priority in pages():
        text = path.read_text()
        title, desc = read_meta(text)
        if not title or not desc:
            missing.append(f"{rel}: {'title' if not title else 'description'} kosong")
            continue

        url = f"{BASE_URL}/{canonical_path(rel)}"
        block = seo_block(
            url=url,
            title=title,
            description=desc,
            image=f"{BASE_URL}/{image}",
            is_home=rel == "index.html",
        )
        new = apply(text, block)
        urls.append((url, priority))

        if new != text:
            if args.check:
                stale.append(rel)
            else:
                path.write_text(new)
                written += 1

    today = dt.date.today().isoformat()
    sitemap = (
        '<?xml version="1.0" encoding="UTF-8"?>\n'
        '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n'
        + "".join(
            f"  <url>\n    <loc>{html.escape(u)}</loc>\n"
            f"    <lastmod>{today}</lastmod>\n"
            f"    <priority>{p}</priority>\n  </url>\n"
            for u, p in urls
        )
        + "</urlset>\n"
    )
    robots = (
        "User-agent: *\n"
        "Allow: /\n\n"
        f"Sitemap: {BASE_URL}/sitemap.xml\n"
    )

    for name, content in (("sitemap.xml", sitemap), ("robots.txt", robots)):
        f = ROOT / name
        if not f.exists() or f.read_text() != content:
            if args.check:
                stale.append(name)
            else:
                f.write_text(content)
                written += 1

    if missing:
        print("dilewati:\n  " + "\n  ".join(missing), file=sys.stderr)

    if args.check:
        if stale:
            print("kedaluwarsa:\n  " + "\n  ".join(stale))
            sys.exit(1)
        print(f"{len(urls)} halaman sinkron dengan tools/build_seo_meta.py")
    else:
        print(f"{written} berkas diperbarui ({len(urls)} halaman + sitemap.xml + robots.txt)")


if __name__ == "__main__":
    main()
