#!/usr/bin/env bash
#
# Membuat tiga symlink di backend/public/ yang tidak dilacak git.
#
# Isinya berada di luar backend/, jadi ketiganya tidak pernah ikut terkirim,
# dan tanpa mereka seluruh gambar situs menjawab 404 sementara halamannya tetap
# tampil, jenis kerusakan yang lolos dari pemeriksaan sekilas. Dipakai bersama
# oleh CI dan langkah setelah deploy supaya keduanya tidak bisa menyimpang.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"

mkdir -p "$ROOT/content-migration/images"
ln -sfn "$ROOT/assets" "$ROOT/backend/public/assets"
ln -sfn "$ROOT/content-migration/images" "$ROOT/backend/public/content-images"
ln -sfn "$ROOT/content-migration/images-opt" "$ROOT/backend/public/content-images-opt"
