#!/bin/sh
set -e
export PORT=${PORT:-80}
envsubst '${PORT}' < /nginx.template.conf > /etc/nginx/http.d/default.conf
php-fpm -D
sleep 1
exec nginx -g "daemon off;"
