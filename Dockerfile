FROM php:5.6


# Install dependencies
RUN apt-get update \
&& apt-get install -y zip \
&& apt-get clean \
&& rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN cd /usr/local/bin \
&& php -r "readfile('https://getcomposer.org/installer');" | php \
&& ln -sf /usr/local/bin/composer.phar /usr/local/bin/composer


# Install php-webdriver and PHPUnit
COPY composer.json /wd/composer.json
RUN cd /wd \
&& composer install \
&& ln -sf /wd/vendor/phpunit/phpunit/phpunit /usr/local/bin/phpunit

RUN apt-get update && apt-get install -y zlib1g-dev \
    && docker-php-ext-install zip


RUN composer require lmc/steward 2.3.3