<?php
class ControllerExtensionPaymentCitypayPaylink extends Controller {
    private $error = array();

    public function index() {

        $this->load->language('extension/payment/citypay_paylink');
        $this->document->setTitle($this->language->get('document_title_configuration'));
        $this->load->model('setting/setting');
         
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
     
            $this->request->post['payment_citypay_paylink_merchant_id'] = trim($this->request->post['payment_citypay_paylink_merchant_id']);
            $this->request->post['payment_citypay_paylink_licence_key'] = trim($this->request->post['payment_citypay_paylink_licence_key']);
            
            $this->model_setting_setting->editSetting('payment_citypay_paylink', $this->request->post);
            $this->session->data['success'] = $this->language->get('message_success_save');
//            echo "<pre />\n";
//            print_r($this->request->post);
//            echo "</pre>\n";
//
//            exit;
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }
        
        //
        // 
        //
        $data['heading_title'] = $this->language->get('heading_title');
        $data['entry_label_merchant_id'] = $this->language->get('entry_label_merchant_id');
        $data['entry_label_licence_key'] = $this->language->get('entry_label_licence_key');
        $data['entry_label_postback_url'] = $this->language->get('entry_label_postback_url');
        $data['entry_label_merchant_currency'] = $this->language->get('entry_label_merchant_currency');
        
        $data['entry_label_new_order_status'] = $this->language->get('entry_label_new_order_status');
        $data['entry_label_completed_order_status'] = $this->language->get('entry_label_completed_order_status');
        $data['entry_label_cancelled_order_status'] = $this->language->get('entry_label_cancelled_order_status');
        $data['entry_label_expired_order_status'] = $this->language->get('entry_label_expired_order_status');
        $data['entry_label_failed_order_status'] = $this->language->get('entry_label_failed_order_status');
        
        $data['entry_label_status'] = $this->language->get('entry_label_status');
        $data['entry_label_geo_zone'] = $this->language->get('entry_label_geo_zone');
        $data['entry_label_sort_order'] = $this->language->get('entry_label_sort_order');
        
        $data['text_all_zones'] = $this->language->get('text_all_zones');
        
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        
        $data['button_label_save'] = $this->language->get('button_label_save');
        $data['button_label_cancel'] = $this->language->get('button_label_cancel');
        
        $data['values_currencies'] = $this->language->get('values_currencies');

