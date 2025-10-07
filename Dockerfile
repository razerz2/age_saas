# Imagem base com PHP 8.3, Apache e extensões
FROM php:8.3-apache

# Instala dependências do sistema e extensões PHP necessárias
RUN apt-get update && apt-get install -y \
    libpq-dev zip unzip git curl libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring xml

# Habilita o mod_rewrite do Apache
RUN a2enmod rewrite

# Copia o projeto
COPY . /var/www/html

# Define o diretório de trabalho
WORKDIR /var/www/html

# Configura o Apache para servir a pasta 'public'
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf
RUN echo "<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>" > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel


# Instala o Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Instala as dependências Laravel
RUN composer install --no-dev --optimize-autoloader

# Ajusta permissões do storage e bootstrap
RUN chmod -R 777 storage bootstrap/cache

# Expõe a porta do Apache
EXPOSE 80

# Comando de inicialização
CMD ["apache2-foreground"]
