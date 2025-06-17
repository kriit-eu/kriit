# syntax=docker/dockerfile:1.7
FROM alpine:3.22

RUN apk add --no-cache \
    php84 php84-fpm php84-gd php84-intl php84-opcache php84-bcmath \
    php84-mysqli php84-pdo_mysql php84-zip php84-session php84-json \
    php84-mbstring php84-openssl php84-curl php84-dom php84-xml \
    php84-xmlwriter php84-tokenizer php84-ctype php84-iconv \
    libavif-dev libjpeg-turbo-dev libpng-dev freetype-dev icu-data-full icu-dev \
    git curl unzip bash \
    mariadb-client \
    && ln -s /usr/bin/php84 /usr/bin/php \
    && ln -s /usr/sbin/php-fpm84 /usr/sbin/php-fpm

# Configure PHP-FPM to listen on TCP instead of unix socket
RUN sed -i 's|listen = 127.0.0.1:9000|listen = 0.0.0.0:9000|g' /etc/php84/php-fpm.d/www.conf \
    && sed -i 's/;listen.owner = nobody/listen.owner = nobody/g' /etc/php84/php-fpm.d/www.conf \
    && sed -i 's/;listen.group = nobody/listen.group = nobody/g' /etc/php84/php-fpm.d/www.conf \
    && sed -i 's/;listen.mode = 0660/listen.mode = 0666/g' /etc/php84/php-fpm.d/www.conf \
    && sed -i 's/user = nobody/user = nobody/g' /etc/php84/php-fpm.d/www.conf \
    && sed -i 's/group = nobody/group = nobody/g' /etc/php84/php-fpm.d/www.conf \
    && echo "catch_workers_output = yes" >> /etc/php84/php-fpm.d/www.conf \
    && echo "php_flag[display_errors] = on" >> /etc/php84/php-fpm.d/www.conf \
    && echo "php_admin_value[error_log] = /proc/self/fd/2" >> /etc/php84/php-fpm.d/www.conf \
    && echo "php_admin_flag[log_errors] = on" >> /etc/php84/php-fpm.d/www.conf

# Composer
ENV COMPOSER_VERSION=2.8.6
RUN curl -fsSL https://getcomposer.org/download/${COMPOSER_VERSION}/composer.phar \
    -o /usr/local/bin/composer && chmod +x /usr/local/bin/composer

# Copy PHP configuration for development
COPY dev.ini /etc/php84/conf.d/99-dev.ini

WORKDIR /var/www/html
EXPOSE 9000
CMD ["php-fpm", "-F"]