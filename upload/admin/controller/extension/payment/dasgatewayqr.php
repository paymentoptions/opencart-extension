<?php
class ControllerExtensionPaymentDasgatewayqr extends Controller {	
	
	private $error = array();

	public function index() {

		$this->load->language('extension/payment/dasgatewayqr');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		
		$this->load->model('localisation/order_status');	

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('payment_dasgatewayqr', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'].'&type=payment', true));
		}
		
		$data['dasgatewayqr_currencies'] = array('SGD','USD','JPY','INR','EUR');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$data['callback_url'] =  HTTPS_CATALOG . 'index.php?route=extension/payment/dasgatewayqr/callback';
		
		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['error_live_publishable_key'])) {
			$data['error_live_publishable_key'] = $this->error['error_live_publishable_key'];
		} else {
			$data['error_live_publishable_key'] = '';
		}
		
		if (isset($this->error['error_test_publishable_key'])) {
			$data['error_test_publishable_key'] = $this->error['error_test_publishable_key'];
		} else {
			$data['error_test_publishable_key'] = '';
		}
		
		if (isset($this->error['error_merchant_id'])) {
			$data['error_merchant_id'] = $this->error['error_merchant_id'];
		} else {
			$data['error_merchant_id'] = '';
		}

		/**breadcrumbs*/
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/dasgatewayqr', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/dasgatewayqr', 'user_token=' . $this->session->data['user_token'], true);
		
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

		
		/**Admin form fields*/
		
		if (isset($this->request->post['payment_dasgatewayqr_live_publishable_key'])) {
			$data['payment_dasgatewayqr_live_publishable_key'] = $this->request->post['payment_dasgatewayqr_live_publishable_key'];
		} else {
			$data['payment_dasgatewayqr_live_publishable_key'] = $this->config->get('payment_dasgatewayqr_live_publishable_key');
		}
		
		if (isset($this->request->post['payment_dasgatewayqr_live_secret_key'])) {
			$data['payment_dasgatewayqr_live_secret_key'] = $this->request->post['payment_dasgatewayqr_live_secret_key'];
		} else {
			$data['payment_dasgatewayqr_live_secret_key'] = $this->config->get('payment_dasgatewayqr_live_secret_key');
		}
		
		if (isset($this->request->post['payment_dasgatewayqr_test_publishable_key'])) {
			$data['payment_dasgatewayqr_test_publishable_key'] = $this->request->post['payment_dasgatewayqr_test_publishable_key'];
		} else {
			$data['payment_dasgatewayqr_test_publishable_key'] = $this->config->get('payment_dasgatewayqr_test_publishable_key');
		}
		
		if (isset($this->request->post['payment_dasgatewayqr_test_secret_key'])) {
			$data['payment_dasgatewayqr_test_secret_key'] = $this->request->post['payment_dasgatewayqr_test_secret_key'];
		} else {
			$data['payment_dasgatewayqr_test_secret_key'] = $this->config->get('payment_dasgatewayqr_test_secret_key');
		}
		
		if (isset($this->request->post['payment_dasgatewayqr_merchant_id'])) {
			$data['payment_dasgatewayqr_merchant_id'] = $this->request->post['payment_dasgatewayqr_merchant_id'];
		} else {
			$data['payment_dasgatewayqr_merchant_id'] = $this->config->get('payment_dasgatewayqr_merchant_id');
		}
		
		if (isset($this->request->post['payment_dasgatewayqr_mode'])) {
			$data['payment_dasgatewayqr_mode'] = $this->request->post['payment_dasgatewayqr_mode'];
		} else {
			$data['payment_dasgatewayqr_mode'] = $this->config->get('payment_dasgatewayqr_mode');
		}
		
		if (isset($this->request->post['payment_dasgatewayqr_currency'])) {
			$data['payment_dasgatewayqr_currency'] = $this->request->post['payment_dasgatewayqr_currency'];
		} else {
			$data['payment_dasgatewayqr_currency'] = $this->config->get('payment_dasgatewayqr_currency');
		}
		
		if (isset($this->request->post['payment_dasgatewayqr_success_status_id'])) {
			$data['payment_dasgatewayqr_success_status_id'] = $this->request->post['payment_dasgatewayqr_success_status_id'];
		} else {
			$data['payment_dasgatewayqr_success_status_id'] = $this->config->get('payment_dasgatewayqr_success_status_id');
		}
		
		if (isset($this->request->post['payment_dasgatewayqr_declined_status_id'])) {
			$data['payment_dasgatewayqr_declined_status_id'] = $this->request->post['payment_dasgatewayqr_declined_status_id'];
		} else {
			$data['payment_dasgatewayqr_declined_status_id'] = $this->config->get('payment_dasgatewayqr_declined_status_id');
		}
		
		if (isset($this->request->post['payment_dasgatewayqr_geo_zone_id'])) {
			$data['payment_dasgatewayqr_geo_zone_id'] = $this->request->post['payment_dasgatewayqr_geo_zone_id'];
		} else {
			$data['payment_dasgatewayqr_geo_zone_id'] = $this->config->get('payment_dasgatewayqr_geo_zone_id');
		}
		

		if (isset($this->request->post['payment_dasgatewayqr_sort_order'])) {
			$data['payment_dasgatewayqr_sort_order'] = $this->request->post['payment_dasgatewayqr_sort_order'];
		} else {
			$data['payment_dasgatewayqr_sort_order'] = $this->config->get('payment_dasgatewayqr_sort_order');
		}

		if (isset($this->request->post['payment_dasgatewayqr_status'])) {
			$data['payment_dasgatewayqr_status'] = $this->request->post['payment_dasgatewayqr_status'];
		} else {
			$data['payment_dasgatewayqr_status'] = $this->config->get('payment_dasgatewayqr_status');
		}

		if (isset($this->request->post['payment_dasgatewayqr_3d_secure'])) {
			$data['payment_dasgatewayqr_3d_secure'] = $this->request->post['payment_dasgatewayqr_3d_secure'];
		} else {
			$data['payment_dasgatewayqr_3d_secure'] = $this->config->get('payment_dasgatewayqr_3d_secure');
		}

		if (isset($this->request->post['payment_dasgatewayqr_order_status_void_id'])) {
			$data['payment_dasgatewayqr_order_status_void_id'] = $this->request->post['payment_dasgatewayqr_order_status_void_id'];
		} else {
			$data['payment_dasgatewayqr_order_status_void_id'] = $this->config->get('payment_dasgatewayqr_order_status_void_id');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		

		$this->response->setOutput($this->load->view('extension/payment/dasgatewayqr', $data));
	}

	/**Module Installation*/
	public function install() {
		$this->load->model('extension/payment/dasgatewayqr');
		$this->model_extension_payment_dasgatewayqr->install();
	}

	/**Module Uninstall function*/
	public function uninstall() {
		$this->load->model('extension/payment/dasgatewayqr');
		$this->model_extension_payment_dasgatewayqr->uninstall();
	}	

	/**Validate Udmin fields*/
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/dasgatewayqr')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ($this->request->post['payment_dasgatewayqr_mode'] == 'Live' && !$this->request->post['payment_dasgatewayqr_live_publishable_key']) {
			$this->error['error_live_publishable_key'] = $this->language->get('error_publishable_key');
		}
		
		if ($this->request->post['payment_dasgatewayqr_mode'] == 'Test' && !$this->request->post['payment_dasgatewayqr_test_publishable_key']) {
			$this->error['error_test_publishable_key'] = $this->language->get('error_publishable_key');
		}
		
		if (!$this->request->post['payment_dasgatewayqr_merchant_id']) {
			$this->error['error_merchant_id'] = $this->language->get('error_merchant_id');
		}


		return !$this->error;
	}
}
