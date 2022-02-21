<?php

class ControllerExtensionPaymentCityPayPaylink extends Controller {

    function __construct($registry) {
        parent::__construct($registry);
        $this->load->language('extension/payment/citypay_paylink');
    }

    public function setOrderHistory($orderId, $orderStatusId, $message) {

        $this->load->model('checkout/order');
        $order = $this->model_checkout_order->getOrder($orderId);

        // changes the status if the order status is pending (1)
        if($order['order_status_id'] == 1) {
            $this->model_checkout_order->addOrderHistory(
                $orderId,
                $orderStatusId,
                $message
            );
        }
    }

    protected function process($orderId) {

        $this->load->model('checkout/order');
        $this->load->model('extension/payment/citypay_paylink');
        $this->load->model('setting/setting');

        if(!isset($orderId)) {
            $this->response->redirect($this->url->link('checkout/failure'));
        }

        $order = $this->model_checkout_order->getOrder($orderId);

        //
        //  Set the status of the order to that specified by the configuration
        //  item 'citypay_paylink_new_order_status_id'.
        //

        $logger = new Log('debug.log');
        $logger->write('Creating new order...');

        $this->model_checkout_order->addOrderHistory(
            $order['order_id'],
            $this->config->get('payment_citypay_paylink_new_order_status_id'),
            sprintf(
                $this->language->get('message_payment_transaction_status_advice_before_token_request'),
                date("H:i:s")
            )
        );

        //
        //  Obtain Paylink v3 token to set up the transaction and pre-populate
        //  the payment form fields as appropriate.
        //

        $cardholderAddress['address1'] = html_entity_decode($order['payment_address_1'], ENT_QUOTES, 'UTF-8');
        $cardholderAddress['address2'] = html_entity_decode($order['payment_address_2'], ENT_QUOTES, 'UTF-8');
        $cardholderAddress['area'] = html_entity_decode($order['payment_city'], ENT_QUOTES, 'UTF-8');
        $cardholderAddress['country'] = html_entity_decode($order['payment_iso_code_2'], ENT_QUOTES, 'UTF-8');
        $cardholderAddress['postcode'] = html_entity_decode($order['payment_postcode'], ENT_QUOTES, 'UTF-8');

        //
        //  Cardholder and cardholder address
        //
        $cardholder = array();
        $cardholder['title'] = '';
        $cardholder['firstName'] = html_entity_decode($order['payment_firstname'], ENT_QUOTES, 'UTF-8');
        $cardholder['lastName'] = html_entity_decode($order['payment_lastname'], ENT_QUOTES, 'UTF-8');
        $cardholder['email'] = html_entity_decode($order['email'], ENT_QUOTES, 'UTF-8');
        $cardholder['address'] = $cardholderAddress;

        //
        //  Token configuration
        //
        $pbUrl=$this->url->link('extension/payment/citypay_paylink/postback') . '&order_id=' . $order['order_id'];

        if(trim($this->config->get('payment_citypay_paylink_postback_url')) != ''){
            $customURL = trim($this->config->get('payment_citypay_paylink_postback_url'));
            $pbUrl = preg_replace("/(http)(.*)(\?.*)/", $customURL."$3", $pbUrl);
        }

        $logger->write('Postback URL ---> '.$pbUrl);


        $tokenConfig = array();
        $tokenConfig['postback_policy'] = 'async';
        $tokenConfig['postback'] = $pbUrl;
        $tokenConfig['redirect_success'] = $this->url->link('extension/payment/citypay_paylink/accept');
        $tokenConfig['redirect_failure'] = $this->url->link('extension/payment/citypay_paylink/cancel');
        $tokenConfig['return_params'] = 'true';

        //
        //  Token request setup
        //
        $tokenRequest = array();
        $tokenRequest['merchantId'] = $this->config->get('payment_citypay_paylink_merchant_id');
        $tokenRequest['licenceKey'] = $this->config->get('payment_citypay_paylink_licence_key');
        $tokenRequest['config'] = $tokenConfig;
        $tokenRequest['test'] = $this->config->get('payment_citypay_paylink_testing_mode') == 1;
        $tokenRequest['identifier'] = '[OrderId: '
            . $order['order_id']
            . ', InvoiceNo: ['
            . $order['invoice_no']
            . ']';
        $tokenRequest['clientVersion'] = "citypay_pl_oc_plugin_3.0.6";
        $tokenRequest['cardholder'] = $cardholder;

        //
        //  Paylink v3 requires the value of the transaction to be expressed
        //  in 'lowest denomination form' ("LDF").
        //
        $amount = $order['total'];
        $decimal_places = $this->currency->getDecimalPlace($order['currency_code']);
        if (!is_numeric($amount) || !is_numeric($decimal_places)) {
            $errorMessage = sprintf(
                $this->language->get('error_unable_to_generate_ldf_amount'),
                $amount,
                $decimal_places
            );
            $this->log->write(
                '[catalog/controller/extension/payment/citypay_paylink] '
                    . $errorMessage
            );

            $this->response->redirect($this->url->link('checkout/checkout', '', $this->config->get('config_secure')));
            return;
        }

        $tokenRequest['amount'] = round($amount , $decimal_places) * pow(10, $decimal_places);

        $jsonEncodedRequest = json_encode($tokenRequest);

        $curl_stderr = fopen('php://temp', 'w+');

        $curl_opts = array(
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $jsonEncodedRequest,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => array(
                        'Accept: application/json',
                        'Content-Type: application/json;charset=UTF-8',
                        'Content-Length: '.strlen($jsonEncodedRequest)
                    ),
                CURLOPT_VERBOSE => true,
                CURLOPT_STDERR => $curl_stderr
            );

        $ch = curl_init('https://secure.citypay.com/paylink3/create');
        curl_setopt_array($ch, $curl_opts);
        $httpsResponse = curl_exec($ch);
        if (!empty($httpsResponse)) {
            fclose($curl_stderr);

            $httpsResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $logger->write('HttpsResponse---> '.$httpsResponse);

            if ($httpsResponseCode == 200) {
                $decodedResponse = json_decode($httpsResponse);



                if ($decodedResponse->result == 0x01)
                {
                    $token = $decodedResponse->id;

                    $orderStatusId = $this->config->get('payment_citypay_paylink_new_order_status_id');
                    $message = sprintf(
                        $this->language->get('message_payment_transaction_status_advice_success_token_create'),
                        date("H:i:s"),
                        $token
                    );

                    $this->setOrderHistory($order['order_id'], $orderStatusId, $message );

                    $this->response->redirect($decodedResponse->url);
                }
                else
                {
                    //
                    //  The Paylink server has encountered Payment Transaction Request
                    //  authentication, validation or other upstream errors while processing
                    //  the Payment Transaction Request.
                    //
                    $template = $this->language->get('error_template_payment_token_request');
                    $errors = $decodedResponse->errors;
                    $i_max = count($errors);
                    for ($i = 0x00; $i < $i_max; $i++) {
                        $error = $errors[$i];
                        $errorMessage = sprintf(
                            $template,
                            $i,
                            $error->code,
                            $error->msg
                        );
                    }

                    $logMessage = sprintf(
                            $this->language->get('error_unable_to_obtain_payment_token_request_error'),
                            date("H:i:s"),
                            $errorMessage
                        );
                }
            }
            else
            {
                //
                //  The Paylink server has generated a HTTP response code that
                //  indicates that an error has occurred.
                //
                $logMessage = sprintf(
                        $this->language->get('error_unable_to_obtain_payment_token_http_connection_error'),
                        $httpsResponseCode
                    );
            }
        }
        else
        {
            rewind($curl_stderr);
            $req_stderr = stream_get_contents($curl_stderr, 4096);
            fclose($curl_stderr);

            $httpsResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            $logMessage = sprintf(
                    $this->language->get('error_unable_to_obtain_payment_token_curl_connection_error'),
                    $httpsResponseCode,
                    $req_stderr
                );
        }

        $this->log->write('[catalog/controller/extension/payment/citypay_paylink] '.$logMessage);

        $orderStatusId = $this->config->get('payment_citypay_paylink_failed_order_status_id');
        $this->setOrderHistory($order['order_id'], $orderStatusId, $logMessage );

        $this->response->redirect($this->url->link('checkout/failure'));
    }


