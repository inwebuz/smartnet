#!/bin/sh
set -eu

composer install --no-interaction --prefer-dist

if grep -Eq '^APP_KEY=[[:space:]]*$' .env; then
    php artisan key:generate --no-interaction
fi

attempt=0
until php -r 'try { new PDO("mysql:host=".getenv("DB_HOST").";port=".getenv("DB_PORT").";dbname=".getenv("DB_DATABASE"), getenv("DB_USERNAME"), getenv("DB_PASSWORD")); } catch (Throwable $exception) { exit(1); }'; do
    attempt=$((attempt + 1))
    if [ "$attempt" -ge 30 ]; then
        echo 'MariaDB did not become available within 60 seconds.' >&2
        exit 1
    fi
    sleep 2
done

php artisan migrate --force --no-interaction
php artisan db:seed --force --no-interaction
