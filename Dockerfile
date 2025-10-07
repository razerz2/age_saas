# Imagem base com PHP 8.3, Apache e extensões
FROM php:8.3-apache

# Instala dependências do sistema e extensões PHP necessárias
RUN apt-get update && apt-get install -y \
    libpq-dev zip unzip git curl libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring xml

# Habilita o mod_rewrite do Apache
RUN a2enmod rewrite

# Copia arquivos do projeto
COPY . /var/www/html/

# Define o diretório de trabalho
WORKDIR /var/www/html/

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
