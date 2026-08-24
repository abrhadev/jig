ARG PHP_VERSION=8.4

FROM php:${PHP_VERSION}-cli-bookworm

ARG JIG_UID=1000
ARG JIG_GID=1000

ENV COMPOSER_HOME=/composer \
    COMPOSER_CACHE_DIR=/composer/cache \
    COMPOSER_NO_INTERACTION=1 \
    COMPOSER_MEMORY_LIMIT=-1 \
    HOME=/home/jig \
    PATH=/app/vendor/bin:/composer/vendor/bin:$PATH

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        ca-certificates \
        git \
        libxml2-dev \
        libonig-dev \
        libsqlite3-dev \
        libzip-dev \
        openssh-client \
        unzip; \
    docker-php-ext-install -j"$(nproc)" dom mbstring pdo pdo_sqlite simplexml xml xmlwriter zip; \
    pecl install pcov; \
    docker-php-ext-enable pcov; \
    pecl clear-cache; \
    rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY jig-container-init /usr/local/bin/jig-container-init

RUN set -eux; \
    chmod +x /usr/local/bin/jig-container-init; \
    printf 'memory_limit=-1\n' > /usr/local/etc/php/conf.d/zz-jig.ini; \
    printf '[safe]\n\tdirectory = *\n' > /etc/gitconfig

RUN set -eux; \
    groupadd -f -g "${JIG_GID}" jig; \
    useradd -u "${JIG_UID}" -g "${JIG_GID}" -m -d /home/jig -s /bin/bash jig; \
    mkdir -p /app /composer/cache; \
    chown -R "${JIG_UID}:${JIG_GID}" /app /composer /home/jig

LABEL com.abrha.jig.uid="${JIG_UID}" \
      com.abrha.jig.gid="${JIG_GID}"

WORKDIR /app

USER jig

ENTRYPOINT ["jig-container-init"]

CMD ["tail", "-f", "/dev/null"]
