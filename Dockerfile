# ── Stage 1: Сборка фронтенда (Vite) ──────────────────────────────────────────
FROM node:22-alpine AS frontend-builder
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY resources resources
COPY vite.config.js tailwind.config.js postcss.config.js ./
RUN npm run build

# ── Stage 2: Установка PHP-зависимостей (Composer) ────────────────────────────
FROM composer:2.8 AS composer-builder
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --ignore-platform-reqs

COPY app app
COPY bootstrap bootstrap
COPY config config
COPY database database
COPY routes routes
COPY artisan ./
RUN composer dump-autoload --optimize --no-dev --no-interaction

# ── Stage 3: Production Runtime (PHP-FPM 8.4 Alpine) ──────────────────────────
FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

# Системные runtime-библиотеки
RUN apk add --no-cache \
    bash \
    curl \
    libpng \
    libjpeg-turbo \
    libwebp \
    libzip \
    oniguruma \
    icu-libs \
    freetype

# Временные сборочные зависимости для компиляции PHP-расширений
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    freetype-dev \
    linux-headers \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps \
    && rm -rf /tmp/pear

# Конфигурация PHP
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

# Копируем Composer CLI
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# Копируем исходный код приложения
COPY . .

# Копируем оптимизированный vendor из Stage 2
COPY --from=composer-builder /app/vendor ./vendor

# Копируем скомпилированные ассеты Vite из Stage 1
COPY --from=frontend-builder /app/public/build ./public/build

# Очистка локального bootstrap-кэша и сборка production package discovery
RUN rm -f bootstrap/cache/*.php \
    && php artisan package:discover --ansi

# Настройка прав доступа для пользователя www-data
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Entrypoint скрипт
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER www-data

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]