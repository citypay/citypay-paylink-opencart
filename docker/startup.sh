#!/bin/bash

echo Starting NGROK...
ngrok http --authtoken=$NGROK_AUTHTOKEN -log=ngrok.log 80 &

service php5-fpm start
nginx -g 'daemon off;'