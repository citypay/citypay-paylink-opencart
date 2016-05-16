<?php
class ControllerPaymentCityPayPaylink extends Controller {

    function __construct($registry) {
        parent::__construct($registry);
        $this->load->language('payment/citypay_paylink');
    }
    
    protected function process() {
        
		$this->load->model('checkout/order');
		$this->load->model('payment/citypay_paylink');
        $this->load->model('setting/setting');
		$order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
	
        //
        //  Set the status of the order to that specified by the configuration
        //  item 'citypay_paylink_new_order_status_id'.
        //
        $this->model_checkout_order->addOrderHistory(
            $order['order_id'],
            $this->config->get('citypay_paylink_new_order_status_id'),
            $this->language->get('message_payment_transaction_status_advice_before_token_request')
        );
        
        //
        //  Obtain Paylink v3 token to set up the transaction and pre-populate
        //  the payment form fields as appropriate. 
        //
        
        //
        //
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
        $tokenConfig = array();
        $tokenConfig['postback_policy'] = 'async';
        $tokenConfig['postback'] = $this->url->link('payment/citypay_paylink/postback')
            . '&order_id='
            . $order['order_id'];
        $tokenConfig['redirect_success'] = $this->url->link('payment/citypay_paylink/accept');
        $tokenConfig['redirect_failure'] = $this->url->link('payment/citypay_paylink/cancel');
        $tokenConfig['return_params'] = 'true';
        
        //
        //  Token request setup
        //
        $tokenRequest = array();
        $tokenRequest['merchantId'] = $this->config->get('citypay_paylink_merchant_id');
        $tokenRequest['licenceKey'] = $this->config->get('citypay_paylink_licence_key');
        $tokenRequest['config'] = $tokenConfig;
        $tokenRequest['test'] = true;
        $tokenRequest['identifier'] = '[OrderId: '
            . $order['order_id']
            . ', InvoiceNo: ['
            . $order['invoice_no']
            . ']';
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
                '[catalog/controller/payment/citypay_paylink] '
                    . $errorMessage
            );
            
