FROM php:8.4-cli-bookworm

ARG APP_UID=1000
ARG APP_GID=1000

RUN apt-get update \
    && apt-get install --no-install-recommends --yes \
        curl \
        git \
        libonig-dev \
        libpq-dev \
        libxml2-dev \
        unzip \
    && docker-php-ext-install -j"$(nproc)" bcmath dom mbstring pcntl pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN groupadd --gid "${APP_GID}" app \
    && useradd --uid "${APP_UID}" --gid "${APP_GID}" --create-home --shell /bin/bash app

WORKDIR /var/www/html

COPY --chown=app:app composer.json composer.lock ./

USER app

RUN composer install --no-interaction --no-progress --prefer-dist --no-scripts

USER root

COPY --chmod=755 docker/entrypoint.sh /usr/local/bin/backend-entrypoint

USER app

EXPOSE 8000

ENTRYPOINT ["backend-entrypoint"]
