FROM php:8.1-apache

# Instala dependências do sistema e extensões necessárias
RUN apt-get update && apt-get install -y unzip libzip-dev libpq-dev \
    && docker-php-ext-install zip pdo_pgsql \
    && pecl install redis \
    && docker-php-ext-enable redis

# Instala o Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copia os arquivos da aplicação
COPY api/ /var/www/html/

WORKDIR /var/www/html

# Atualiza o composer.lock e instala as dependências
RUN composer update --no-interaction --prefer-dist --optimize-autoloader

RUN a2enmod rewrite

RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]