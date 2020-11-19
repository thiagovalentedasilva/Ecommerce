FROM php:7.4-apache
RUN docker-php-ext-install pdo pdo_mysql mysqli
RUN apt-get update 
RUN apt-get install nano 

#RUN apt-get install -yq
RUN apt-get install -yq libfreetype6-dev
RUN apt-get install -yq libmcrypt-dev
#RUN apt-get install -yq libpng12-dev
RUN apt-get install -yq libjpeg-dev
RUN apt-get install -yq libpng-dev
RUN a2enmod rewrite 

#RUN docker-php-ext-configure gd
#RUN docker-php-ext-install gd
#RUN docker-php-ext-enable gd
#https://www.jaccon.com.br/instalando-gd-em-uma-imagem-docker-com-apache/

#RUN apt-get install php7.1-gd
#RUN a2enmod rewrite 
#RUN systemctl restart apache2