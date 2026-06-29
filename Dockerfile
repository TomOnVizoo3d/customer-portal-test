FROM php:8.3.8-apache

# Installer für Composer hinzufügen
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Benötigte Verzeichnisse anlegen & Module aktivieren
RUN mkdir -p /usr/local/lib/php/PHPMailer && \
    a2enmod rewrite && \
    docker-php-ext-install mysqli && \
    apt-get update && apt-get install -y libfreetype-dev libjpeg62-turbo-dev libpng-dev && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd

# Dummy .env Datei für die PHP-Schnittstelle (falls im Code hart codiert verlangt)
RUN echo "<?php" > /usr/local/lib/php/vizoo_customers.env

# NUR die für Customers relevanten Shared Files kopieren 
# (Stelle sicher, dass dieser Ordner existiert oder passe den Pfad an)
COPY ./shared/countries.php \
     ./shared/auth/salts.txt \
     ./shared/auth/passwordRequirements.inl \
     ./shared/auth/php/vizoo-auth-header.php \
     /usr/local/lib/php/