#!/usr/bin/env python3
"""Turunkan resolusi + konversi WebP untuk aset gambar, tanpa menyentuh aslinya.

Masalah yang diselesaikan: `assets/img/pages` berisi 123 MB foto resolusi kamera,
beberapa 5000x3750 piksel, padahal lebar tampilnya di layout tidak pernah
lebih dari sekitar 800 px. Beranda sendiri mengirim 7,4 MB gambar.

Yang dilakukan: untuk setiap JPEG/PNG di bawah tree sumber, tulis varian WebP
pada beberapa lebar ke tree cermin (`assets/img-opt/`, `content-migration/images-opt/`),
lalu catat hasilnya di `data/image-variants.json`. File asli **tidak pernah
ditulis ulang atau dihapus**. Ia tetap jadi fallback <img> dan satu-satunya
master beresolusi penuh. Itu juga alasan output ditaruh di tree terpisah:
regenerasi berarti hapus satu folder, bukan memulihkan 142 file dari git.

Manifest adalah kontrak dengan <x-img>: komponen Blade membaca lebar yang
benar-benar ada, jadi tidak ada varian yang direferensikan tapi tak pernah
dibuat. Dimensi asli ikut dicatat supaya komponen bisa menulis width/height dan
menghilangkan layout shift.

    python3 tools/optimize_images.py [--tree assets|content] [--dry-run] [--force]

Butuh `cwebp` (libwebp) dan `sips` (bawaan macOS) di PATH.
"""

import argparse
import json
import pathlib
import shutil
import subprocess
import sys

ROOT = pathlib.Path(__file__).resolve().parent.parent

# Satu manifest untuk semua tree. Kuncinya URL publik, bukan path disk, karena
# itulah yang tertulis di view. Cover artikel dilayani dari /content-images/
# padahal filenya ada di content-migration/images/.
MANIFEST = ROOT / "data/image-variants.json"

# Tree yang dikenal: (folder sumber, folder keluaran, URL sumber, URL keluaran).
# URL dan path disk sengaja dipisah: gambar artikel ada di content-migration/
# tapi dilayani lewat symlink di public/ dengan nama yang sama sekali lain.
TREES = {
    "assets": ("assets/img", "assets/img-opt", "/assets/img", "/assets/img-opt"),
    "content": (
        "content-migration/images",
        "content-migration/images-opt",
        "/content-images",
        "/content-images-opt",
    ),
}

# Lebar yang benar-benar dipakai layout: kolom konten maksimal ~800 px, hero
# full-bleed ~1600 px pada layar 2x, kartu/thumbnail ~400 px. Lebih dari 1600
# tidak pernah tampil, jadi tidak pernah dibuat.
WIDTHS = (1600, 800, 400)

# JPEG foto tahan kompresi lebih keras daripada PNG yang isinya logo, teks, dan
# bidang warna rata, yang justru paling cepat terlihat rusak.
QUALITY = {".jpg": 80, ".jpeg": 80, ".png": 90}

# GIF bisa beranimasi dan SVG sudah vektor: keduanya bukan urusan skrip ini.
CONVERTIBLE = (".jpg", ".jpeg", ".png")


def dimensions(path: pathlib.Path) -> tuple[int, int]:
    out = subprocess.run(
        ["sips", "-g", "pixelWidth", "-g", "pixelHeight", str(path)],
        capture_output=True,
        text=True,
        check=True,
    ).stdout
    dims = {}
    for line in out.splitlines():
        if ":" in line:
            key, _, value = line.strip().partition(": ")
            if key in ("pixelWidth", "pixelHeight"):
                dims[key] = int(value)
    return dims["pixelWidth"], dims["pixelHeight"]


def target_widths(native: int) -> list[int]:
    """Lebar varian untuk satu gambar, tanpa pernah memperbesar.

    Gambar yang aslinya sudah lebih kecil dari lebar terkecil tetap dapat satu
    varian pada ukuran aslinya. Ikon dan logo kecil tetap ikut jadi WebP.
    """
    widths = {w for w in WIDTHS if w < native}
    widths.add(min(native, max(WIDTHS)))
    return sorted(widths, reverse=True)


