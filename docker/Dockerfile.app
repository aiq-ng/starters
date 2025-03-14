FROM php:8.3-fpm

# Install required packages
RUN apt-get update && \
    apt-get install -y \
        libzip-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libnss3-tools \
        unzip \
        postgresql-client \
        libpq-dev \
        cron \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        gd \
        mysqli \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        zip \
        sockets \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Set the working directory
WORKDIR /workspace/starters/app

# Copy the application files to the working directory
COPY . .

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN mkdir -p /workspace/starters/app/storage && chmod -R 755 /workspace/starters/app/storage

# Copy the custom php-fpm.conf file into the PHP-FPM configuration directory
COPY docker/config/php-fpm/php-fpm.conf /etc/php/8.3/fpm/php-fpm.conf
RUN chmod +x /etc/php/8.3/fpm/php-fpm.conf

# Copy the entrypoint script
COPY docker/scripts/app-entry.sh /usr/local/bin/entrypoint.sh
COPY docker/scripts/cronjobs/due-bills-cronjob.sh /usr/local/bin/due-bills-cronjob.sh
COPY docker/scripts/cronjobs/expired-items-cronjob.sh /usr/local/bin/expired-items-cronjob.sh
COPY docker/scripts/cronjobs/backup-cronjob.sh /usr/local/bin/backup-cronjob.sh

# Make the script executable
RUN chmod +x /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/due-bills-cronjob.sh
RUN chmod +x /usr/local/bin/expired-items-cronjob.sh
RUN chmod +x /usr/local/bin/backup-cronjob.sh

# Expose PHP-FPM default port (9000)
EXPOSE 9080
EXPOSE 9090

# Set up entrypoint to handle PHP-FPM and cron jobs
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Run PHP-FPM in the foreground
CMD ["php-fpm", "-F"]
