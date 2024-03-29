FROM bitnami/php-fpm:8.2.6
RUN apt-get update && apt-get install -y gnupg2
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - && \
    echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list
RUN curl -sL https://deb.nodesource.com/setup_14.x | bash -
RUN apt-get update && \
    apt-get install -qq -y nodejs yarn imagemagick libmagickwand-dev --no-install-recommends php-dev build-essential
RUN pecl install imagick