        $data['action'] = $this->url->link('extension/payment/citypay_paylink', 'user_token='.$this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token='.$this->session->data['user_token'].'&type=payment', true);
        
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        if (isset($this->error['merchant_id'])) {
            $data['error_merchant_id'] = $this->error['merchant_id'];
        } else {
            $data['error_merchant_id'] = '';
        }

        if (isset($this->error['licence_key'])) {
            $data['error_licence_key'] = $this->error['licence_key'];
        } else {
            $data['error_licence_key'] = '';
        }
        
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_payment'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'].'&type=payment', true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/citypay_paylink', 'user_token=' . $this->session->data['user_token'], true)
        );

        //
        //
        //
        if (isset($this->request->post['payment_citypay_paylink_merchant_id'])) {
            $data['payment_citypay_paylink_merchant_id'] = $this->request->post['payment_citypay_paylink_merchant_id'];
        } else {
            $data['payment_citypay_paylink_merchant_id'] = $this->config->get('payment_citypay_paylink_merchant_id');
        }
        
        if (isset($this->request->post['payment_citypay_paylink_licence_key'])) {
            $data['payment_citypay_paylink_licence_key'] = $this->request->post['payment_citypay_paylink_licence_key'];
        } else {
            $data['payment_citypay_paylink_licence_key'] = $this->config->get('payment_citypay_paylink_licence_key');
        }

        if (isset($this->request->post['payment_citypay_paylink_postback_url'])) {
            $data['payment_citypay_paylink_postback_url'] = $this->request->post['payment_citypay_paylink_postback_url'];
        } else {
            $data['payment_citypay_paylink_postback_url'] = $this->config->get('payment_citypay_paylink_postback_url');
        }

        if (isset($this->request->post['payment_citypay_paylink_merchant_currency_id'])) {
            $data['payment_citypay_paylink_merchant_currency_id'] = $this->request->post['payment_citypay_paylink_merchant_currency_id'];
        } else {
            $data['payment_citypay_paylink_merchant_currency_id'] = $this->config->get('payment_citypay_paylink_merchant_currency_id');
        }
        
        if (isset($this->request->post['payment_citypay_paylink_new_order_status_id'])) {
            $data['payment_citypay_paylink_new_order_status_id'] = $this->request->post['payment_citypay_paylink_new_order_status_id'];
        } else {
            $data['payment_citypay_paylink_new_order_status_id'] = $this->config->get('payment_citypay_paylink_new_order_status_id');
        }
        
        if (isset($this->request->post['payment_citypay_paylink_completed_order_status_id'])) {
            $data['payment_citypay_paylink_completed_order_status_id'] = $this->request->post['payment_citypay_paylink_completed_order_status_id'];
        } else {
            $data['payment_citypay_paylink_completed_order_status_id'] = $this->config->get('payment_citypay_paylink_completed_order_status_id');
        }
        
        if (isset($this->request->post['payment_citypay_paylink_cancelled_order_status_id'])) {
            $data['payment_citypay_paylink_cancelled_order_status_id'] = $this->request->post['payment_citypay_paylink_cancelled_order_status_id'];
        } else {
            $data['payment_citypay_paylink_cancelled_order_status_id'] = $this->config->get('payment_citypay_paylink_cancelled_order_status_id');
        }
        
        if (isset($this->request->post['payment_citypay_paylink_expired_order_status_id'])) {
            $data['payment_citypay_paylink_expired_order_status_id'] = $this->request->post['payment_citypay_paylink_expired_order_status_id'];
        } else {
            $data['payment_citypay_paylink_expired_order_status_id'] = $this->config->get('payment_citypay_paylink_expired_order_status_id');
        }
        
        if (isset($this->request->post['payment_citypay_paylink_failed_order_status_id'])) {
            $data['payment_citypay_paylink_failed_order_status_id'] = $this->request->post['payment_citypay_paylink_failed_order_status_id'];
        } else {
            $data['payment_citypay_paylink_failed_order_status_id'] = $this->config->get('payment_citypay_paylink_failed_order_status_id');
        }
        
        if (isset($this->request->post['payment_citypay_paylink_status'])) {
            $data['payment_citypay_paylink_status'] = $this->request->post['payment_citypay_paylink_status'];
        } else {
            $data['payment_citypay_paylink_status'] = $this->config->get('payment_citypay_paylink_status');
        }
        
        if (isset($this->request->post['payment_citypay_paylink_geo_zone_id'])) {
            $data['payment_citypay_paylink_geo_zone_id'] = $this->request->post['payment_citypay_paylink_geo_zone_id'];
        } else {
            $data['payment_citypay_paylink_geo_zone_id'] = $this->config->get('payment_citypay_paylink_geo_zone_id');
        }
        
        if (isset($this->request->post['payment_citypay_paylink_sort_order'])) {
            $data['payment_citypay_paylink_sort_order'] = $this->request->post['payment_citypay_paylink_sort_order'];
        } else {
            $data['payment_citypay_paylink_sort_order'] = $this->config->get('payment_citypay_paylink_sort_order');
        }
         
        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        
        $this->load->model('localisation/geo_zone');
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/payment/citypay_paylink', $data));
    }
    
    protected function validate() {
        
        if (!$this->user->hasPermission('modify', 'extension/payment/citypay_paylink')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        if (!$this->request->post['payment_citypay_paylink_merchant_id']) {
            $this->error['merchant_id'] = $this->language->get('error_merchant_id');
        }
        
        if (!$this->request->post['payment_citypay_paylink_licence_key']) {
            $this->error['licence_key'] = $this->language->get('error_licence_key');
        }
        
        return !$this->error;
    }
}