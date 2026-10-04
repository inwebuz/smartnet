FROM php:8.5-cli-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends libicu-dev libonig-dev libsqlite3-dev libzip-dev unzip \
    && docker-php-ext-install bcmath intl mbstring pcntl pdo_mysql pdo_sqlite zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

COPY docker/bootstrap.sh /usr/local/bin/smartnet-bootstrap
RUN chmod +x /usr/local/bin/smartnet-bootstrap

EXPOSE 8000
