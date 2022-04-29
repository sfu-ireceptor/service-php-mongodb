FROM php:7.3.33-apache

# install MongoDB PHP extension
RUN pecl install mongodb && echo "extension=mongodb.so" > /usr/local/etc/php/conf.d/mongo.ini

# install zip, composer
RUN apt-get update && \
	apt-get install -y zip && \ 
	curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Apache setup
COPY ./docker/apache-vhost-https.conf /etc/apache2/sites-available/000-default.conf
COPY ./docker/apache-vhost.conf /etc/apache2/sites-available/http.conf

ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf && \
	sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf && \
	sed -ri -e 's!daily!monthly!g' /etc/logrotate.d/apache2 && \
	sed -ri -e 's!rotate 14!rotate 120!g' /etc/logrotate.d/apache2 && \
    a2enmod rewrite && \
    a2dismod cgi && \
	a2enmod ssl && \
	a2enmod headers

RUN mkdir -p /etc/apache2/ssl

# add source code and dependencies
COPY . /var/www/html
WORKDIR /var/www/html
RUN composer install

# Laravel setup
RUN chown -R www-data:www-data /var/www/html/storage && \
        chown root:root /var/www/html && \
        chmod go-w /var/www/html && \
        chmod u+w /var/www/html && \

        #find /var/www -perm 0777 | xargs chmod 0755 && \
        find storage -name .gitignore | xargs chmod 0644 && \

        cp .env.example .env && \
        php artisan key:generate

# add mapping file
RUN mkdir /config
ADD https://raw.githubusercontent.com/sfu-ireceptor/config/master/AIRR-iReceptorMapping.txt /config/
RUN ln -s /config/AIRR-iReceptorMapping.txt /var/www/html/config/AIRR-iReceptorMapping.txt

RUN chmod 644 /var/www/html/config/AIRR-iReceptorMapping.txt
