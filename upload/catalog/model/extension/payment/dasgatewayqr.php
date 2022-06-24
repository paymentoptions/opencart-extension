<?php
class ModelExtensionPaymentDasgatewayqr extends Model {
	public function getMethod($address, $total) {
		$this->load->language('extension/payment/dasgatewayqr');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_dasgatewayqr_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		

		$method_data = array();

		
			$method_data = array(
				'code'       => 'dasgatewayqr',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payment_dasgatewayqr_sort_order')
			);
		

		return $method_data;
	}

	public function addOrder($dasgatewayqr_id,$client_id,$merch_trans_ref,$merchant_ip,$merchant_id,$amount,$currency,$mode,$status,$created) {

		$this->db->query("INSERT INTO `" . DB_PREFIX . "dasgateway_qr_order` (`dasgatewayqr_id`, `client_id`, `merch_trans_ref`, `merchant_ip`, `merchant_id`, `amount`, `currency`, `mode`, `status`, `created`) VALUES ('".$dasgatewayqr_id."','".$client_id."','".$merch_trans_ref."','".$merchant_ip."','".$merchant_id."','".$amount."','".$currency."','".$mode."','".$status."','".$created."')");
		
		return true;
	}
	
	public function updateOrder($transaction_token,$merchant_txn_ref,$amount,$success) {

		$this->db->query("UPDATE `" . DB_PREFIX . "dasgateway_qr_order` SET `transaction_id` =  '".$transaction_token."', `status` = '".$success."' WHERE `merch_trans_ref` = '".$merchant_txn_ref."' AND `amount` =  $amount");
		
		return true;
	}
	
	public function checkStatus($order_id) {

		$status = $this->db->query("SELECT `status` FROM `" . DB_PREFIX . "dasgateway_qr_order` WHERE client_id = '".$order_id."'");
		
		if($status->num_rows){
                        foreach($status->rows as $row){
                                $order_status = $row['status'];
                        }
		}
		
		return $order_status;
	}

	
} 