FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
        icu-dev \
        libpq-dev \
    && docker-php-ext-install -j"$(nproc)" \
        intl \
        opcache \
        pdo_pgsql \
    && rm -rf /tmp/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

CMD ["php-fpm"]
