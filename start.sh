#!/bin/sh
php-fpm -D
sleep 2
envsubst '${PORT}' < /etc/nginx/nginx.template.conf > /etc/nginx/nginx.conf
nginx -g "daemon off;"
