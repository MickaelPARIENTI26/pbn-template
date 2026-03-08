FROM php:8.1-fpm-alpine

RUN docker-php-ext-install pdo pdo_mysql

RUN apk add --no-cache nginx gettext

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

COPY nginx.template.conf /etc/nginx/nginx.template.conf
COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]
