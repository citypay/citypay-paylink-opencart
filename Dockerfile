FROM ubuntu:20.04
LABEL maintainer="Michael Martins <michael.martins@citypay.com>"

ENV DEBIAN_FRONTEND noninteractive

# Install dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip \
    less \
    vim \
    software-properties-common \
    git \
    curl \
    jq \
    openssl \
    nginx \
    && rm -rf /var/lib/apt/lists/*

RUN apt-add-repository ppa:ondrej/php && apt update && apt-get install -y php7.4-fpm php7.4-cli php7.4-mcrypt php7.4-gd php7.4-mysqlnd php7.4-curl php7.4-zip php7.4-xml

# Run some install actions
RUN phpenmod mcrypt

ENV OC_VERSION=3.0.4

# Install opencart
RUN mkdir /opencart \
    && cd /opencart \
    && curl -L -O "https://github.com/opencart/opencart/releases/download/${OC_VERSION}/opencart-${OC_VERSION}.zip" \
    && unzip opencart-${OC_VERSION}.zip -d opencart-${OC_VERSION} \
    && mv opencart-${OC_VERSION}/upload/* . \
    && rm -rf opencart-${OC_VERSION} opencart-${OC_VERSION}.zip \
    && rm -rf /usr/share/nginx/html \
    && chown -R www-data:www-data /opencart

# Install ngrok to monitor for postbacks
RUN curl -O "https://bin.equinox.io/c/4VmDzA7iaHb/ngrok-stable-linux-386.zip" -k \
    && unzip ngrok-stable-linux-386.zip \
    && cp ngrok /usr/bin/ngrok

# Setup PHP
RUN sed -i 's/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/' /etc/php/7.4/fpm/php.ini

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
RUN chmod +x opt/startup.sh

CMD ["/opt/startup.sh"]
