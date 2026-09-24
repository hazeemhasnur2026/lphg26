#!/bin/sh
#
# Dijalankan tiap container menyala, sebelum php-fpm.
#
# Yang dikerjakan di sini hanya hal yang bergantung pada keadaan saat berjalan:
# symlink ke volume, migrasi, dan cache yang memuat nilai dari .env. Sisanya
# sudah selesai saat image dibangun.
set -e

cd /app

# Entrypoint berjalan sebagai root supaya supervisor dapat menjalankan nginx,
# tetapi php-fpm melayani permintaan sebagai www-data. Berkas yang dibuat root
# di sini akan tampak hanya-baca bagi php-fpm, dan gejalanya menyesatkan:
# "attempt to write a readonly database" pada basis data yang izinnya terlihat
# normal. Karena itu seluruh perintah artisan dijalankan sebagai www-data.
SEBAGAI="su-exec www-data"

# Tiga symlink ini tidak dilacak git karena menunjuk ke luar backend/. Tanpa
# mereka seluruh gambar situs menjawab 404 sementara halamannya tetap tampil.
sh deploy/link-public-dirs.sh

cd backend

if [ -z "${APP_KEY:-}" ]; then
    echo "!! APP_KEY belum disetel. Jalankan sekali:"
    echo "   docker compose run --rm app php artisan key:generate --show"
    echo "   lalu simpan hasilnya di .env atau sebagai variabel lingkungan."
    exit 1
fi

# SQLite butuh berkasnya ada sebelum migrasi. Berkas ini hidup di volume,
# jadi isinya bertahan walau image dibangun ulang.
# Berkas SQLite sengaja tidak diletakkan di backend/database: folder itu berisi
# migrasi, factory, dan seeder, yang merupakan kode. Memasang volume di atasnya
# membekukan ketiganya pada salinan pertama, sehingga migrasi baru tidak pernah
# terlihat setelah image dibangun ulang.
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    DB_PATH="${DB_DATABASE:-/app/data/database.sqlite}"
    mkdir -p "$(dirname "$DB_PATH")"
    if [ -f "$DB_PATH" ]; then
        DB_BARU=0
    else
        touch "$DB_PATH"
        DB_BARU=1
    fi
    # SQLite juga menulis berkas -wal dan -journal di sebelah basis datanya,
    # jadi foldernya ikut harus bisa ditulis.
    chown -R www-data:www-data "$(dirname "$DB_PATH")"
fi

chown -R www-data:www-data storage bootstrap/cache

# Hanya satu container yang boleh bermigrasi. Keduanya menyala bersamaan dan
# berbagi basis data yang sama; tanpa pembatasan ini keduanya berlomba dan yang
# kalah berhenti dengan "table already exists".
if [ "${LEWATI_MIGRASI:-0}" = "1" ]; then
    echo "Migrasi dilewati di container ini."
else
    $SEBAGAI php artisan migrate --force

    # Basis data yang baru dibuat masih kosong: migrasi hanya membuat tabel.
    # Tanpa langkah ini situs menyala dengan nol artikel, nol unit usaha, dan
    # nol banner, yang tampak seperti kerusakan padahal hanya belum terisi.
    if [ "${DB_BARU:-0}" = "1" ]; then
        $SEBAGAI php artisan db:seed --force
        $SEBAGAI php artisan import:articles
        echo
        echo "Basis data terisi. Akun panel admin belum ada, buat sekali dengan:"
        echo "   docker compose exec app php artisan make:filament-user"
        echo
    fi
fi

# Cache dibuat saat berjalan, bukan saat build: isinya memuat nilai dari
# lingkungan, dan menyimpannya di image berarti membekukan nilai milik mesin
# yang membangunnya.
$SEBAGAI php artisan config:cache
$SEBAGAI php artisan route:cache
$SEBAGAI php artisan view:cache
$SEBAGAI php artisan event:cache

exec "$@"
