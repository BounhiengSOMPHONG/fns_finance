FROM php:8.4-cli-bookworm AS vendor

WORKDIR /app

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libzip-dev \
        libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        gd \
        intl \
        mbstring \
        pdo_mysql \
        zip \
        opcache \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_MEMORY_LIMIT=-1

COPY composer.json composer.lock ./
COPY artisan ./
COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY resources ./resources
COPY routes ./routes

RUN composer clear-cache \
    && composer install \
        --no-dev \
        --prefer-dist \
        --no-interaction \
        --optimize-autoloader \
        --no-progress \
    || composer install \
        --no-dev \
        --prefer-source \
        --no-interaction \
        --optimize-autoloader \
        --no-progress


FROM node:22-bookworm-slim AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
COPY public ./public

RUN npm ci && npm run build


FROM php:8.4-cli-bookworm AS app

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libzip-dev \
        libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        gd \
        intl \
        mbstring \
        pdo_mysql \
        zip \
        opcache \
    && rm -rf /var/lib/apt/lists/*

COPY . .

COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

RUN rm -f public/hot \
    && rm -f bootstrap/cache/*.php \
    && mkdir -p \
        storage/app/public \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache \
    && php artisan storage:link || true

EXPOSE 8000

CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]
