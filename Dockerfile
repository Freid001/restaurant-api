FROM ubuntu:xenial

LABEL maintainer = "freid001"

ENV COMPOSER_ALLOW_SUPERUSER 1

# install dependencies
RUN apt-get update
RUN apt-get install -y software-properties-common python-software-properties
RUN apt-get install -y language-pack-en-base
RUN LC_ALL=en_US.UTF-8 add-apt-repository ppa:ondrej/php

# setup php
RUN apt-get update && \
    apt-get install -y nginx \
    php7.1 \
    php7.1-fpm \
    php7.1-cli \
    php7.1-common \
    php7.1-json \
    php7.1-opcache \
    php7.1-mysql \
    php7.1-mbstring \
    php7.1-gd \
    php7.1-imap \
    php7.1-ldap \
    php7.1-dev \
    php7.1-intl \
    php7.1-gd \
    php7.1-curl \
    php7.1-zip \
    php7.1-xml \
    curl

RUN phpenmod pdo_mysql

RUN cd ~ && \
    curl -sS https://getcomposer.org/installer -o composer-setup.php && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer

COPY entrypoint.sh /opt/app/bin/entrypoint.sh
COPY ./ /var/www/app

WORKDIR /opt/app/bin

RUN cd /var/www/app && composer install

EXPOSE 8000

ENTRYPOINT ["/bin/sh", "./entrypoint.sh"]

ARG VERSION
ARG GHASH
ARG BUILD_TIME

ENV VERSION $VERSION
ENV GHASH $GHASH
ENV BUILD_TIME $BUILD_TIME
