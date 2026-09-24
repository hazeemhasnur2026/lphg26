#!/usr/bin/env bash
#
# Dijalankan di server setelah berkas selesai dikirim.
#
# Berhenti pada kesalahan pertama: separuh deploy yang dianggap berhasil lebih
# berbahaya daripada deploy yang gagal dengan jelas.
set -euo pipefail

cd "$(dirname "$0")/.."
ROOT="$(pwd)"

echo "==> Symlink di public/"
bash "$ROOT/deploy/link-public-dirs.sh"

cd "$ROOT/backend"

echo "==> Memeriksa environment"
if [ ! -f .env ]; then
    echo "!! backend/.env belum ada di server."
    echo "   Salin dari deploy/env.production.example, lalu isi APP_KEY dan setelan surel."
    exit 1
fi

# Nilai yang salah di sini tidak membuat situs gagal jalan, justru itu bahayanya:
# situs tetap tampil normal sambil membocorkan jejak tumpukan beserta isi
# variabel lingkungan ke siapa pun yang memicu galat. Deploy dihentikan.
nilai() { grep -E "^$1=" .env | head -1 | cut -d= -f2- | tr -d '"' | tr -d "'" | xargs; }

gagal=0
periksa() {
    if [ "$(nilai "$1")" != "$2" ]; then
        echo "!! $1 di server bernilai '$(nilai "$1")', seharusnya '$2'"
        gagal=1
    fi
}

periksa APP_ENV production
periksa APP_DEBUG false
periksa SESSION_SECURE_COOKIE true

if [ -z "$(nilai APP_KEY)" ]; then
    echo "!! APP_KEY kosong. Jalankan: php artisan key:generate"
    gagal=1
fi

if [ "$gagal" -ne 0 ]; then
    echo
    echo "Deploy dihentikan. Perbaiki backend/.env di server, contohnya ada di"
    echo "deploy/env.production.example, lalu jalankan ulang."
    exit 1
fi

echo "==> Izin tulis"
# Laravel menulis cache view, log, dan sesi ke sini; SQLite menulis berkas
# databasenya. Salah izin di sini muncul sebagai 500 tanpa penjelasan.
mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chmod -R ug+rw storage bootstrap/cache

echo "==> Migrasi database"
php artisan migrate --force

echo "==> Menyalakan cache"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "==> Selesai"
php artisan about --only=environment | head -5
