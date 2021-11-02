#!/bin/bash

chown -R www-data:www-data ./*
echo Starting NGROK..

if [[ -z "${NGROK_AUTHTOKEN}" ]]; then
  echo "No NGROK_AUTHTOKEN in env"
  exit
else
  ngrok authtoken $NGROK_AUTHTOKEN
  echo "web_addr: 0.0.0.0:4040" >>./ngrok.conf
  nohup ngrok http -log=ngrok.log -region=eu --config=ngrok.conf 80 &
fi

sleep 4
NGROK_URL=$(curl http://127.0.0.1:4040/api/tunnels | jq '.tunnels[].public_url'  | grep https:)
NGROK_URL=$(sed -e 's/^"//' -e 's/"$//' <<<"$NGROK_URL")

# replace oc store url with NGROK URL
sed -i "s|define('HTTP_SERVER', '.*');|define('HTTP_SERVER', '"$NGROK_URL"/');|" opencart/config.php
sed -i "s|define('HTTPS_SERVER', '.*');|define('HTTPS_SERVER', '"$NGROK_URL"/');|" opencart/config.php
sed -i "s|'HTTP_SERVER', 'https|'HTTP_SERVER', 'http|" opencart/config.php

sed -i "s|define('HTTP_SERVER', '.*');|define('HTTP_SERVER', '"$NGROK_URL"/admin/');|" opencart/admin/config.php
sed -i "s|define('HTTP_CATALOG', '.*');|define('HTTP_CATALOG', '"$NGROK_URL"/');|" opencart/admin/config.php
sed -i "s|'HTTP_SERVER', 'https|'HTTP_SERVER', 'http|" opencart/admin/config.php
sed -i "s|'HTTP_CATALOG', 'https|'HTTP_CATALOG', 'http|" opencart/admin/config.php

sed -i "s|define('HTTPS_SERVER', '.*');|define('HTTPS_SERVER', '"$NGROK_URL"/admin/');|" opencart/admin/config.php
sed -i "s|define('HTTPS_CATALOG', '.*');|define('HTTPS_CATALOG', '"$NGROK_URL"/');|" opencart/admin/config.php


echo 'ngrokurl=' $NGROK_URL
echo ============================================

service php7.3-fpm start
nginx -g 'daemon off;'