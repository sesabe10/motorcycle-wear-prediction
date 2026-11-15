FROM php:8.4-cli

RUN apt-get update \
 && apt-get install -y --no-install-recommends libpq-dev libzip-dev unzip git \
 && docker-php-ext-install pdo_pgsql zip opcache \
 && echo "opcache.enable_cli=1" > /usr/local/etc/php/conf.d/opcache-cli.ini \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-dev \
 && sed -i 's/\r$//' docker/iniciar.sh \
 && chmod -R ug+rw storage bootstrap/cache

ENV PHP_CLI_SERVER_WORKERS=4
CMD ["sh", "docker/iniciar.sh"]
