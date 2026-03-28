FROM php:8.2-cli

WORKDIR /var/www/html

# PDO MySQL pour la connexion base de donnees
RUN docker-php-ext-install pdo pdo_mysql

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
