FROM node:24-alpine AS js-build

WORKDIR /srv/client
COPY client/package* ./
RUN npm install
COPY client/*.js ./

RUN npm run build

FROM php:8.4.13-apache-trixie

WORKDIR /srv/metadata_manager

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN a2enmod rewrite
RUN echo "ServerName localhost\nServerAdmin webmaster@localhost" >> /etc/apache2/apache2.conf
COPY vhost.conf /etc/apache2/sites-available/000-default.conf

COPY src .
COPY --from=js-build /srv/client/dist/script.js html/

ENV PORT 80
EXPOSE $PORT