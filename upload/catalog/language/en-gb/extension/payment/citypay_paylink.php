<?php
$_['text_title'] = 'Credit / Debit card';
$_['button_confirm'] = 'Confirm';

$_['error_unable_to_generate_ldf_amount']
    = '[CityPay Paylink Plugin for OpenCart] Unable to generate amount'
    . ' expressed in LDF form: amount %s, decimal places %s';

$_['error_unable_to_obtain_payment_token_request_error']
    = '%s - [CityPay Paylink Plugin for OpenCart] Error obtaining a suitable'
    . ' CityPay Paylink token from the remote server for payment'
    . ' processing due to an issue relating to the contents of the'
    . ' token request sent by the plugin to the remote service.'
    . "\n Reported errors - \n"
    . '%s';

$_['error_template_payment_token_request'] = "(%s) %s -> %s\n";

$_['error_unable_to_obtain_payment_token_http_connection_error']
    = '[CityPay Paylink Plugin for OpenCart] Error obtaining a suitable'
    . ' CityPay Paylink token from the remote server for payment'
    . ' processing due to an issue relating to the ability for the'
    . ' plugin to connect to the remote service.'
    . "\n HTTP response code: %s"
    . "\n cURL request / response trace - \n"
    . '%s';

$_['error_unable_to_obtain_payment_token_curl_connection_error']
    = '[CityPay Paylink Plugin for OpenCart] Error obtaining a suitable'
    . ' CityPay Paylink token from the remote server for payment'
    . ' processing due to an issue relating to the ability for the'
    . ' plugin to connect to the remote service.'
    . "\n HTTP response code: %s"
    . "\n cURL request / response trace - \n"
    . '%s';

$_['error_postback_message_not_delivered_as_http_post']
    = '%s - [CityPay Paylink Plugin for OpenCart] Error detected on receipt of'
    . ' postback message:  connection has not been made as a HTTP POST'
    . ' request.';

$_['error_postback_message_body_not_parseable_as_json']
    = '%s - [CityPay Paylink Plugin for OpenCart] Error detected on receipt of'
    . ' payload accompanying the postback message, and attempting to'
    . ' interpret it as a JSON message.';

$_['error_paylink_response_data_not_available_for_validation']
    = '[CityPay Paylink Plugin for OpenCart] Post data does not'
    . ' contain the requisite data to enable validation of the'
    . ' contents of the postback operation.'
    . "\nRequest data -\n%s\n";

$_['error_paylink_response_could_not_be_validated']
    = '[CityPay Paylink Plugin for OpenCart] Post data could not be'
    . ' validated.'
    . "\nRequest data -\n%s\n";

$_['error_paylink_response_indicates_payment_failure']
    = '[CityPay Paylink Plugin for OpenCart] Validated post data'
    . ' indicates that payment transaction failed.'
    . "\nRequest data -\n%s\n";

$_['error_purported_redirection_by_paylink_of_incorrect_type']
    = '[CityPay Paylink Plugin for OpenCart] Purported redirection'
    . ' from Paylink is of the incorrect type.'
    . "\nServer data -\n%s"
    . "\nRequest (get) data -\n%s"
    . "\nRequest (post) data -\n%s";

$_['message_payment_transaction_status_advice_before_token_request']
    = '%s - [CityPay Paylink Plugin for OpenCart] Transaction status set to '
    . '\'pending\' for token request.';

$_['message_payment_transaction_status_advice_success_token_create']
    = '%s - [CityPay Paylink Plugin for OpenCart] Token Created: %s ';

$_['message_payment_transaction_status_advice_on_postback_authorised']
    = '%s - [CityPay Paylink Plugin for OpenCart] Transaction result: AUTHORISED'
    . ' (Authorisation code: %s)';

$_['message_payment_transaction_status_advice_on_postback_not_authorised']
    = '%s - [CityPay Paylink Plugin for OpenCart] Transaction result: NOT AUTHORISED'
    . ' (Error code: %s)';

$_['message_payment_transaction_status_advice_on_postback_token_expired']
    = '[CityPay Paylink Plugin for OpenCart] Transaction result: EXPIRED'
    . ' (Error code: %s)';

$_['message_payment_transaction_status_advice_on_postback_cancelled']
    = '%s - [CityPay Paylink Plugin for OpenCart] Transaction result: CANCELLED'
    . ' (Error code: %s)';
