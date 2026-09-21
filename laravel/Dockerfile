# syntax=docker/dockerfile:1

FROM composer:2 AS vendor

WORKDIR /app

COPY . .
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader

FROM node:25-bookworm-slim AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY resources ./resources
COPY vite.config.js ./
COPY --from=vendor /app/vendor/tightenco/ziggy /app/vendor/tightenco/ziggy
RUN npm run build

FROM dunglas/frankenphp:1-php8.4-bookworm AS runtime

WORKDIR /app

RUN apt-get update \
    && apt-get install -y --no-install-recommends libcap2-bin \
    && install-php-extensions pdo_sqlite intl zip opcache \
    && rm -rf /var/lib/apt/lists/* \
    && groupadd --gid 10001 app \
    && useradd --uid 10001 --gid app --no-create-home --home-dir /nonexistent --shell /usr/sbin/nologin app

COPY --from=vendor /app /app
COPY --from=assets /app/public/build /app/public/build
COPY Caddyfile /etc/frankenphp/Caddyfile

RUN mkdir -p \
        /tmp/drm \
        /app/storage/framework/cache \
        /app/storage/framework/sessions \
        /app/storage/framework/views \
        /app/storage/logs \
        /app/bootstrap/cache \
    && chown -R app:app \
        /tmp/drm \
        /app/storage/framework/cache \
        /app/storage/framework/sessions \
        /app/storage/framework/views \
        /app/storage/logs \
        /app/bootstrap/cache \
    && chmod 0555 /app/scripts/start-demo.sh \
    && setcap -r /usr/local/bin/frankenphp

ENV APP_ENV=production \
    APP_DEBUG=false \
    DEMO_MODE=true \
    CACHE_STORE=file \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/tmp/drm/database.sqlite \
    HOME=/tmp/drm \
    XDG_CONFIG_HOME=/tmp/drm/config \
    XDG_DATA_HOME=/tmp/drm/data \
    PORT=8080

USER app

EXPOSE 8080

ENTRYPOINT ["/app/scripts/start-demo.sh"]
