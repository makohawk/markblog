FROM php:8.4-cli

ENV LANG=ja_JP.UTF-8 \
    LANGUAGE=ja_JP:ja \
    LC_ALL=ja_JP.UTF-8

RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    unzip \
    libzip-dev \
    locales \
    git \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && echo "ja_JP.UTF-8 UTF-8" >> /etc/locale.gen \
    && locale-gen

RUN docker-php-ext-install -j$(nproc) zip opcache

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN git config --global --add safe.directory /app

WORKDIR /app

COPY . .

RUN chmod +x markblog

RUN echo "alias phpunit='vendor/bin/phpunit'" >> /root/.bashrc

CMD ["php", "markblog", "serve"]