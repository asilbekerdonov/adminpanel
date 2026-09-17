#!/bin/sh
set -e

echo "🚀  Laravel entrypoint starting..."

# ── 1. Ожидание доступности MySQL (если настроен хост) ────────────────────────
if [ -n "$DB_HOST" ] && [ "$DB_CONNECTION" = "mysql" ]; then
    DB_PORT="${DB_PORT:-3306}"
    echo "⏳  Ожидание подключения к MySQL ($DB_HOST:$DB_PORT)..."
    max_tries=30
    counter=0
    until nc -z -w 2 "$DB_HOST" "$DB_PORT" 2>/dev/null || [ $counter -eq $max_tries ]; do
        sleep 1
        counter=$((counter + 1))
    done

    if [ $counter -eq $max_tries ]; then
        echo "⚠️  Предупреждение: MySQL ($DB_HOST:$DB_PORT) не ответил за $max_tries сек. Пробуем продолжить..."
    else
        echo "✅  MySQL доступен!"
    fi
fi

# ── 2. Определение режима запуска ─────────────────────────────────────────────
# Если аргументы не переданы или передан php-fpm — это основной веб-контейнер
IS_WEB_SERVER=0
if [ $# -eq 0 ] || [ "$1" = "php-fpm" ]; then
    IS_WEB_SERVER=1
fi

# ── 3. Инициализация (только для основного веб-сервера) ───────────────────────
if [ $IS_WEB_SERVER -eq 1 ]; then
    # Генерация ключа (если не задан)
    if [ -z "$APP_KEY" ]; then
        echo "⚙️   APP_KEY не задан — генерирую..."
        php artisan key:generate --force
    fi

    # Кэширование для production
    if [ "$APP_ENV" = "production" ]; then
        echo "⚙️   Кэширование конфигурации (production)..."
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache
        php artisan event:cache
    fi

    # Миграции
    echo "🗄️   Запуск миграций..."
    php artisan migrate --force --no-interaction

    # Сидеры (опционально)
    if [ "${AUTO_SEED:-false}" = "true" ]; then
        echo "🌱  Запуск сидеров..."
        php artisan db:seed --force --no-interaction
    fi

    echo "✅  Веб-приложение готово к приёму запросов."
fi

# ── 4. Запуск переданной команды или php-fpm по умолчанию ────────────────────
if [ $# -gt 0 ]; then
    echo "▶️   Выполнение команды: $@"
    exec "$@"
else
    echo "▶️   Запуск PHP-FPM на порту 9000..."
    exec php-fpm
fi