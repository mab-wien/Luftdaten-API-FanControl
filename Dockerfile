FROM php:7.2-apache

COPY htdocs/ /var/www/html/
# VOLUME htdocs /var/www/html

EXPOSE 80
