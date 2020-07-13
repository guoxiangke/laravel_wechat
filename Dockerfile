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

FROM drupal:8.9-fpm
# https://hub.docker.com/_/drupal

# install the PHP extensions  pcntl & cron
RUN set -ex; \
  apt-get update; \
  apt-get install -y --no-install-recommends \
    vim \
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
COPY --from=frontend /app/public/js/ /var/www/html/public/js/
COPY --from=frontend /app/public/css/ /var/www/html/public/css/
COPY --from=frontend /app/mix-manifest.json /var/www/html/mix-manifest.json

COPY docker/start.sh /usr/local/bin/start
RUN chown -R www-data:www-data storage bootstrap/cache \
  && chmod -R ug+rwx storage bootstrap/cache \
  && chmod u+x /usr/local/bin/start
RUN touch /var/www/html/storage/logs/laravel.log \
  && chmod -R 777 /var/www/html/storage/logs/
# mkdir -p /var/www/html/storage/app/avatars/wechat/
CMD ["/usr/local/bin/start"]
