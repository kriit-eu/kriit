FROM alpine:3.22

ARG PHPMYADMIN_VERSION=5.2.1
RUN apk add --no-cache \
      php84 php84-fpm php84-mysqli php84-json php84-session php84-exif \
      php84-mbstring php84-zip php84-opcache curl unzip tzdata \
 && ln -s /usr/bin/php84 /usr/bin/php \
 && curl -fsSL \
      https://files.phpmyadmin.net/phpMyAdmin/${PHPMYADMIN_VERSION}/phpMyAdmin-${PHPMYADMIN_VERSION}-all-languages.zip \
      -o /tmp/pma.zip \
 && unzip -q /tmp/pma.zip -d /var/www \
 && mv /var/www/phpMyAdmin-* /var/www/phpmyadmin \
 && rm /tmp/pma.zip

COPY config.inc.php /var/www/phpmyadmin/config.inc.php
WORKDIR /var/www/phpmyadmin
EXPOSE 80
# Use PHP built-in server
CMD ["php", "-S", "0.0.0.0:80", "-t", "/var/www/phpmyadmin"]