FROM bitnami/php-fpm:8.2.6
RUN apt-get update
RUN apt-get install -y gnupg2
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - && \
    echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list
RUN mkdir -p /etc/apt/keyrings
RUN curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key \
    | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg; \
    NODE_MAJOR=20; \
    echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_MAJOR.x nodistro main" \
    > /etc/apt/sources.list.d/nodesource.list
RUN apt-get update
RUN apt-get install -y python3.9
ENV PYTHON /usr/bin/python3
RUN ln -s /usr/bin/python3 /usr/bin/python
RUN apt-get install -qy nodejs
RUN apt-get install -y yarn
RUN apt-get install -y imagemagick
RUN apt-get install -y libmagickwand-dev
RUN apt-get install -y --no-install-recommends php-dev
RUN apt-get install -y build-essential
RUN apt-get install -y unzip
RUN pecl install imagick