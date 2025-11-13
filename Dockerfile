FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    unzip \
    git \
    curl

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Создаем папки
RUN mkdir -p Helpers Models

# Копируем ВСЕ файлы из www
COPY ./www/ ./

# Устанавливаем зависимости
RUN composer install --no-dev --optimize-autoloader

# Устанавливаем правильные права
RUN chown -R www-data:www-data /var/www/html

CMD ["php-fpm"]