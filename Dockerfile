FROM php:8.2-cli

# Установка системных зависимостей
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Установка рабочей директории
WORKDIR /var/www/html

# Копирование composer файлов
COPY composer.json composer.lock ./

# Установка зависимостей PHP
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Копирование остальных файлов
COPY . .

# Установка прав доступа
RUN chmod -R 755 storage bootstrap/cache || true

# Открытие порта
EXPOSE 8000

# Команда по умолчанию
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
