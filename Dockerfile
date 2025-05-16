FROM php:8.0-apache

# Cài đặt các extensions PHP cần thiết
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Cài đặt các công cụ cần thiết
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip

# Cấu hình Apache
RUN a2enmod rewrite
RUN a2enmod headers

# Copy cấu hình Virtual Host tùy chỉnh
COPY ./apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Copy source code vào container
COPY . /var/www/html/
WORKDIR /var/www/html/

# Phân quyền cho thư mục
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
