FROM php:8.1-cli

# Install dependencies for PDO MySQL
RUN apt-get update && apt-get install -y \
    default-mysql-client \
    libmariadb-dev \
    && docker-php-ext-install pdo pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY . .

CMD php -S 0.0.0.0:$PORT index.php