    public function encrypt($textToEncrypt) {
        $password = $this->config->get('payment_citypay_paylink_licence_key');
        $key = substr(hash('sha256', $password, true), 0, 32);
        $cipher = 'aes-256-gcm';
        $iv_len = openssl_cipher_iv_length($cipher);
        $tag_length = 16;
        $iv = openssl_random_pseudo_bytes($iv_len);
        $tag = ""; // will be filled by openssl_encrypt

        $ciphertext = openssl_encrypt($textToEncrypt, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag, "", $tag_length);

        return base64_encode($iv.$ciphertext.$tag);
    }

    public function decrypt($textToDecrypt ) {
        $encrypted = base64_decode($textToDecrypt);
        $password = $this->config->get('payment_citypay_paylink_licence_key');
        $key = substr(hash('sha256', $password, true), 0, 32);
        $cipher = 'aes-256-gcm';
        $iv_len = openssl_cipher_iv_length($cipher);
        $tag_length = 16;
        $iv = substr($encrypted, 0, $iv_len);
        $ciphertext = substr($encrypted, $iv_len, -$tag_length);
        $tag = substr($encrypted, -$tag_length);

        return openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
    }


    public function index() {
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/citypay_paylink');

        if ($this->request->server['REQUEST_METHOD'] == 'POST' && isset($this->request->post['orderId'])) {
            $orderIdDecrypt = $this->decrypt($this->request->post['orderId']);
            $this->process($orderIdDecrypt );
        }

        if(!isset($this->session->data['order_id'])) {
            $this->response->redirect($this->url->link('checkout/failure'));
        }

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $data = array();

        $data['order_id'] = $this->encrypt($order_info['order_id']);

        $data['button_confirm'] = $this->language->get('button_confirm');

        $data['citypay_paylink_plugin'] = $this->url->link('extension/payment/citypay_paylink');

        return $this->load->view('extension/payment/citypay_paylink', $data);
    }

