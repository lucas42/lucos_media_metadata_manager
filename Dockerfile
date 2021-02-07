FROM php:8-apache

WORKDIR /srv/metadata_manager

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN a2enmod rewrite
COPY vhost.conf /etc/apache2/sites-available/000-default.conf

COPY src/. .

ENV PORT 80
EXPOSE $PORT