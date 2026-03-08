FROM php:8.1-apache

RUN docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite \
    && sed -i 's/^#\(LoadModule mpm_prefork_module\)/\1/' /etc/apache2/apache2.conf \
    && find /etc/apache2/mods-enabled/ -name "mpm_*.load" -delete \
    && ln -sf /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/ \
    && ln -sf /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/

COPY apache.conf /etc/apache2/sites-available/000-default.conf

COPY . /var/www/html/

EXPOSE 80
