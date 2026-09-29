# syntax=docker/dockerfile:1

# ── Tahap 1: aset frontend ────────────────────────────────────────────────────
# Dibangun di dalam image, bukan disalin dari mesin pengembang: public/build
# tidak dilacak git, dan aset yang dibangun di macOS tidak dijamin sama dengan
# yang dibutuhkan container Linux.
FROM node:22-alpine AS aset

WORKDIR /app/backend
COPY backend/package.json backend/package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY backend/vite.config.js ./
COPY backend/resources ./resources
# Pemindai Tailwind membaca Blade, jadi view harus ada sebelum build.
COPY backend/app ./app
COPY backend/config ./config
RUN npm run build

# ── Tahap 2: dependensi PHP ───────────────────────────────────────────────────
FROM composer:2 AS vendor

WORKDIR /app
COPY backend/composer.json backend/composer.lock ./
# Paket dev tidak punya urusan di server, dan ikut mengirimnya memperbesar
# permukaan yang bisa diserang. --ignore-platform-reqs dipakai karena image
# composer membawa PHP-nya sendiri; versi yang sesungguhnya dipakai ada di
# tahap berikutnya.
RUN composer install \
        --no-dev --no-scripts --no-autoloader \
        --prefer-dist --no-interaction --no-progress \
        --ignore-platform-reqs

# ── Tahap 3: runtime ──────────────────────────────────────────────────────────
# PHP 8.4 bukan pilihan gaya: 18 paket terkunci menuntutnya, dan 8.3 gagal pada
# composer install.
FROM php:8.4-fpm-alpine AS runtime

# nginx dan php-fpm berjalan di satu container, diatur supervisor.
#
# Memisahkannya jadi dua service terlihat lebih rapi, tapi di aplikasi ini
# justru menimbulkan dua masalah: nginx harus membaca berkas yang sama dengan
# PHP, sehingga 165 MB gambar ikut tersalin ke image kedua, dan berbagi lewat
# named volume membuat aset beku pada isi pertama, sehingga rebuild tidak
# pernah memperbaruinya.
# bash dan sqlite bukan untuk PHP, melainkan untuk deploy/backup.sh: skripnya
# ditulis untuk bash, dan menyalin basis data lewat `sqlite3 .backup` yang aman
# dijalankan selagi aplikasi menulis. Tanpa keduanya jadwal backup harian gagal
# tiap malam tanpa ada yang menyadarinya, dan itu baru ketahuan saat backupnya
# dibutuhkan.
RUN apk add --no-cache \
        nginx supervisor su-exec bash sqlite tzdata \
        icu-libs oniguruma libzip sqlite-libs libpng libjpeg-turbo freetype \
    && apk add --no-cache --virtual .bangun \
        icu-dev oniguruma-dev libzip-dev sqlite-dev libpng-dev libjpeg-turbo-dev freetype-dev $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install -j"$(nproc)" intl mbstring zip pdo_mysql pdo_sqlite gd opcache \
    && apk del .bangun

# Setelan opcache untuk produksi. validate_timestamps=0 berarti PHP tidak
# memeriksa perubahan berkas tiap permintaan; itu aman karena kode di dalam
# image tidak pernah berubah, dan container dibangun ulang saat ada perubahan.
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.max_accelerated_files=10000'; \
    } > /usr/local/etc/php/conf.d/opcache.ini \
    && { \
        echo 'upload_max_filesize=8M'; \
        echo 'post_max_size=12M'; \
        echo 'memory_limit=256M'; \
        echo 'expose_php=Off'; \
    } > /usr/local/etc/php/conf.d/aplikasi.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY --chown=www-data:www-data . .
COPY --from=vendor --chown=www-data:www-data /app/vendor ./backend/vendor
COPY --from=aset --chown=www-data:www-data /app/backend/public/build ./backend/public/build

# Autoloader dibuat di sini, dengan berkas aplikasi yang sudah lengkap.
RUN cd backend && composer dump-autoload --optimize --no-dev --no-scripts

COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint \
    && mkdir -p backend/storage/framework/cache backend/storage/framework/sessions \
               backend/storage/framework/views backend/storage/logs backend/bootstrap/cache \
               content-migration/images /app/data \
    && mkdir -p /var/lib/nginx/tmp /var/log/supervisor /run/nginx \
    && chown -R www-data:www-data backend/storage backend/bootstrap/cache content-migration /app/data \
                                  /var/lib/nginx /var/log/supervisor /run/nginx \
    && find assets content-migration backend/public -type f ! -perm -004 -exec chmod 644 {} +

# Tetap root supaya supervisor dapat menjalankan nginx; keduanya melepas hak ke
# www-data lewat setelan masing-masing.
# Jam container disamakan dengan jam aplikasi. Tanpa ini PHP memakai
# Asia/Jakarta sementara shell tetap UTC, sehingga nama berkas backup
# bertimestamp tujuh jam lebih awal daripada yang tertulis di panel — tepat saat
# seseorang harus memilih backup mana yang dipulihkan.
ENV TZ=Asia/Jakarta

EXPOSE 80
ENTRYPOINT ["entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
