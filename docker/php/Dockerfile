FROM php:7.0-cli

WORKDIR /root/
RUN apt-get update && apt-get install -y wget libssl-dev git
RUN wget https://getcomposer.org/composer.phar && mv composer.phar /usr/bin/composer && chmod +x /usr/bin/composer
RUN yes | pecl install mongodb && docker-php-ext-enable mongodb

WORKDIR /var/www
CMD composer install && php -S 0.0.0.0:8080 /var/www/web/index.php