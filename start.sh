#!/bin/sh
php-fpm -D
sleep 2
nginx -t && nginx -g "daemon off;"
