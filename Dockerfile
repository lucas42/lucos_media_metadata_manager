FROM php:8-apache

WORKDIR /var/www/html/



COPY src/. .

ENV PORT 80
EXPOSE $PORT