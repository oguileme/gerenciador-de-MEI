#!/usr/bin/env sh

set -eu

if [ ! -f .env ]; then
    cp .env.example .env
fi

composer install --no-interaction --no-progress --prefer-dist

if ! grep -Eq '^APP_KEY=.+$' .env; then
    php artisan key:generate --force --no-interaction
fi

echo "Aguardando o PostgreSQL ficar disponível..."

until php -r '
try {
    new PDO(
        sprintf("pgsql:host=%s;port=%s;dbname=%s", getenv("DB_HOST"), getenv("DB_PORT"), getenv("DB_DATABASE")),
        getenv("DB_USERNAME"),
        getenv("DB_PASSWORD")
    );
} catch (Throwable) {
    exit(1);
}
'; do
    sleep 2
done

php artisan migrate --force --no-interaction

cd public

exec php -S 0.0.0.0:8000 \
    ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php
