FROM php:8.2-fpm

RUN apt-get update -y \
    && apt-get install -y --no-install-recommends git zip unzip \
    && apt-get clean -y \
    && rm -rf /var/lib/apt/lists/* /usr/share/man/* /usr/share/doc/* /var/cache/* /var/log/* /tmp/*

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions intl pdo_pgsql opcache xdebug

COPY --from=composer /usr/bin/composer /usr/local/bin/composer
RUN curl https://get.symfony.com/cli/installer | bash && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# try to synchronise uids with host linux user to avoid ownership issues
RUN chown www-data:www-data /var/www && usermod -u 1000 www-data && groupmod -g 1000 www-data
USER www-data