    private function _log($message) {
        //
        //  Obtain the backtrace and remove the call to this function.
        //
        $backtrace = debug_backtrace();
        array_shift($backtrace);

        $args = func_get_args();
        if (count($args) > 0x01) {
            array_shift($args);
        }

        //
        //  Dump the message, and the backtrace to the application log file.
        //
        $this->log->write(vsprintf($message, $args));
        $this->log->write($backtrace);
    }

    public function accept() {

        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
            $this->_log(
                $this->language->get('error_purported_redirection_by_paylink_of_incorrect_type'),
                $this->request->server,
                $this->request->get,
                $this->request->post
            );
            $this->response->redirect($this->url->link('checkout/failure'));
            return;
        }

        if (!$this->validate($this->config->get('payment_citypay_paylink_licence_key'))) {
            $this->_log(
                $this->language->get('error_paylink_response_could_not_be_validated'),
                $this->request->post
            );
            $this->response->redirect($this->url->link('checkout/failure'));
            return;
        }

        if ($this->request->post['authorised'] != 'true') {
            $this->_log(
                $this->language->get('error_paylink_response_indicates_payment_failure'),
                $this->request->post
            );
            $this->response->redirect($this->url->link('checkout/failure'));
            return;
        }

        $this->load->model('checkout/order');

