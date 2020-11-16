FROM php:7.0-apache
RUN docker-php-ext-install pdo pdo_mysql mysqli
RUN apt-get update 
RUN apt-get install nano 
#RUN docker-php-ext-install gd
#RUN apt-get install php7.1-gd
#RUN a2enmod rewrite 
#RUN systemctl restart apache2