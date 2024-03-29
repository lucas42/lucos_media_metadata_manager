FROM lucas42/lucos_navbar:latest as navbar
FROM php:8-apache

WORKDIR /srv/metadata_manager

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN a2enmod rewrite
RUN echo "ServerName localhost\nServerAdmin webmaster@localhost" >> /etc/apache2/apache2.conf
COPY vhost.conf /etc/apache2/sites-available/000-default.conf

COPY src .
COPY --from=navbar lucos_navbar.js html/

ENV PORT 80
EXPOSE $PORT