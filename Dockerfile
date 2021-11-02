FROM ubuntu:14.04
LABEL maintainer="Michael Martins <michael.martins@citypay.com>"

# Install dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip \
    less \
    vim \
    python-software-properties \
    software-properties-common \
    git \
    curl \
    openssl \
    nginx php5-fpm php5-cli php5-mcrypt php5-gd php5-mysqlnd php5-curl \
    && rm -rf /var/lib/apt/lists/*

# Run some install actions
RUN php5enmod mcrypt

ENV OC_VERSION=3.0.2.0

# Install opencart
RUN mkdir /opencart \
    && cd /opencart \
    && curl -L -O "https://github.com/opencart/opencart/releases/download/${OC_VERSION}/${OC_VERSION}-OpenCart.zip" \
    && unzip ${OC_VERSION}-OpenCart.zip -d ${OC_VERSION}-OpenCart \
    && mv ${OC_VERSION}-OpenCart/upload/* . \
    && rm -rf ${OC_VERSION}-OpenCart ${OC_VERSION}-OpenCart.zip \
    && rm -rf /usr/share/nginx/html \
    && chown -R www-data:www-data /opencart

# Install ngrok to monitor for postbacks
RUN curl -O "https://bin.equinox.io/c/4VmDzA7iaHb/ngrok-stable-linux-386.zip" \
    && unzip ngrok-stable-linux-386.zip \
    && cp ngrok /usr/bin/ngrok

# Setup PHP
RUN sed -i 's/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/' /etc/php5/fpm/php.ini

RUN echo '\ndisplay_errors = 1;\nerror_reporting = E_ALL;' >> /opencart/php.ini

# Setup Opencart
COPY docker/default /etc/nginx/sites-available/default
COPY docker/startup.sh /opt

# Removed as a default user will not be created
#COPY docker/config.php /opencart/config.php
#COPY docker/admin-config.php /opencart/admin/config.php

RUN touch /opencart/config.php \
    && touch /opencart/admin/config.php \
    && chown -R www-data:www-data /opencart
#    && chmod 0755 /opencart/config.php /opencart/admin/config.php
# do not delete install directory as this probably will be a frash run
#    && rm -rf /opencart/install

#ENV CITYPAY_PLUGIN_VERSION 1.1.0


# forward request and error logs to docker log collector
RUN ln -sf /dev/stdout /var/log/nginx/access.log \
	&& ln -sf /dev/stderr /var/log/nginx/error.log

EXPOSE 80

STOPSIGNAL SIGTERM

CMD ["/opt/startup.sh"]

#https://github.com/opencart/opencart/releases/download/3.0.2.0/3.0.2.0-OpenCart.zip
