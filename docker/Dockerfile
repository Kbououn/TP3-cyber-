# ATTENTION : image de base volontairement ancienne (PHP 8.1.1, janvier 2022) :
# elle embarque de nombreuses CVE connues, au niveau du système d'exploitation
# (paquets Debian) et de PHP lui-même. C'est la cible du scan `trivy image`
# (voir Cours Trivy - TP2 et Cours OWASP - A06).
FROM php:8.1.1-apache

# Mauvaise pratique volontaire : aucune directive USER -> Apache et les scripts PHP
# tournent en tant que root dans le conteneur. Voir Cours OWASP - A05.

RUN apt-get update && apt-get install -y --no-install-recommends \
        libzip-dev unzip \
    && docker-php-ext-install pdo_mysql \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Configuration PHP volontairement verbeuse : erreurs et informations serveur affichées
# telles quelles au visiteur, même hors environnement de développement.
RUN { \
        echo 'display_errors = On'; \
        echo 'expose_php = On'; \
        echo 'error_reporting = E_ALL'; \
    } > /usr/local/etc/php/conf.d/insecure-demo.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --optimize-autoloader

COPY . .
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Droits volontairement permissifs (chmod 777) plutôt qu'une gestion fine par utilisateur.
RUN mkdir -p var public/uploads \
    && chmod -R 777 var public/uploads \
    && composer dump-autoload --optimize

EXPOSE 80

CMD ["apache2-foreground"]
