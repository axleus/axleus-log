ARG PHP_VERSION=8.4.20

FROM php:${PHP_VERSION}-apache-bookworm AS base

# Copy Composer from the official Docker Hub repository to the local filesystem
COPY --from=composer:2.9.8 /usr/bin/composer /usr/bin/

# Install the database extensions for MySQL, PostgreSQL, and SQLite, their
# dependencies, and any other tools that are required for the environment to be
# used fully and completely.
RUN apt-get update \
    && apt-get install -y git \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apt-get clean

# Copy PHP ini configuration
COPY docker/php/conf.d/ /usr/local/etc/php/conf.d/

# Allow the www-data login so that it can run Composer instead of using root
RUN usermod -s /usr/bin/bash www-data

# Copy all of the files from the context to the current directory setting the
# correct owner
COPY --chown=www-data:www-data . .

RUN chmod +x bin/install-deps.sh

# Validate and install PHP's dependencies
RUN su --preserve-environment www-data --command "bin/install-deps.sh"