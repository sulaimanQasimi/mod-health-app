# syntax=docker/dockerfile:1

############################
# Clone public repository
############################
FROM alpine/git AS git-source
ARG GIT_REPO=https://github.com/sulaimanQasimi/mod-health-app.git
ARG GIT_BRANCH=main
ARG GIT_REF
RUN if [ -n "$GIT_REF" ]; then \
        git clone "$GIT_REPO" /src && cd /src && git fetch --depth 1 origin "$GIT_REF" && git checkout "$GIT_REF"; \
    else \
        git clone --depth 1 --branch "$GIT_BRANCH" "$GIT_REPO" /src; \
    fi

############################
# Composer dependencies
############################
FROM php:8.3-cli-bookworm AS vendor

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libicu-dev \
        libonig-dev \
        libxml2-dev \
        libcurl4-openssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        curl \
        exif \
        gd \
        intl \
        mbstring \
        pcntl \
        pdo_mysql \
        xml \
        zip \
    && apt-get purge -y --auto-remove \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY --from=git-source /src/composer.json /src/composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader \
    --no-progress
COPY --from=git-source /src/ .
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-progress \
    && composer dump-autoload --optimize

############################
# Frontend assets
############################
FROM node:22-bookworm-slim AS assets
WORKDIR /app
COPY --from=git-source /src/package.json /src/package-lock.json ./
RUN echo "legacy-peer-deps=true" > .npmrc \
    && npm ci --no-audit --no-fund --legacy-peer-deps
COPY --from=git-source /src/vite.config.js /src/tsconfig.json /src/tsconfig.node.json /src/postcss.config.js ./
COPY --from=git-source /src/resources ./resources
COPY --from=git-source /src/public ./public
RUN npm run build

############################
# Production PHP-FPM
############################
FROM php:8.3-fpm-bookworm AS production-base

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    APP_SOURCE=/opt/mod-health-app

RUN apt-get update && apt-get install -y --no-install-recommends \
        gosu \
        git \
        unzip \
        curl \
        nodejs \
        npm \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libicu-dev \
        libonig-dev \
        libxml2-dev \
        libcurl4-openssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        curl \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        xml \
        zip \
    && apt-get purge -y --auto-remove \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY --from=git-source /src/docker/php/php.ini /usr/local/etc/php/conf.d/99-laravel.ini
COPY --from=git-source /src/docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]

FROM production-base AS production
COPY --from=vendor --chown=www-data:www-data /app /opt/mod-health-app
COPY --from=assets --chown=www-data:www-data /app/public/build /opt/mod-health-app/public/build
RUN cp -a /opt/mod-health-app/. /var/www/html/ \
    && mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chown -R www-data:www-data /var/www/html /opt/mod-health-app
