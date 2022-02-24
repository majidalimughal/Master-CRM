FROM php:8.0-fpm

# Copy composer.lock and composer.json
COPY  ./laravel/composer.json /var/www/laravel/

# Set working directory
WORKDIR /var/www/laravel

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libpq-dev \
    libc-client-dev libkrb5-dev
RUN apt-get install libzip-dev -y

RUN curl -sL https://deb.nodesource.com/setup_14.x| bash -
RUN apt-get install -y nodejs

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install extensions
RUN docker-php-ext-install pdo_mysql pgsql pdo_pgsql zip exif pcntl 
RUN docker-php-ext-configure gd --with-freetype=/usr/include/ 
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql
RUN docker-php-ext-install gd
RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install imap

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Add user for laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

    

RUN chown -R www:www /var/www/laravel
# Copy existing application directory contents
COPY ./laravel /var/www/laravel

# Copy existing application directory permissions
COPY --chown=www:www ./laravel /var/www/laravel

# Change current user to www
USER www

#RUN composer install --no-scripts --no-autoloader

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"] 
