FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo pdo_pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

EXPOSE 9000

CMD ["sh", "-c", "composer install --no-interaction --optimize-autoloader && php artisan key:generate --force && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=9000"]
