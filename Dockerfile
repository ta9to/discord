FROM php:8.1-cli

WORKDIR /app

# Install dependencies
RUN apt-get update && \
    apt-get install -y --no-install-recommends git unzip && \
    rm -rf /var/lib/apt/lists/*

COPY composer.json composer.lock /app/

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    composer install --no-dev

CMD ["php", "./app.php"]

