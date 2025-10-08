# Dockerfile (racine)
FROM php:8.2-fpm AS appbuild

# 1) Outils & extensions PHP nécessaires à Symfony
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev libonig-dev libxml2-dev \
    libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo_mysql intl zip gd opcache \
 && rm -rf /var/lib/apt/lists/*

# 2) Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 3) Contexte de travail
WORKDIR /var/www/html

# 4) Variables de build/exécution
#    - APP_ENV pilote l’installation (dev/prod)
#    - COMPOSER_ALLOW_SUPERUSER évite l’avertissement Composer en root
#    - SYMFONY_SKIP_AUTO_RUN empêche les auto-scripts Flex (cache:clear, etc.)
ARG APP_ENV=dev
ENV APP_ENV=${APP_ENV} \
    APP_DEBUG=0 \
    COMPOSER_ALLOW_SUPERUSER=1 \
    SYMFONY_SKIP_AUTO_RUN=1

# 5) Copie du code (tout le projet)
COPY . /var/www/html

# 6) Dépendances + build assets (sans toucher à la DB)
#    - en prod: install --no-dev --no-scripts puis asset-map:compile
#    - en dev : install standard --no-scripts (utile si on lance l'image seule)
RUN set -ex; \
    if [ "$APP_ENV" = "prod" ]; then \
        composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts; \
        php bin/console asset-map:compile --env=prod || true; \
    else \
        composer install --prefer-dist --no-interaction --no-scripts; \
    fi

# 7) Permissions minimales (uploads + cache/logs + assets)
RUN chown -R www-data:www-data var public
USER www-data

# 8) Image finale = cette stage (contient déjà le code + assets compilés)
FROM appbuild
