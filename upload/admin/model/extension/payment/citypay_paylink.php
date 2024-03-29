<?php
class ModelExtensionPaymentCityPayPaylink extends Model {

    public function install() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "payment_citypay_paylink_order` (
              `payment_citypay_paylink_order_id` INT(11) NOT NULL AUTO_INCREMENT,
              `order_id` INT(11) NOT NULL,
              `transaction_reference` varchar(127) DEFAULT NULL,
              `currency_code` CHAR(3) NOT NULL,
              `total` DECIMAL( 10, 2 ) NOT NULL,
              `created` DATETIME NOT NULL,
              `modified` DATETIME NOT NULL,
              PRIMARY KEY (`payment_citypay_paylink_order_id`)
            ) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "payment_citypay_paylink_order_transaction` (
              `payment_citypay_paylink_order_transaction_id` INT(11) NOT NULL AUTO_INCREMENT,
              `payment_citypay_paylink_order_id` INT(11) NOT NULL,
              `created` DATETIME NOT NULL,
              `type` ENUM('auth', 'payment', 'rebate', 'reversed') DEFAULT NULL,
              `amount` DECIMAL( 10, 2 ) NOT NULL,
              PRIMARY KEY (`payment_citypay_paylink_order_transaction_id`)
            ) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
    }

    public function uninstall() {
        //not recommended, dangerous!
//        $this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "citypay_paylink_order");
//        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "citypay_paylink_order_transaction`;");
    }

    public function getOrder($order_id) {
        $db_query = $this->db->query(
            "SELECT * FROM `"
            . DB_PREFIX
            . "payment_citypay_paylink_order` WHERE `order_id` = '"
            . (int)$order_id
            . "' LIMIT 1"
        );
        if ($db_query->num_rows) {
            $order = $db_query->row;
            $order['transactions'] = $this->getTransactions($order['payment_citypay_paylink_order_id']);
            return $order;
        } else {
            return false;
        }
    }

    private function getTransactions($cp_paylink_order_id) {
        $qry = $this->db->query(
            "SELECT * FROM `"
            . DB_PREFIX
            . "payment_citypay_paylink_order_transaction` WHERE `payment_citypay_paylink_order_id` = '"
            . (int) $cp_paylink_order_id
            . "'"
        );
        if ($qry->num_rows) {
            return $qry->rows;
        } else {
            return false;
        }
    }

    public function addTransaction($cp_paylink_order_id, $type, $total) {
        $this->db->query(
            "INSERT INTO `"
            . DB_PREFIX
            . "payment_citypay_paylink_order_transaction` SET `payment_citypay_paylink_order_id` = '"
            . (int) $cp_paylink_order_id
            . "', `created` = now(), `type` = '"
            . $this->db->escape($type)
            . "', `amount` = '"
            . (double)$total . "'");
    }

    public function logger($message) {
        $log = new Log('citypay_paylink.log');
        $log->write($message);
    }
}