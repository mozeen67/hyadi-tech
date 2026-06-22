FROM wordpress:latest

# 1. Copiamos tu carpeta con tus temas y plugins
COPY ./wp-content /var/www/html/wp-content

# 2. Ajustamos los permisos para que WordPress funcione bien
RUN chown -R www-data:www-data /var/www/html/wp-content

# 3. El truco maestro: Interceptar el arranque de Apache
RUN mv /usr/local/bin/apache2-foreground /usr/local/bin/apache2-foreground-orig && \
    echo '#!/bin/bash' > /usr/local/bin/apache2-foreground && \
    echo 'a2dismod mpm_event mpm_worker 2>/dev/null || true' >> /usr/local/bin/apache2-foreground && \
    echo 'a2enmod mpm_prefork 2>/dev/null || true' >> /usr/local/bin/apache2-foreground && \
    echo 'exec apache2-foreground-orig "$@"' >> /usr/local/bin/apache2-foreground && \
    chmod +x /usr/local/bin/apache2-foreground
RUN echo -e 'upload_max_filesize = 512M\npost_max_size = 512M\nmemory_limit = 512M\nmax_execution_time = 300' > /usr/local/etc/php/conf.d/uploads.ini