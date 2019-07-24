# Install any dependencies and application libraries.
FROM composer:1.8 as installer

WORKDIR /var/app

COPY ./composer.json composer.json

RUN composer install --no-dev --no-plugins --no-interaction --ignore-platform-reqs && \
    composer dump-autoload -o

ENTRYPOINT ["composer", "--ansi"]
CMD ["-"]

# Build the base nginx image to use to serve assets
# and proxy any php file requests to the backend.
FROM nginx:1.17-alpine as server

COPY ./public/img /var/app/public/img
COPY ./public/favicon.ico /var/app/public/favicon.ico
COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/nginx/site.template /etc/nginx/conf.d/site.template

# 'set -eux' ensures the below scripts run as we would expect.
# All env variables must exist and all commands will be printed.
RUN set -eux; \
  adduser -u 82 -D -S -G www-data www-data; \
  touch /var/run/nginx.pid; \
  chown -R www-data:www-data /var/run/nginx.pid; \
  chown -R www-data:www-data /var/cache/nginx; \
  chown -R www-data:www-data /etc/nginx; \
  chown -R www-data:www-data /var/log/nginx; \
  chown -R www-data:www-data /var/app

USER www-data

WORKDIR /var/app/public

ENV PHP_HOST=localhost:9000

ENTRYPOINT ["/bin/sh", "-c"]
CMD ["envsubst '${PHP_HOST}' < /etc/nginx/conf.d/site.template > /etc/nginx/conf.d/default.conf; exec nginx -g 'daemon off;'"]

EXPOSE 8080

HEALTHCHECK --interval=1m --timeout=1s CMD pgrep nginx || exit 1

# Build the php fpm server to receive requests from
# the nginx proxy server.
FROM php:7.3-fpm-alpine as backend

ARG uid=82
ARG gid=82

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
	apk add --no-cache --virtual .api-phpexts-rundeps $runDeps; \
	apk del .build-deps

# 'set -eux' ensures the below scripts run as we would expect.
# All env variables must exist and all commands will be printed.
RUN set -eux; \
  mkdir -p /var/app; \
  chown -R www-data:www-data /var/app

COPY ./docker/php/php-fpm.conf /usr/local/etc/php-fpm.conf
COPY ./docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY ./docker/php/php.ini /usr/local/etc/php/php.ini

WORKDIR /var/app/

COPY public/index.php public/index.php
COPY app app/
COPY --from=installer /var/app/vendor vendor/

USER www-data

EXPOSE 9000
