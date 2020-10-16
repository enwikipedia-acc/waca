FROM php:7.3-apache-buster

# VOLUME /var/www/html
RUN apt-get update \
    && apt-get install git unzip -q -y --no-install-recommends \
    && apt-get clean

ADD https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions /usr/local/bin/
RUN chmod ug+x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions pdo_mysql xdebug

WORKDIR /opt
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && rm composer-setup.php

VOLUME /var/www/html/templates_c
VOLUME /var/www/html/errorlog

WORKDIR /var/www/html
COPY . /var/www/html

ENTRYPOINT /var/www/html/docker/entrypoint.sh
