<?php
class ControllerExtensionPaymentDasgatewayqr extends Controller {
	
	public $api_key;
	
	public function index() {
	    
		$this->load->language('extension/payment/dasgatewayqr');
		$this->load->model('checkout/order');
		$this->load->model('extension/payment/dasgatewayqr');

		/**Gathering order details*/
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);		

		$order_id = $this->session->data['order_id'];
		$data['timestamp'] = date('Y:m:d-H:i:s');
		$data['order_id'] = $order_id;
		$data['continue'] = $this->url->link('checkout/cart');		
		
		$merchant_txn_ref = $order_id;  //order id
                $mode = $this->config->get('payment_dasgatewayqr_mode');
                
                $data['payment_option_img'] = 'catalog/view/theme/default/image/paymentoption.png';
                $data['text_payment_option'] = 'Payment Option';
                $data['accepted_cards'] = $this->config->get('payment_dasgatewayqr_cards'); //latest
                
        if($mode == 'Live'){ $this->api_key = $this->config->get('payment_dasgatewayqr_live_publishable_key'); } 
        if($mode == 'Test'){ $this->api_key = $this->config->get('payment_dasgatewayqr_test_publishable_key'); }
        
        

        $dasgatewayqr_currency = $this->config->get('payment_dasgatewayqr_currency');
        $merchant_id        = $this->config->get('payment_dasgatewayqr_merchant_id');
        $webhook_url        = $this->url->link('extension/payment/dasgatewayqr/webhook&orderId='.$order_id,'',true);
        $success_url        = $this->url->link('extension/payment/dasgatewayqr/callback','',true);
        $decline_url        = $this->url->link('extension/payment/dasgatewayqr/webhook','',true);
        $cancel_url         = $this->url->link('checkout/checkout','',true);
        
        $params = array(
            "RETURN_URLS" => array(
                "WEBHOOK_URL" => $webhook_url,
                "SUCCESS_URL" => $success_url,
                "DECLINE_URL" => $decline_url,
                "CANCEL_URL" => $cancel_url,
                "INPROGRESS_URL" => ""
            ),
            "amount" => number_format($order_info['total']),
            "currency" => $dasgatewayqr_currency,
            "client_id" => $this->session->data['order_id'],
            "merchant_id" => $merchant_id,
            "merchant_email" => null,
            "merchant_txn_ref" => $merchant_txn_ref,
            "card" => array (
                "name" => $order_info['payment_firstname']." ".$order_info['payment_lastname'],
            ),
            "billing_address" => array(
                "country"     => $order_info['payment_iso_code_2'],
                "email"       => $order_info['email'],
                "phone"       => $order_info['telephone'],
                "address"     => $order_info['payment_address_1'],
                "address2"    => $order_info['payment_address_2'],
                "city"        => $order_info['payment_city'],
                "state"       => substr($order_info['payment_zone'], 0, 30),
                "postal_code" => $order_info['payment_postcode']
            ),
            "shipping_address" => array(
                "country" => null,
                "email" => null,
                "phone" => null,
                "address" => null,
                "address2" => null,
                "city" => null,
                "state" => null,
                "postal_code" => null
            ),
            "mf1" => null,
            "mf2" => null,
            "mf3" => null,
            "mf4" => null,
            "risk1" => null,
            "risk2" => null,
            "risk3" => null,
            "risk4" => null,
            "risk5" => null,
            "auto_capture" => false
        );
		
		try {
            $response = $this->getQr($params);
        }
        catch (Exception $e) {
            echo $e->getMessage();
            die();
        }            

        if($response){           
            if($response->id){
                $url = $response->url;
                $qr  = $response->qr_code;
            } else {
                echo "Unable to perform Payment. Please contact your Merchant. [";
                echo $response->error_message;
                echo "]";
                die;
            }
        } else {
            echo "Unable to perform Payment. Please contact your Merchant.";
            die;
        }
                
        $url = preg_replace("/^http:/i", "https:", $url);                
        
        $dasgatewayqr_id      = $response->id;
        $client_id          = $params['client_id'];
        $merch_trans_ref    = $merchant_txn_ref;
        $merchant_ip        = $_SERVER['REMOTE_ADDR'];
        $merchant_id        = $params['merchant_id'];
        $amount             = $response->amount;
        $currency           = $response->currency;
        $status             = 'pending';
        $created            = $response->created_at;
        $this->model_extension_payment_dasgatewayqr->addOrder($dasgatewayqr_id,$client_id,$merch_trans_ref,$merchant_ip,$merchant_id,$amount,$currency,$mode,$status,$created);
                
		$data['total']            = $params['amount'];
        $data['qr']               = $qr;
        $data['qr_url']           = $url;
        $data['check_status_url'] = $this->url->link('extension/payment/dasgatewayqr/check_status','',true);
        $data['redirect_url']     = $this->url->link('extension/payment/dasgatewayqr/redirect_url','',true);

