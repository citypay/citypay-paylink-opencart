FROM bitnami/opencart:2

LABEL maintainer="Michael Martins <michael.martins@citypay.com>"

RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip \
    less \
    vim \
    && rm -rf /var/lib/apt/lists/*

ENV CITYPAY_PLUGIN_VERSION 1.1.0

#COPY scripts/*.sh /usr/local/bin/

EXPOSE 80