        $this->response->redirect($this->url->link('checkout/success'));
    }

    public function cancel() {

        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
            $this->_log(
                $this->language->get('error_purported_redirection_by_paylink_of_incorrect_type'),
                $this->request->server,
                $this->request->get,
                $this->request->post
            );
            $this->response->redirect($this->url->link('checkout/failure'));
            return;
        }

        if (!$this->validate($this->config->get('payment_citypay_paylink_licence_key'))) {
            $this->_log(
                $this->language->get('error_paylink_response_could_not_be_validated'),
                $this->request->post
            );
            $this->response->redirect($this->url->link('checkout/failure'));
            return;
        }

        if ($this->request->post['authorised'] != 'false') {
            $this->_log(
                $this->language->get('error_paylink_response_indicates_payment_failure'),
                $this->request->post
            );
            $this->response->redirect($this->url->link('checkout/failure'));
            return;
        }

        $this->load->model('checkout/order');

        $this->response->redirect($this->url->link('checkout/failure'));
    }


    public function postback() {
        $logger = new Log('debug.log'); //creating a log file for debug
        $logger->write('Calling postback...');

        function object_to_array($obj) {
            if(is_object($obj)) $obj = (array) $obj;
            if(is_array($obj)) {
                $new = array();
                foreach($obj as $key => $val) {
                    $new[$key] = object_to_array($val);
                }
            }
            else $new = $obj;
            return $new;
        }

        $this->load->model('checkout/order');

        $order_id = $this->request->get['order_id'];

        $order = $this->model_checkout_order->getOrder($order_id);

        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
            http_response_code(400);
            flush();
            $errorMessage = $this->language->get('error_postback_message_not_delivered_as_http_post');
            $this->_log($errorMessage);
            $orderStatusId =  $this->config->get('payment_citypay_paylink_failed_order_status_id');
            $message = sprintf(
                $errorMessage,
                date("H:i:s")
            );
            $this->setOrderHistory($order['order_id'], $orderStatusId, $message);

            return;
        }

        //  Get the message body accompanying the incoming HTTP POST request,
        //  using a call to file_get_contents in conjunction with the PHP
        //  "php://input" read-only I/O stream.
        //
        $postback = file_get_contents("php://input");
        if ($postback == FALSE) {
            http_response_code(400);
            flush();
            $errorMessage = $this->language->get('error_postback_message_had_no_body');
            $this->_log($errorMessage);
            $orderStatusId =  $this->config->get('payment_citypay_paylink_failed_order_status_id');
            $message = sprintf(
                $errorMessage,
                date("H:i:s")
            );
            $this->setOrderHistory($order['order_id'], $orderStatusId, $message);

            return;
        }

        //
        //  De-serialize the JSON formatted message body contents to form
        //  an object, of an anonymous class, structure to contain properties
        //  that mirror those of the JSON packet.
        //
        $jsonPostback = json_decode($postback);
        if ($jsonPostback == NULL) {
            http_response_code(400);
            flush();
            $errorMessage = $this->language->get('error_postback_message_body_not_parseable_as_json');
            $this->_log($errorMessage);
            $orderStatusId =  $this->config->get('payment_citypay_paylink_failed_order_status_id');
            $message = sprintf(
                $errorMessage,
                date("H:i:s")
            );
            $this->setOrderHistory($order['order_id'], $orderStatusId, $message);

            return;
        }

        $this->request->post = object_to_array($jsonPostback);
        if (!$this->validate($this->config->get('payment_citypay_paylink_licence_key'))) {
            http_response_code(400);
            flush();
            $errorMessage = $this->language->get('error_postback_message_body_not_capable_of_validation');
            $this->_log($errorMessage);
            $orderStatusId =  $this->config->get('payment_citypay_paylink_failed_order_status_id');
            $message = sprintf(
                $errorMessage,
                date("H:i:s")
            );

            $this->setOrderHistory($order['order_id'], $orderStatusId, $message);

            return;
        }

        //
        //  Return a HTTP 200 OK response code to the Paylink server.
        //
        http_response_code(200);
        flush();

        //
        //  Check whether the Payment Transaction was authorised.
        //


        $logger->write('JSON Postback Auth---> '.$jsonPostback->authorised); // getting postback info

        if ($jsonPostback->authorised == "true") {
            $orderStatusId = $this->config->get('payment_citypay_paylink_completed_order_status_id');
            $message = sprintf(
                $this->language->get('message_payment_transaction_status_advice_on_postback_authorised'),
                date("H:i:s"),
                $this->request->post['authcode']
            );

            $this->setOrderHistory($order['order_id'], $orderStatusId, $message);
        } else {
            $errorcode = trim($this->request->post['errorcode']);
            switch ($errorcode) {
                case '080':
                    $status = $this->config->get('payment_citypay_paylink_cancelled_order_status_id');
                    $message = $this->language->get('message_payment_transaction_status_advice_on_postback_cancelled');
                    break;

                default:
                    $status = $this->config->get('payment_citypay_paylink_failed_order_status_id');
                    $message = $this->language->get('message_payment_transaction_status_advice_on_postback_not_authorised');
                    break;
            }

            $message = sprintf(
                $message,
                date("H:i:s"),
                $errorcode
            );

            $this->setOrderHistory($order['order_id'], $status, $message);
        }
    }

    protected function validate($licenceKey) {

        //
        //  Check that there is an associated payload.
        //
        if (!isset(
            $this->request->post['amount'],
            $this->request->post['errorcode'],
            $this->request->post['merchantid'],
            $this->request->post['transno'],
            $this->request->post['identifier']
        )) {
            $this->_log(
                $this->language->get('error_paylink_response_data_not_available_for_validation'),
                $this->request->post
            );
            return false;
        }

        //
        //  The Customer Browser Redirection from the Paylink Payment Form to the
        //  Merchant Application is performed through a POST operation to the
        //  relevant redirection endpoint.
        //
        //  If the Payment Transaction has been configured to return the parameters
        //  of the authorised or otherwise declined Payment Transaction, the parameters
        //  are provided in URL encoded form which PHP processes to generate the
        //  $_POST associative array.
        //
        //  Note
        //
        //  Verification of the payload forwarded to the Merchant Application via
        //  the Customer Browser Redirection is performed by reference to the licence
        //  key [licencekey] used for the Payment Transaction which is not transmitted
        //  back to the Merchant Application indirectly via the Customer Browser.
        //

        $authcode = isset($this->request->post['authcode']) ? $this->request->post['authcode'] : "";
        $digestSource = $authcode
            . $this->request->post['amount']
            . $this->request->post['errorcode']
            . $this->request->post['merchantid']
            . $this->request->post['transno']
            . $this->request->post['identifier']
            . $licenceKey;

        //
        //  Encode the digest source as a UTF-8 encoded string.
        //
        $digestSource_utf8 = utf8_encode($digestSource);

        //
        //  Calculate the SHA256 hash for the UTF-8 encoded digest source
        //  to generate a byte array containing the result.
        //
        $digest_sha256 = hash('sha256', $digestSource_utf8, true);

        //
        //  Encode the SHA256 hash value to a Base64 encoded string.
        //
        $digest_sha256_base64 = base64_encode($digest_sha256);

        //
        //  Compare the Base64 encoded SHA256 hash value calculated by the
        //  Merchant Application to the Base64 encoded SHA256 hash value
        //  accompanying the Payment Transaction parameters.
        //
        return ($this->request->post['sha256'] == $digest_sha256_base64);
    }
}