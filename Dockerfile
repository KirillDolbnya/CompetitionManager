FROM php:8.3-fpm-bookworm

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/download/2.7.5/install-php-extensions /usr/local/bin/

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    zip \
    unzip \
    curl \
    git \
    libpng-dev libjpeg-dev libfreetype6-dev \
    default-mysql-client \
    tzdata && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

RUN install-php-extensions \
    @composer \
    mbstring \
    exif \
    intl \
    gd \
    bcmath \
    opcache \
    pcntl \
    zip \
    curl \
    pdo_mysql \
    imagick

RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/

ARG DOCKER_HOST_USER=app
ARG DOCKER_HOST_UID=1000
ARG DOCKER_HOST_GROUP=app
ARG DOCKER_HOST_GID=1000

RUN groupadd -g $DOCKER_HOST_GID $DOCKER_HOST_GROUP || true \
 && useradd -u $DOCKER_HOST_UID -g $DOCKER_HOST_GID -m -s /bin/bash $DOCKER_HOST_USER

USER $DOCKER_HOST_USER

EXPOSE 9000
