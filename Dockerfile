FROM php:8.2-fpm

# Arguments defined in docker-compose.yml
ARG user
ARG uid

EXPOSE 8001
# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    vim \
    curl \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    libfreetype6-dev \
    zip \
    unzip \
    libsodium-dev \
    libzip-dev \
    openssl


# RUN curl -sL https://deb.nodesource.com/setup_14.x | bash - 

# RUN apt-get install -y nodejs 

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*


# RUN docker-php-ext-install openssl 
RUN docker-php-ext-install bcmath 
RUN docker-php-ext-install ctype 
RUN docker-php-ext-install mbstring 
RUN docker-php-ext-install pdo
RUN docker-php-ext-install pdo_mysql 
#RUN docker-php-ext-install tokenizer
RUN docker-php-ext-install xml
RUN docker-php-ext-install zip
RUN docker-php-ext-install fileinfo
RUN docker-php-ext-install sodium
RUN docker-php-ext-install pcntl 

RUN docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ 
RUN docker-php-ext-install -j$(nproc) gd
# 
# RUN docker-php-ext-install gd 

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Set working directory
WORKDIR /var/www
COPY ./ /var/www/

USER $user
