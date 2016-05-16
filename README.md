CityPay Paylink Plugin for OpenCart
===================================

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

CityPay Paylink Plugin for OpenCart does not, at present, make use of
the CityPay SDK for PHP [https://github.com/citypay/php-sdk](https://github.com/citypay/php-sdk).

Version support
---------------

CityPay Paylink Plugin for OpenCart has been developed to work with
OpenCart version 2.2.0.0; no other versions are presently supported.

Building the plugin
-------------------

CityPay Paylink Plugin for OpenCart uses a Phing build scripts to
enable the preparation of a ZIP file suitable for importing the
extension to OpenCart.

To build the ZIP file, run `phing` in the main project directory
without any arguments. The resultant ZIP file is located in the
`build` directory.

Installing the plugin
---------------------

To install the plugin to OpenCart, login to the OpenCart administration
panel, select the "Extension Installer" under the 'jigsaw piece' menu,
upload the ZIP generated in the previous step; and await confirmation
that the install process as been performed.

Once installed, select the 'jigsaw piece' menu again, and then select
"Payments" to obtain the list of available payment methods.

To enable the plugin, it is necessary to mark it as enabled from the
OpenCart applications' perspective by clicking the "enable" button,
and also to configure the plugin with details of your merchant account,
the licence key associated with your merchant account, the currency
available for use with the plugin, the geographical zone associated with
the payment method, and any applicable sort order for the payment method.
Additionally, it is necessary to configure order states that are to be
used for the purpose of indicating whether a given payment transaction
was successful, was declined or otherwise cancelled.

Support
-------

For support, please contact [support@citypay.com](mailto:support@citypay.com)

Test suite support
------------------

Although some effort has been made to enable unit testing of the plugin
using a fork of the [https://github.com/openbaypro/opencart-test-suite.git](https://github.com/openbaypro/opencart-test-suite.git)
repository, unit testing is not presently supported.
