CityPay Paylink Plugin for OpenCart
===================================

![OpenCart Logo](opencart.png)

CityPay Paylink Plugin for OpenCart is intended to enable hosted form
payment processing using CityPay's Paylink system with OpenCart, the
popular open-source electronic retail commerce solution.

Limitations
-----------

At present, the implementation is relatively basic insofar as it only
caters for instant payment card processing functionality; more
sophisticated functionality, as follows, is not presently supported -

1. payment pre-authorisation;
2. support for recurring payments;
3. support for 'one-click' customer relationships;
4. stores that accept more than one currency and conversion
   between currencies; and
5. processing refunds on receipt of returned goods, or
   cancellation of orders.

Version support
---------------
OpenCart version 3.0.5

PHP version
---------------
PHP 7.3 and later.

Building the plugin
-------------------

CityPay Paylink Plugin for OpenCart uses a Phing build scripts to
enable the preparation of a ZIP file suitable for importing the
extension to OpenCart.

Download [PHing](https://www.phing.info) and to build the ZIP file, run `php phing-version.phar` in the main project directory
without any arguments. The resultant ZIP file is located in the
`build` directory.

Installing the plugin
---------------------

To install the plugin to OpenCart, login to the OpenCart administration
panel, select the "Installer" under the 'Extensions' menu,
upload the ZIP generated in the previous step; and await confirmation
that the install process as been performed.

Once installed, select the 'Extensions' menu again, and then select
"Extensions" to obtain the list of available extensions. 
Select 'Payments' extension type, find 'CityPay Paylink Hosted Form' plugin and install it. 
With the plugin installed click in the action 'Edit' and fill in the configuration form with details of your merchant account,
the licence key associated with your merchant account, the currency
available for use with the plugin, the geographical zone associated with
the payment method, and any applicable sort order for the payment method.
Additionally, it is necessary to configure order states that are to be
used for the purpose of indicating whether a given payment transaction
was successful, was declined or otherwise cancelled.

Support
-------

For support, please contact [support@citypay.com](mailto:support@citypay.com)