<?php

class TransactionDB {

    private $db1;

    public function __construct($db1) {
        $this->db1 = $db1;
    }

    public function create() {
        $sql_create = "CREATE TABLE IF NOT EXISTS `" . $this->db1->prefix . "btpay_transaction` (
            `transaction_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Entity Id',
            `order_id` int(10) unsigned NOT NULL COMMENT 'Order Id',
            `transaction_type` varchar(50) DEFAULT NULL COMMENT 'Transaction Type',
            `transaction_status` smallint(5) unsigned DEFAULT NULL COMMENT 'Transaction Status',
            `amount_processed` decimal(12,4) DEFAULT NULL COMMENT 'Amount Processed',
            `currency_code` varchar(3) DEFAULT NULL COMMENT 'Currency Code',
            `order_name` varchar(50) DEFAULT NULL COMMENT 'Txn Order',
            `rrn` varchar(50) DEFAULT NULL COMMENT 'Txn RRN',
            `int_ref` varchar(50) DEFAULT NULL COMMENT 'Txn IntRef',
            `response_message` varchar(255) DEFAULT NULL COMMENT 'Gateway Response Message',
            `payment_method` varchar(255) DEFAULT NULL COMMENT 'Payment Method',
            `extra_info` text COMMENT 'Additional Information',
            `created_at` timestamp NULL DEFAULT NULL COMMENT 'Created At',
                PRIMARY KEY (`transaction_id`)
          )" . " DEFAULT CHARSET=utf8";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta($sql_create);
    }

    public function get($id) {
        $sql_get = "SELECT * FROM " . $this->db1->prefix . "btpay_transaction WHERE order_id=" . $id . ";";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        return $this->db1->get_results($sql_get);
    }

    public function insert($transaction_string) {
        $sql_insert = "INSERT INTO " . $this->db1->prefix . "btpay_transaction (order_id, transaction_type, transaction_status, amount_processed, currency_code, order_name, rrn, int_ref, response_message, payment_method, extra_info, created_at) VALUES ("
                . $transaction_string . ");";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta($sql_insert);
    }
    
    public function duplicate($extrainfo){
        $sql_duplicate = "SELECT * FROM ". $this->db1->prefix . "btpay_transaction WHERE extra_info='" . $extrainfo . "';";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        return $this->db1->get_results($sql_duplicate);
    }

}

?>