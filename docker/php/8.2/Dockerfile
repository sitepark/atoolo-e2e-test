FROM php:8.2-apache

RUN set -ex; \
   apt-get update; \
   apt-get -y --no-install-recommends install git unzip libzip-dev libicu-dev vim; \
   mkdir -p /apps /srv/sitepark/ies-webnode/config; \
   chmod a+rwx /apps /srv/sitepark/ies-webnode/config; \
   rm -rf /var/lib/apt/lists/*;


# PHP initialization
RUN set -ex; \
   pecl install xdebug; \
   docker-php-ext-enable \
   xdebug; \
   docker-php-ext-configure \
   intl; \
   docker-php-ext-install \
   intl \
   zip \
   sysvsem \
   opcache; \
   cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini;

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN set -ex; \
   mkdir -p /apps/.composer; \
   chown www-data /apps/.composer
ENV COMPOSER_HOME /apps/.composer

# Symfony-CLI
RUN set -ex; \
   curl -sS https://get.symfony.com/cli/installer | bash; \
   mv /root/.symfony5/bin/symfony /usr/local/bin/symfony;

# .bashrc
COPY root/.bashrc /root/.bashrc

WORKDIR /apps