#!/usr/bin/env bash

mkdir -p citypay-paylink-opencart
cp -R upload citypay-paylink-opencart/
cp install.xml citypay-paylink-opencart/

VERSION=$(awk -F '[<>]' '/version/{print $3}' install.xml | tr -d '[:space:]')
echo $VERSION

cd citypay-paylink-opencart

zip -r "../citypay-paylink-opencart-$VERSION.ocmod.zip" ./* \
&& cd ../ && rm -rf citypay-paylink-opencart \
&& mkdir -p dist \
&& mv "citypay-paylink-opencart-$VERSION.ocmod.zip" dist