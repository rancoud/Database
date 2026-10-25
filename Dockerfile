ARG PHPVERSION="8.4"
FROM php:$PHPVERSION-cli-alpine

ARG PHPVERSION

RUN apk --update --no-cache add \
    libpq \
    mysql-client \
    postgresql-client \
    postgresql-dev \
    oniguruma-dev \
  && rm -rf /tmp/* /var/cache/apk/*

RUN docker-php-ext-install \
  mbstring \
  pdo_mysql \
  pdo_pgsql

RUN apk add --no-cache \
    $PHPIZE_DEPS \
    linux-headers \
    libtool \
    curl \
    git \
    && curl -fsSL \
        https://github.com/php/pie/releases/latest/download/pie.phar \
        -o /usr/local/bin/pie \
    && chmod +x /usr/local/bin/pie \
    && case "$PHPVERSION" in \
            8.6*) pie install "xdebug/xdebug:3.6.0alpha1@alpha" ;; \
            *)    pie install "xdebug/xdebug:3.5.3" ;; \
        esac \
    && php -v \
    && rm -rf /tmp/* /var/cache/apk/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

WORKDIR /app

COPY composer.json .
COPY composer.lock .
RUN composer validate --strict
RUN composer install --no-interaction --no-progress
RUN composer audit --no-interaction

COPY . .

ENTRYPOINT ["./entrypoint.sh"]
