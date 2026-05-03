FROM dunglas/frankenphp:latest-php8.2-alpine

# Instal driver MySQL dan teman-temannya
RUN install-php-extensions pdo_mysql gd intl zip opcache

WORKDIR /app
COPY . .
RUN chown -R www-data:www-data storage bootstrap/cache