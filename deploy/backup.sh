#!/usr/bin/env bash
#
# Backup harian: basis data dan berkas yang diunggah dari admin.
#
# Yang tidak ikut: kode, vendor, node_modules, dan varian WebP. Semuanya bisa
# dibangun ulang dari repositori, jadi memasukkannya hanya membuat backup besar
# dan lambat tanpa menambah apa pun yang benar-benar bisa hilang.
#
# Yang ikut justru dua hal yang tidak ada di repositori mana pun: isi basis data
# dan gambar yang diunggah orang lewat panel admin.
#
# Pasang di cron server, setelah menyesuaikan BACKUP_DIR:
#   15 2 * * * /path/ke/deploy/backup.sh >> /var/log/hasnur-backup.log 2>&1
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
BACKUP_DIR="${BACKUP_DIR:-$ROOT/../backup-hasnur}"
SIMPAN_HARI="${BACKUP_RETENTION_DAYS:-30}"
CAP="$(date +%Y%m%d-%H%M%S)"

mkdir -p "$BACKUP_DIR"

nilai_env() {
    # Variabel lingkungan menang atas .env, dan urutan ini bukan selera.
    # Di Docker, DB_DATABASE ditetapkan compose sebagai /app/data/database.sqlite
    # dan tidak pernah ditulis ke backend/.env. Membaca .env lebih dulu berarti
    # skrip ini jatuh ke nilai bawaan backend/database/database.sqlite, lalu
    # mencadangkan berkas yang tidak pernah dipakai siapa pun sambil melaporkan
    # "Selesai". Backup yang salah lebih berbahaya daripada backup yang gagal.
    local dari_lingkungan="${!1:-}"
    if [ -n "$dari_lingkungan" ]; then
        printf '%s\n' "$dari_lingkungan"
        return
    fi

    # || true wajib: dengan set -o pipefail, grep yang tidak menemukan kunci
    # akan menghentikan seluruh skrip.
    grep -E "^$1=" "$ROOT/backend/.env" 2>/dev/null | head -1 | cut -d= -f2- | tr -d '"' | tr -d "'" | xargs || true
}

echo "==> Basis data"
KONEKSI="$(nilai_env DB_CONNECTION)"
case "$KONEKSI" in
    sqlite)
        SUMBER="$(nilai_env DB_DATABASE)"
        SUMBER="${SUMBER:-$ROOT/backend/database/database.sqlite}"
        # .backup milik sqlite3 aman dijalankan selagi aplikasi menulis;
        # menyalin berkasnya dengan cp bisa menghasilkan salinan setengah jadi.
        sqlite3 "$SUMBER" ".backup '$BACKUP_DIR/db-$CAP.sqlite'"
        gzip -f "$BACKUP_DIR/db-$CAP.sqlite"
        ;;
    mysql|mariadb)
        mysqldump \
            --host="$(nilai_env DB_HOST)" --port="$(nilai_env DB_PORT)" \
            --user="$(nilai_env DB_USERNAME)" --password="$(nilai_env DB_PASSWORD)" \
            --single-transaction --quick \
            "$(nilai_env DB_DATABASE)" | gzip > "$BACKUP_DIR/db-$CAP.sql.gz"
        ;;
    *)
        echo "!! DB_CONNECTION '$KONEKSI' belum didukung skrip ini."
        exit 1
        ;;
esac

echo "==> Gambar yang diunggah"
# content-migration/images tidak dilacak git, jadi isinya hanya ada di server.
tar -czf "$BACKUP_DIR/unggahan-$CAP.tar.gz" -C "$ROOT" content-migration/images 2>/dev/null || {
    echo "   (folder unggahan belum ada, dilewat)"
    rm -f "$BACKUP_DIR/unggahan-$CAP.tar.gz"
}

echo "==> Membuang backup lebih tua dari $SIMPAN_HARI hari"
find "$BACKUP_DIR" -maxdepth 1 -name 'db-*' -mtime "+$SIMPAN_HARI" -delete
find "$BACKUP_DIR" -maxdepth 1 -name 'unggahan-*' -mtime "+$SIMPAN_HARI" -delete

echo "==> Selesai"
ls -lh "$BACKUP_DIR" | tail -5
