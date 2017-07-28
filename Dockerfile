# https://vufind.org/wiki/installation:ubuntu

FROM ubuntu:14.04

ADD . /usr/local/vufind

ENV DEBIAN_FRONTEND noninteractive
ENV LANG=C.UTF-8

RUN apt-get -y update

RUN apt-get -y install software-properties-common
RUN add-apt-repository ppa:ondrej/php

RUN apt-get -y update

RUN apt-get install -y nodejs-legacy openjdk-7-jdk curl git apache2 mysql-server
RUN a2enmod rewrite

RUN apt-get -y install php5.6 php5.6-mcrypt php5.6-mbstring php5.6-curl php5.6-cli
RUN apt-get -y install php5.6-mysql php5.6-gd php5.6-intl php5.6-xsl php5.6-zip
RUN apt-get -y install php5-cgi
RUN apt-get -y install php-pear
RUN curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /usr/local/vufind

RUN composer install
RUN php install.php
RUN chown -R www-data:www-data /usr/local/vufind/local/cache
RUN chown -R www-data:www-data /usr/local/vufind/local/config
RUN mkdir /usr/local/vufind/local/cache/cli
RUN chmod 777 /usr/local/vufind/local/cache/cli
RUN ln -s /usr/local/vufind/local/httpd-vufind.conf /etc/apache2/conf-enabled/vufind.conf

RUN sh -c 'echo export JAVA_HOME=\"/usr/lib/jvm/default-java\" > /etc/profile.d/vufind.sh'
RUN sh -c 'echo export VUFIND_HOME=\"/usr/local/vufind\"  >> /etc/profile.d/vufind.sh'
RUN sh -c 'echo export VUFIND_LOCAL_DIR=\"/usr/local/vufind/local\"  >> /etc/profile.d/vufind.sh'

EXPOSE 80 443 3306 8080

CMD service apache2 start && /bin/bash
