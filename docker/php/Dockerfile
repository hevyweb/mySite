FROM php:8.4-fpm

# Install system packages, node keyrings, build tools, and GD dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    gnupg2 \
    curl \
    python3 \
    python3-pip \
    build-essential \
    zip \
    unzip \
    imagemagick \
    libmagickwand-dev \
    libmagickcore-dev \
    libicu-dev \
    libzip-dev \
    # GD2 Dependencies
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    && ln -sf /usr/bin/python3 /usr/bin/python \
    && rm -rf /var/lib/apt/lists/*

ENV PYTHON=/usr/bin/python3

# Node.js & Yarn repositories
RUN curl -fsSL https://dl.yarnpkg.com/debian/pubkey.gpg | gpg --dearmor -o /usr/share/keyrings/yarn.gpg \
    && echo "deb [signed-by=/usr/share/keyrings/yarn.gpg] https://dl.yarnpkg.com/debian stable main" > /etc/apt/sources.list.d/yarn.list \
    && mkdir -p /etc/apt/keyrings \
    && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_20.x nodistro main" > /etc/apt/sources.list.d/nodesource.list \
    && apt-get update && apt-get install -y --no-install-recommends \
    nodejs \
    yarn \
    && rm -rf /var/lib/apt/lists/*

# Configure & Install PHP Extensions (including GD with FreeType, JPEG, and WebP support)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) gd intl zip pdo pdo_mysql

# Install PECL Extensions
RUN pecl install imagick apcu xdebug \
    && docker-php-ext-enable imagick apcu xdebug

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer