#!/bin/sh
php-fpm -D
sleep 2
envsubst '$PORT' < /etc/nginx/nginx.conf > /etc/nginx/nginx.conf.tmp
mv /etc/nginx/nginx.conf.tmp /etc/nginx/nginx.conf
nginx -g "daemon off;"
