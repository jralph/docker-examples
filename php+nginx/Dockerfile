# Create an installer image to handle composer packages and running of composer.
FROM composer:1.8 as installer

WORKDIR /var/app

COPY ./composer.json composer.json

# Extend the installer image to build dependencies for development.
FROM installer as dev_deps

RUN composer install --no-plugins --no-interaction --ignore-platform-reqs && \
    composer dump-autoload

# Extend the installer image to build dependencies for production.
FROM dev_deps as prod_deps

RUN composer install --no-dev --no-plugins --no-interaction --ignore-platform-reqs && \
    composer dump-autoload -o

# Build the base nginx image to use to serve assets
# and proxy any php file requests to the backend.
FROM nginx:1.17-alpine as server

ARG uid=82

COPY ./public/img /var/app/public/img
COPY ./public/favicon.ico /var/app/public/favicon.ico
COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/nginx/site.template /etc/nginx/conf.d/site.template

# 'set -eux' ensures the below scripts run as we would expect.
# All env variables must exist and all commands will be printed.
RUN set -eux; \
  adduser -u ${uid} -D -S -G www-data www-data; \
  touch /var/run/nginx.pid; \
  chown -R www-data:www-data /var/run/nginx.pid; \
  chown -R www-data:www-data /var/cache/nginx; \
  chown -R www-data:www-data /etc/nginx; \
  chown -R www-data:www-data /var/log/nginx; \
  chown -R www-data:www-data /var/app;

USER www-data

WORKDIR /var/app/public

ENV PHP_HOST=localhost:9000
ENV NGINX_TIMEOUT=60s
ENV NGINX_FASTCGI_READ_TIMEOUT=60

ENTRYPOINT ["/bin/sh", "-c"]
CMD ["envsubst '${PHP_HOST},${NGINX_TIMEOUT},${NGINX_FASTCGI_READ_TIMEOUT}' < /etc/nginx/conf.d/site.template > /etc/nginx/conf.d/default.conf; exec nginx -g 'daemon off;'"]

EXPOSE 8080

HEALTHCHECK --interval=10s --timeout=1s CMD pgrep nginx || exit 1

# Build the php fpm server to receive requests from
# the nginx proxy server.
FROM php:7.3-fpm-alpine as php

ARG uid=82
ARG gid=82
ARG with_xdebug=false

# 'set -eux' ensures the below scripts run as we would expect.
# All env variables must exist and all commands will be printed.
RUN set -eux; \
	apk add --no-cache --virtual .build-deps \
	  acl \
	  file \
	  gettext \
	  git \
	  shadow \
		$PHPIZE_DEPS \
		icu-dev \
		libzip-dev \
		zlib-dev \
	; \
	usermod -u ${uid} www-data; \
  groupmod -g ${gid} www-data; \
	docker-php-ext-configure zip --with-libzip; \
	docker-php-ext-install -j$(nproc) \
		intl \
		zip \
		bcmath \
	; \
	docker-php-ext-enable \
		opcache \
	; \
	runDeps="$( \
		scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
			| tr ',' '\n' \
			| sort -u \
			| awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
	)"; \
	apk add --no-cache --virtual .api-phpexts-rundeps fcgi $runDeps; \
	if [ $with_xdebug = "true" ]; then \
	  pecl install xdebug; \
	  docker-php-ext-enable xdebug; \
	  echo "error_reporting = E_ALL" >> $PHP_INI_DIR/docker-php-ext-xdebug.ini; \
    echo "display_startup_errors = On" >> $PHP_INI_DIR/docker-php-ext-xdebug.ini; \
    echo "display_errors = On" >> $PHP_INI_DIR/docker-php-ext-xdebug.ini; \
    echo "xdebug.remote_enable=1" >> $PHP_INI_DIR/docker-php-ext-xdebug.ini; \
	fi; \
	wget -O /usr/local/bin/php-fpm-healthcheck https://raw.githubusercontent.com/renatomefi/php-fpm-healthcheck/master/php-fpm-healthcheck; \
  chmod +x /usr/local/bin/php-fpm-healthcheck; \
  apk del .build-deps

# 'set -eux' ensures the below scripts run as we would expect.
# All env variables must exist and all commands will be printed.
RUN set -eux; \
  mkdir -p /var/app; \
  chown -R www-data:www-data /var/app

COPY ./docker/php/php-fpm.conf /usr/local/etc/php-fpm.conf
COPY ./docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY ./docker/php/php.ini $PHP_INI_DIR/php.ini

# When xdebug is wanted, we should set the timeouts to unlimited to avoid connection drops during debugging.
RUN set -eux; \
    if [ $with_xdebug = "true" ]; then \
      sed -i -E "s/request_terminate_timeout\s?=\s?([0-9]+)/request_terminate_timeout =0 /g" /usr/local/etc/php-fpm.d/www.conf; \
      sed -i -E "s/max_execution_time\s?=\s?([0-9]+)/max_execution_time=0/g" $PHP_INI_DIR/php.ini; \
      sed -i -E "s/max_input_time\s?=\s?([0-9]+)/max_input_time=0/g" $PHP_INI_DIR/php.ini; \
    fi;

WORKDIR /var/app/

COPY --chown=www-data:www-data public/index.php public/index.php
COPY --chown=www-data:www-data app app/
COPY --from=dev_deps --chown=www-data:www-data /var/app/vendor vendor/

USER www-data

EXPOSE 9000

HEALTHCHECK --interval=10s --timeout=1s CMD php-fpm-healthcheck || exit 1

# Extend the backend php image to be able to run our tests.
FROM php as test

COPY --chown=www-data:www-data tests tests/

ENTRYPOINT ["/var/app/vendor/bin/phpunit"]
CMD ["--bootstrap", "vendor/autoload.php", "tests"]

# Setup our production-ready backend image.
FROM php as backend

RUN rm -rf /var/app/vendor

COPY --from=prod_deps --chown=www-data:www-data /var/app/vendor vendor/
