FROM php:8.2-apache

# 1. Instalar dependencias del sistema y herramientas de compilación
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# 2. Configurar e instalar extensiones de PHP requeridas por CodeIgniter 4 y PostgreSQL
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    intl \
    gd \
    zip \
    pdo \
    pdo_pgsql \
    pgsql \
    opcache

# 3. Habilitar mod_rewrite de Apache (crítico para las rutas amigables de CodeIgniter)
RUN a2enmod rewrite

# 4. Configurar Apache para escuchar en el puerto dinámico $PORT (requerido por Render)
# Y cambiar el DocumentRoot de Apache a la carpeta /public de CodeIgniter 4
ENV PORT=80
RUN sed -i 's/Listen 80/Listen ${PORT}/g' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf



# 5. Instalar Composer globalmente
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. Configurar el directorio de trabajo
WORKDIR /var/www/html

# 7. Copiar los archivos del proyecto al contenedor
COPY . /var/www/html

# 8. Instalar dependencias de producción de Composer
# (Optimiza las clases autocargadas y omite paquetes de desarrollo)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 9. Configurar permisos apropiados para Apache
# CodeIgniter requiere permisos de escritura en la carpeta 'writable' para logs, caché y sesiones
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/writable

# 10. Configurar ajustes de producción para PHP
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && echo "upload_max_filesize = 256M" >> "$PHP_INI_DIR/php.ini" \
    && echo "post_max_size = 256M" >> "$PHP_INI_DIR/php.ini" \
    && echo "memory_limit = 512M" >> "$PHP_INI_DIR/php.ini"

# Quita el EXPOSE 80 fijo y el CMD anterior, pon esto:
EXPOSE 10000

CMD bash -c "sed -i \"s/Listen 80/Listen \${PORT:-10000}/g\" /etc/apache2/ports.conf && \
    sed -i \"s/<VirtualHost \*:80>/<VirtualHost \*:\${PORT:-10000}>/g\" /etc/apache2/sites-available/*.conf && \
    apache2-foreground"