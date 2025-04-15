FROM php:8.4-cli

WORKDIR /var/www

RUN apt-get update
RUN apt-get install -y git curl libzip-dev libsqlite3-dev gnupg libonig-dev

RUN apt-get autoclean && apt-get autoremove && apt-get autopurge && rm -rf /var/lib/apt/lists/*

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash -

RUN apt-get install -y nodejs

RUN apt-get autoclean && apt-get autoremove && apt-get autopurge && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_sqlite mbstring zip

COPY --from=composer/composer:latest-bin /composer /usr/bin/composer

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --prefer-dist

RUN npm install && npm run build

RUN cp .env.prod .env && php artisan key:generate && php artisan migrate

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 8080

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]

