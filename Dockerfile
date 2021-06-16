#
# PHP Dependencies
# https://laravel-news.com/multi-stage-docker-builds-for-laravel
#
FROM composer:1.7 as vendor

COPY database/ database/
COPY nova/ nova/

COPY composer.json composer.json
COPY composer.lock composer.lock

RUN composer install \
    --no-dev \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist

#
# Frontend
#
FROM node:8.11 as frontend

RUN mkdir -p /app/public

COPY package.json webpack.mix.js yarn.lock /app/
COPY resources/js/ /app/resources/js/
COPY resources/sass/ /app/resources/sass/

WORKDIR /app

RUN yarn install && yarn production

#
# Application
#
FROM drupal:8.9-apache
# https://hub.docker.com/_/drupal

# install the PHP extensions  pcntl
RUN set -ex; \
  apt-get update; \
  apt-get install -y --no-install-recommends \
    libonig-dev\
  ; \
  docker-php-ext-install -j "$(nproc)" \
    mbstring \
    pcntl \
    bcmath \
  ; \
  \
  rm -rf /var/lib/apt/lists/* \
  && rm -rf /var/www/html \
  && mkdir /var/www/html

COPY . /var/www/html
COPY --from=vendor /app/vendor/ /var/www/html/vendor/
COPY --from=frontend /app/public/ /var/www/html/public/

COPY docker/start.sh /usr/local/bin/start
WORKDIR /var/www/html

RUN chown -R www-data:www-data storage bootstrap/cache \
  && chmod -R ug+rwx storage bootstrap/cache \
  && chmod u+x /usr/local/bin/start

ENV APACHE_DOCUMENT_ROOT /var/www/html/public/
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

CMD ["/usr/local/bin/start"]
