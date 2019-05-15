FROM composer:latest

MAINTAINER Zolana Nsakala <zolana.nsakala@ionos.com>

RUN apk add --update ca-certificates mysql mysql-client \
    && docker-php-ext-install pdo_mysql mysqli \
    && rm -f /var/cache/apk/*
