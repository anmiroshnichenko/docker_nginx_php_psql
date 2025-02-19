# Add PHP-FPM base image
FROM php:8.2-fpm

# Install your extensions
# To connect to MySQL, add mysqli
#RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN apt-get update && \
    apt-get install -y libpq-dev && \
    docker-php-ext-install pdo pdo_pgsql pgsql
