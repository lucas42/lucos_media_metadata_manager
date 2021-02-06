FROM php:8-apache

WORKDIR /var/www/html/

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN a2enmod rewrite

COPY src/. .

ENV PORT 80
EXPOSE $PORT