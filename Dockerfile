# Utilise PHP 8.2 avec Apache
FROM php:8.2-apache

# Copie tous les fichiers dans le dossier du serveur web
COPY . /var/www/html/

# Donne les bons droits aux fichiers
RUN chown -R www-data:www-data /var/www/html

# Active les modules PHP de base (mets-en d'autres si besoin)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Ouvre le port 80
EXPOSE 80

# Installe Composer (optionnel, utile si tu l'utilises)
RUN apt-get update && apt-get install -y unzip git zip curl && \
    curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer