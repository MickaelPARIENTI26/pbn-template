FROM php:8.1-fpm-alpine

RUN docker-php-ext-install pdo pdo_mysql

RUN apk add --no-cache nginx

COPY nginx.conf /etc/nginx/nginx.conf

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]