def convert(src: pathlib.Path, dest: pathlib.Path, width: int, quality: int) -> None:
    dest.parent.mkdir(parents=True, exist_ok=True)
    subprocess.run(
        [
            "cwebp",
            "-quiet",
            "-q",
            str(quality),
            "-m",
            "6",
            "-resize",
            str(width),
            "0",
            str(src),
            "-o",
            str(dest),
        ],
        check=True,
    )


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--tree", default="assets", choices=sorted(TREES), help="tree yang dikonversi")
    parser.add_argument("--dry-run", action="store_true", help="hanya laporkan rencana")
    parser.add_argument("--force", action="store_true", help="tulis ulang varian yang sudah ada")
    args = parser.parse_args()

    for binary in ("cwebp", "sips"):
        if shutil.which(binary) is None:
            print(f"error: {binary} tidak ada di PATH", file=sys.stderr)
            return 1

    source, out, url_prefix, out_url = TREES[args.tree]
    tree, out_dir = ROOT / source, ROOT / out
    if not tree.is_dir():
        print(f"error: {tree} bukan folder", file=sys.stderr)
        return 1

    manifest = {}
    if MANIFEST.exists() and not args.force:
        manifest = json.loads(MANIFEST.read_text())

    sources = sorted(
        p for p in tree.rglob("*") if p.suffix.lower() in CONVERTIBLE and p.is_file()
    )
    if not sources:
        print(f"tidak ada JPEG/PNG di {source}")
        return 0

    before = after = 0
    written = skipped = 0

    for src in sources:
        rel = url_prefix + "/" + src.relative_to(tree).as_posix()
        native_w, native_h = dimensions(src)
        src_bytes = src.stat().st_size
        before += src_bytes

        variants = []
        for width in target_widths(native_w):
            dest = out_dir / src.relative_to(tree).parent / f"{src.stem}-{width}.webp"
            fresh = dest.exists() and dest.stat().st_mtime >= src.stat().st_mtime
            if args.dry_run:
                print(f"  {'lewat ' if fresh and not args.force else 'buat  '} {dest.relative_to(ROOT)}")
            elif fresh and not args.force:
                skipped += 1
            else:
                convert(src, dest, width, QUALITY[src.suffix.lower()])
                written += 1

            if dest.exists():
                variants.append(
                    {
                        "width": width,
                        "path": out_url + "/" + dest.relative_to(out_dir).as_posix(),
                        "bytes": dest.stat().st_size,
                    }
                )

        if variants:
            after += max(v["bytes"] for v in variants)
            manifest[rel] = {
                "width": native_w,
                "height": native_h,
                "bytes": src_bytes,
                "variants": variants,
            }
            largest = max(variants, key=lambda v: v["width"])
            if not args.dry_run:
                print(
                    f"{rel}\n"
                    f"    {native_w}x{native_h} {src_bytes / 1048576:7.2f} MB"
                    f"  ->  {largest['width']}px {largest['bytes'] / 1048576:6.2f} MB"
                    f"  ({100 - largest['bytes'] * 100 // max(src_bytes, 1)}% lebih kecil)"
                )

    if args.dry_run:
        print(f"\n{len(sources)} gambar, {sum(len(target_widths(dimensions(s)[0])) for s in sources)} varian direncanakan")
        return 0

    MANIFEST.parent.mkdir(parents=True, exist_ok=True)
    MANIFEST.write_text(json.dumps(manifest, indent=2, sort_keys=True) + "\n")

    print(
        f"\n{len(sources)} gambar: {written} varian ditulis, {skipped} dilewat (sudah baru)\n"
        f"varian terbesar per gambar: {before / 1048576:.1f} MB  ->  {after / 1048576:.1f} MB"
        f"  ({100 - after * 100 // max(before, 1)}% lebih kecil)\n"
        f"manifest: {MANIFEST.relative_to(ROOT)}"
    )
    return 0


if __name__ == "__main__":
    sys.exit(main())
