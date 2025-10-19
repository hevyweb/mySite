FROM php:8.3-fpm
RUN apt-get update
RUN apt-get install -y gnupg2
RUN apt-get install -y curl
RUN rm -rf /var/lib/apt/lists/*

RUN curl -fsSL https://dl.yarnpkg.com/debian/pubkey.gpg | gpg --dearmor -o /usr/share/keyrings/yarn.gpg
RUN echo "deb [signed-by=/usr/share/keyrings/yarn.gpg] https://dl.yarnpkg.com/debian stable main" > /etc/apt/sources.list.d/yarn.list

# Add NodeSource GPG key and repository
RUN curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg
RUN echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_20.x nodistro main" > /etc/apt/sources.list.d/nodesource.list
RUN apt-get update
RUN apt-get install -y python3 python3-pip
RUN ln -sf /usr/bin/python3 /usr/bin/python
ENV PYTHON=/usr/bin/python3
RUN apt-get install -qy nodejs
RUN apt-get install -y yarn
RUN apt-get install -y imagemagick
RUN apt-get install -y libmagickwand-dev --no-install-recommends
RUN apt-get install -y libmagickcore-dev --no-install-recommends
RUN apt-get install -y build-essential
RUN apt-get install -y libicu-dev
RUN apt-get install -y libzip-dev
RUN apt-get install -y zip unzip

RUN pecl install imagick

RUN docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && docker-php-ext-enable intl
RUN docker-php-ext-install intl zip \
    && docker-php-ext-enable intl zip
RUN docker-php-ext-enable imagick
RUN docker-php-ext-install pdo pdo_mysql
RUN pecl install apcu \
    && docker-php-ext-enable apcu
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
