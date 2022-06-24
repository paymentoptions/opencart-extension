<?php
class ModelExtensionPaymentDasgatewayqr extends Model {
	public function install() {
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "dasgateway_qr_order` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			  `dasgatewayqr_id` varchar(50) NOT NULL,
              `client_id` varchar(30) NOT NULL,
			  `merch_trans_ref` varchar(30) NULL,
			  `merchant_ip` varchar(30) NOT NULL,
			  `merchant_id` varchar(30) NOT NULL,
			  `amount` int(11) NOT NULL,
			  `currency` varchar(11) NOT NULL,
			  `mode` varchar(11) NOT NULL,
			  `log` TEXT NULL,
			  `transaction_id` varchar(50) NULL,
			  `status` varchar(20) NOT NULL,
			  `created` varchar(30) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");

	}

	public function uninstall() {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "dasgateway_qr_order`;");

	}
}