		return $this->load->view('extension/payment/dasgatewayqr', $data);
	}
	
	public function callback(){           
            $this->load->model('checkout/order');
            $this->load->model('extension/payment/dasgatewayqr');
                
            $post_data = file_get_contents('php://input');
            $post_data = json_decode($post_data);
            
            if($post_data){
                $merchant_txn_ref  = $post_data->merchant_txn_ref;
                $success           = $post_data->success;
                $amount            = $post_data->transaction->amount;
                $transaction_token = $post_data->transaction_token;
            }

            if(isset($amount) && $merchant_txn_ref != ''){
                    
                if($success == 1){
                    $success = 'success';
                } else {
                    $success = 'failed';
                }
                    
                /** The success transaction log to order table*/
                $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
                $notify = true;
                $message = 'Merchant Txn ref(Order Reference): ' . $merchant_txn_ref;
		$message .= 'Message: ' . $success;
		$message .= 'Transaction Id:' . $transaction_token;
		$message .= 'Amount: ' . $amount;		
                
                if($success == 'success'){
                    $order_update = $this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('payment_dasgatewayqr_success_status_id'), $message);
                } else {
                    $order_update = $this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('payment_dasgatewayqr_declined_status_id'), $message);
                }
                                                                    
                /**insert transaction details to the database*/
                $insert_trans_details = $this->model_extension_payment_dasgatewayqr->updateOrder($transaction_token,$merchant_txn_ref,$amount,$success);
            }
            
            if($this->session->data['order_id']){
                $status = $this->model_extension_payment_dasgatewayqr->checkStatus($this->session->data['order_id']);
                if($status == 'success'){
                    $this->response->redirect($this->url->link('checkout/success', '', 'SSL'));
                } else {
                    $this->response->redirect($this->url->link('checkout/failure', '', 'SSL'));
                }
            } else {
                $this->response->redirect($this->url->link('common/home', '', 'SSL'));
            }
	}
	
	public function cancel(){
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/dasgatewayqr');

        $post_data = json_decode(file_get_contents("php://input"));
    
        if($post_data){
            $merchant_txn_ref  = $post_data->merchant_txn_ref;
            $success           = $post_data->success;
            $amount            = $post_data->transaction->amount;
            $transaction_token = $post_data->transaction_token;
        }

        if(isset($success) && $order_id != ''){
            
            if($success == 1){
                $success = 'success';
            } else {
                $success = 'failed';
            }
            
            /** The success transaction log to order table*/
            $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);            

    		/**insert transaction details to the database*/
	        $insert_trans_details = $this->model_extension_payment_dasgatewayqr->updateOrder($transaction_token,$merchant_txn_ref,$amount,$success);
		
    		if($insert_trans_details){
                $this->response->redirect($this->url->link('checkout/failure', '', 'SSL'));
    		} else {
                $this->response->redirect($this->url->link('checkout/failure', '', 'SSL'));
    		}
		    $this->response->redirect($this->url->link('checkout/failure', '', 'SSL'));
        }
        $this->response->redirect($this->url->link('checkout/failure', '', 'SSL'));
	}
	
	public function webhook(){
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/dasgatewayqr');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        
        $post_data = file_get_contents('php://input');
        $post_data = json_decode($post_data);
        
        if($post_data){
            $merchant_txn_ref  = $post_data->merchant_txn_ref;
            $success           = $post_data->success;
            $amount            = $post_data->transaction->amount;
            $transaction_token = $post_data->transaction_token;
        }

        if(isset($amount) && $merchant_txn_ref != ''){
                
            if($success == 1){
                $success = 'success';
            } else {
                $success = 'failed';
            }
                
            /** The success transaction log to order table*/
            $order_id = $order_info['order_id'];
            if(!$order_id){
                $order_id = $_GET['orderId'];
            }
	        if($success == 'success'){
                    $order_update = $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_dasgatewayqr_success_status_id'));
		} else {
                    $order_update = $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_dasgatewayqr_declined_status_id'));
		}						
	        /**insert transaction details to the database*/
	        $insert_trans_details = $this->model_extension_payment_dasgatewayqr->updateOrder($transaction_token,$merchant_txn_ref,$amount,$success);
		
	    	$result = '';		
		    if($insert_trans_details){
                $result = "success";
		    } else {
                $result = "failed";
		    }
        }            
        return $result;
	}
	
	public function check_status(){
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/dasgatewayqr');
            
        $order_id = $_REQUEST['order_id'];            
        $status = $this->model_extension_payment_dasgatewayqr->checkStatus($order_id);           
            
        echo strtolower($status); die();            
	}	
	
	public function redirect_url(){
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/dasgatewayqr');
            
        $order_id = $_REQUEST['order_id'];            
        $status = $this->model_extension_payment_dasgatewayqr->checkStatus($order_id);
            
        if($status == 'success'){
            $return_link = $this->url->link('checkout/success','',true);
        } else {
            $return_link = $this->url->link('checkout/failure','', true);
        }            
        echo $return_link;
	}

	/**QR Code generator*/
	public function getQr($post_vals){
		
	    /** Convert the array to a JSON object */
		$post_data = json_encode($post_vals);
		
		$url = 'http://apiuat.zsolu.com/apm/api/QRPayment/QRGenerator';

        /** Encode key to base64 */
        $base64_key = base64_encode($this->api_key);

        /** Create the array containing the Authorization header */
        $headers = array("Authorization: Basic ".$base64_key,
		                 "Content-Type: application/json"
	    );

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_USERPWD => $this->api_key . ":",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $post_data,
            CURLOPT_HTTPHEADER => $headers,
        ));       
                
        $response = curl_exec($curl);

		if(curl_errno($curl)){
            $result =  'Request Error:' . curl_error($curl);
        } else {		
            $result = json_decode($response);		
		}
		
		curl_close($curl);
		
		return $result;		
	}	

	/**getting user ip details*/
	public function getUserIpAddr(){
		if(!empty($_SERVER['HTTP_CLIENT_IP'])){
	        //ip from share internet
		    $ip = $_SERVER['HTTP_CLIENT_IP'];
		}elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
		    //ip pass from proxy
		    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	    }else{
		    $ip = $_SERVER['REMOTE_ADDR'];
		}
	    return $ip;
	}
}
