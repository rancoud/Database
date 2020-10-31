ARG PHPVERSION="7.4"

FROM php:$PHPVERSION-cli-alpine

RUN apk --update --no-cache add \
    libpq \
    postgresql-dev \
    mysql-client \
    && rm -rf /tmp/* /var/cache/apk/*

RUN docker-php-ext-install pdo_mysql pdo_pgsql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

WORKDIR /app
COPY composer.json .
COPY composer.lock .

RUN composer install

COPY . .

ENTRYPOINT ["./entrypoint.sh"]