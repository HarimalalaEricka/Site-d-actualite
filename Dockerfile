FROM php:8.2-apache

WORKDIR /var/www/html

# Expose le dossier public comme racine web
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# PDO MySQL pour la connexion base de données
RUN docker-php-ext-install pdo pdo_mysql

# Activer les modules Apache nécessaires
RUN a2enmod rewrite headers expires deflate

# Mettre a jour la configuration Apache pour utiliser /public comme DocumentRoot
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
	&& sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copier le code source (optionnel si tu montes le volume)
# COPY . /var/www/html

EXPOSE 80

# Apache en premier plan
CMD ["apache2-foreground"]