FROM php:7.2-apache

# install MongoDB PHP extension
RUN pecl install mongodb && echo "extension=mongodb.so" > /usr/local/etc/php/conf.d/mongo.ini

# install zip, composer
RUN apt-get update && \
	apt-get install -y zip && \ 
	curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Apache setup
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf && \
	sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf && \
	a2enmod rewrite

# add source code and dependencies
COPY . /var/www/html
WORKDIR /var/www/html
RUN composer install

# Laravel setup
RUN chmod -R 777 /var/www/html/storage && \
	cp .env.example .env && \
	php artisan key:generate

# download mapping file
ADD https://raw.githubusercontent.com/sfu-ireceptor/config/airr-api-dev-small/AIRR-iReceptorMapping.txt /var/www/html/config
RUN chmod 755 /var/www/html/config/AIRR-iReceptorMapping.txt
