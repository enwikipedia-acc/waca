FROM php:8.2-fpm-bookworm AS base

ENV DEBIAN_FRONTEND="noninteractive"

# msmtp-mta: provides /usr/sbin/sendmail for PHP's mail()
RUN apt-get update \
    && apt-get install -y --no-install-recommends msmtp msmtp-mta \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# redirect writable paths to a single location for a readonly container image.
RUN ln -sf /tmp/msmtprc /etc/msmtprc

ADD https://github.com/mlocati/docker-php-extension-installer/releases/download/2.11.12/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions \
    && install-php-extensions pdo_mysql sockets \
    && docker-php-ext-enable opcache

WORKDIR /var/www/html

###############################################################################
FROM node:20-bookworm-slim AS js-builder

WORKDIR /var/www/html
COPY package*.json ./
RUN npm ci
COPY resources/scss/ ./resources/scss/
RUN npm run build-scss

###############################################################################
FROM base AS builder

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY composer.json /var/www/html
COPY composer.lock /var/www/html
RUN composer install --no-dev --no-progress --optimize-autoloader --no-interaction

COPY .git /var/www/html/.git

COPY --from=js-builder /var/www/html/resources/generated/ /var/www/html/resources/generated/
COPY --from=js-builder /var/www/html/node_modules/ /var/www/html/node_modules/

COPY . /var/www/html

RUN git config --global --add safe.directory /var/www/html \
    && git describe --always --dirty > VERSION

# Not everything gets compiled because we use "string:" templates for HTML titles
# RUN php docker/precompile-templates.php

###############################################################################
FROM base AS production

COPY --from=builder --chown=www-data:www-data /var/www/html /var/www/html

RUN mkdir -p templates_c errorlog \
    && sed -i "s|exec(\"git describe --always --dirty\")|'$(cat VERSION)'|" includes/Environment.php \
    && chown -R www-data:www-data /var/www/html/templates_c /var/www/html/errorlog

COPY docker/php-opcache-prod.ini /usr/local/etc/php/conf.d/opcache-production.ini
COPY docker/php-redis-session.ini /usr/local/etc/php/conf.d/redis-session.ini
RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini \
    && install-php-extensions redis

USER www-data

ENV SMARTY_COMPILE_CHECK=off

# SIGQUIT triggers php-fpm graceful stop (finish in-flight requests)
STOPSIGNAL SIGQUIT

EXPOSE 9000

ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]

###############################################################################
FROM base AS development

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip npm \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions xdebug

# Composer placed at the path the dev entrypoint expects
COPY --from=composer:2 /usr/bin/composer /opt/composer.phar

COPY docker/msmtprc /tmp/msmtprc
RUN chmod 600 /tmp/msmtprc

ENV XDEBUG_CONFIG="client_host=host.docker.internal client_port=9003 discover_client_host=false"
ENV XDEBUG_MODE="develop,debug,profile"
ENV DEVELOPMENT_MODE=1

EXPOSE 9000

ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]