            $this->response->redirect($this->url->link('checkout/checkout', '', $this->config->get('config_secure')));
            return;
        }

        $tokenRequest['amount'] = $amount * pow(10, $decimal_places);
        
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

            if ($httpsResponseCode == 200) {    
                $decodedResponse = json_decode($httpsResponse);

                if ($decodedResponse->result == 0x01)
                {
                    $token = $decodedResponse->id;
                    $url = $decodedResponse->url;
                    
                    $this->response->redirect($decodedResponse->url);
                }
                else
                {
                    //
                    //  The Paylink server has encountered Payment Transaction Request
                    //  authentication, validation or other upstream errors while processing
                    //  the Payment Transaction Request.
                    //
                    $template = $this->language-get('error_template_payment_token_request');
                    $errors = $decodedResponse->errors;
                    $i_max = count($errors);
                    for ($i = 0x00; $i < $i_max; $i++) {
                        $error = $errors[$i];
                        $errorMessage .= sprintf(
                            template,
                            $i,
                            $error->code,
                            $error->msg
                        );
                    }
                    
                    $logMessage = '[catalog/controller/payment/citypay_paylink] '
                        . sprintf(
                            $this->language->get('error_unable_to_obtain_payment_token_request_error'),
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
                $logMessage = '[catalog/controller/payment/citypay_paylink]'
                    . sprintf(
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

            $req_errno = curl_errno($ch);
            $req_error = curl_error($ch);

            curl_close($ch);
            
            $logMessage = '[catalog/controller/payment/citypay_paylink]'
                . sprintf(
                    $this->language->get('error_unable_to_obtain_payment_token_curl_connection_error'),
                    $httpsResponseCode,
                    $req_stderr
                );
        }
        
        $this->log->write($logMessage);
       
        $this->response->redirect($this->url->link('checkout/checkout', '', $this->config->get('config_secure')));
    }
        
    /**
     * 
     */
    public function index() {
        $this->load->model('checkout/order');
        $this->load->model('payment/citypay_paylink');
        
        $gateway_info = $this->model_payment_citypay_paylink;
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->process();
        }
        
        $data = array();
        
        $data['order_id'] = $order_info['order_id'];
        
        $data['button_confirm'] = $this->language->get('button_confirm');

		$data['text_loading'] = $this->language->get('text_loading');

		$data['citypay_paylink_plugin'] = $this->url->link('payment/citypay_paylink');

		return $this->load->view('payment/citypay_paylink', $data);
  
        $this->response->setOutput($this->load->view('payment/citypay_paylink', $data));
    }
    
    private function _log(
        $message
    ) {
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
            _log(
                $this->language->get('error_purported_redirection_by_paylink_of_incorrect_type'),
                $this->request->server,
                $this->request->get,
                $this->request->post
            );
            $this->response->redirect($this->url->link('checkout/failure'));
            return;
        }
            
        if (!$this->validate($this->config->get('citypay_paylink_licence_key'))) {
            _log(
                $this->language->get('error_paylink_response_could_not_be_validated'),
                $this->request->post
            );
            $this->response->redirect($this->url->link('checkout/failure'));
            return;
        }

        if ($this->request->post['authorised'] != 'true') {
            _log(
                $this->language->get('error_paylink_response_indicates_payment_failure'),
                $this->request->post
            );
            $this->response->redirect($this->url->link('checkout/failure'));
            return;
        }
        
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
       
        $this->response->redirect($this->url->link('checkout/success'));
    }
    
    public function cancel() {
              
        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
            _log(
                $this->language->get('error_purported_redirection_by_paylink_of_incorrect_type'),
                $this->request->server,
                $this->request->get,
                $this->request->post
            );
            $this->response->redirect($this->url->link('checkout/failure'));
            return;
        }
            
        if (!$this->validate($this->config->get('citypay_paylink_licence_key'))) {
            _log(
                $this->language->get('error_paylink_response_could_not_be_validated'),
                $this->request->post
            );
            $this->response->redirect($this->url->link('checkout/failure'));
            return;
        }
            
        if ($this->request->post['authorised'] != 'false') {
            _log(
                $this->language->get('error_paylink_response_indicates_payment_failure'),
                $this->request->post
            );
            $this->response->redirect($this->url->link('checkout/failure'));
            return;
        }
        
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        
        $this->response->redirect($this->url->link('checkout/failure'));
    }
     
    public function postback() {
        
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
            _log($errorMessage);
            $this->model_checkout_order->addOrderHistory(
                $order['order_id'],
                $this->config->get('citypay_paylink_failed_order_status_id'),
                $errorMessage
            );
            return;
        }
        
        //  Get the message body accompanying the incoming HTTP POST request,
        //  using a call to file_get_contents in conjunction with the PHP
        //  "php://input" read-only I/O stream.
        //
        $postback = file_get_contents("php://input");
        if ($postback === FALSE) {
            http_response_code(400);
            flush();
            $errorMessage = $this->language->get('error_postback_message_had_no_body');
            _log($errorMessage);
            $this->model_checkout_order->addOrderHistory(
                $order['order_id'],
                $this->config->get('citypay_paylink_failed_order_status_id'),
                $errorMessage
            );
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
            _log($errorMessage);
            $this->model_checkout_order->addOrderHistory(
                $order['order_id'],
                $this->config->get('citypay_paylink_failed_order_status_id'),
                $errorMessage
            );
            return;
        }
        
        $this->request->post = object_to_array($jsonPostback);
        if (!$this->validate($this->config->get('citypay_paylink_licence_key'))) {
            http_response_code(400);
            flush(); 
            $errorMessage = $this->language->get('error_postback_message_body_not_capable_of_validation');
            _log($errorMessage);
            $this->model_checkout_order->addOrderHistory(
                $order['order_id'],
                $this->config->get('citypay_paylink_failed_order_status_id'),
                $errorMessage
            );
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
        if ($jsonPostback->authorised === "true") {
            $this->model_checkout_order->addOrderHistory(
                $order['order_id'],
                $this->config->get('citypay_paylink_completed_order_status_id'),
                sprintf(
                    $this->language->get('message_payment_transaction_status_advice_on_postback_authorised'),
                    $this->request->post['authcode']
                )
            );
        } else {
            $errorcode = trim($this->request->post['errorcode']);
            switch ($errorcode) {
            case '080':
                $status = $this->config->get('citypay_paylink_cancelled_order_status_id');
                $message = $this->language->get('message_payment_transaction_status_advice_on_postback_cancelled');
                break;
                
            default:
                $status = $this->config->get('citypay_paylink_failed_order_status_id');
                $message = $this->language->get('message_payment_transaction_status_advice_on_postback_not_authorised');
                break;
            }
            
            $this->model_checkout_order->addOrderHistory(
                $order['order_id'],
                $status,
                sprintf(
                    $message,
                    $errorcode
                )
            );
        }
    }
    
    protected function validate($licenceKey) {
        
        //
        //  Check that there is an associated payload.
        //
        if (!isset(
            $this->request->post['authcode'],
            $this->request->post['amount'],
            $this->request->post['errorcode'],
            $this->request->post['merchantid'],
            $this->request->post['transno'],
            $this->request->post['identifier']
        )) {            
            _log(
                $this->language->get('error_paylink_response_data_not_available_for_validation'),
                $this->request
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
        $digestSource = $this->request->post['authcode']
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