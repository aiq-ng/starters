FROM php:8.3-cli

# Install required packages
RUN apt-get update && \
    apt-get install -y \
        libzip-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        unzip \
        postgresql-client \
        libpq-dev \
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

# Copy the entrypoint script
COPY docker/scripts/app-entry.sh /usr/local/bin/entrypoint.sh

# Make the script executable
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose the port
EXPOSE 9090

# Set the entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
