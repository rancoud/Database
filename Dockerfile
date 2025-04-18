ARG PHPVERSION="8.4"
FROM php:$PHPVERSION-cli-alpine

RUN apk --update --no-cache add \
    libpq \
    mysql-client \
    postgresql-client \
    postgresql-dev \
  && rm -rf /tmp/* /var/cache/apk/*

RUN docker-php-ext-install \
  pdo_mysql \
  pdo_pgsql

RUN apk add --no-cache \
    $PHPIZE_DEPS \
    linux-headers \
    && pecl install xdebug-3.4.2 \
    && docker-php-ext-enable xdebug \
    && rm -rf /tmp/* /var/cache/apk/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

WORKDIR /app

COPY composer.json .
COPY composer.lock .
RUN composer validate
RUN composer install --no-interaction --no-progress

COPY . .

ENTRYPOINT ["./entrypoint.sh"]
