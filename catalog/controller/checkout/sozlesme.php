<?php 
class ControllerCheckoutSozlesme extends Controller { 
	public function index() {
		$this->load->language('checkout/sozlesme');
				$data['breadcrumbs'] = array();
				
		$data['breadcrumbs'][] = array(
			'href' => $this->url->link('common/home'),
			'text' => $this->language->get('text_home')
		);
		$this->load->model('setting/extension');
		$data['breadcrumbs'][] = array(
			'href' => $this->url->link('checkout/cart'),
			'text' => $this->language->get('heading_title')
		);

		if ($this->cart->hasProducts() || !empty($this->session->data['vouchers'])) {
			$data['heading_title'] = $this->language->get('heading_title');

			$data['text_recurring_item'] = $this->language->get('text_recurring_item');
			$data['text_next'] = $this->language->get('text_next');
			$data['text_next_choice'] = $this->language->get('text_next_choice');

			$data['column_image'] = $this->language->get('column_image');
			$data['column_name'] = $this->language->get('column_name');
			$data['column_model'] = $this->language->get('column_model');
			$data['column_quantity'] = $this->language->get('column_quantity');
			$data['column_price'] = $this->language->get('column_price');
			$data['column_total'] = $this->language->get('column_total');

			$data['button_update'] = $this->language->get('button_update');
			$data['button_remove'] = $this->language->get('button_remove');
			$data['button_shopping'] = $this->language->get('button_shopping');
			$data['button_checkout'] = $this->language->get('button_checkout');

			if (!$this->cart->hasStock() && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
				$data['error_warning'] = $this->language->get('error_stock');
			} elseif (isset($this->session->data['error'])) {
				$data['error_warning'] = $this->session->data['error'];

				unset($this->session->data['error']);
			} else {
				$data['error_warning'] = '';
			}

			if ($this->config->get('config_customer_price') && !$this->customer->isLogged()) {
				$data['attention'] = sprintf($this->language->get('text_login'), $this->url->link('account/login'), $this->url->link('account/register'));
			} else {
				$data['attention'] = '';
			}

			if (isset($this->session->data['success'])) {
				$data['success'] = $this->session->data['success'];
		
				unset($this->session->data['success']);
			} else {
				$data['success'] = '';
			}

			$data['action'] = $this->url->link('checkout/cart/edit', '', true);

			if ($this->config->get('config_cart_weight')) {
				$data['weight'] = $this->weight->format($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->language->get('decimal_point'), $this->language->get('thousand_point'));
			} else {
				$data['weight'] = '';
			}

			$this->load->model('tool/image');
			$this->load->model('tool/upload');

			$data['products'] = array();

			$products = $this->cart->getProducts();
			foreach ($products as $product) {
				$product_total = 0;
				foreach ($products as $product_2) {
					if ($product_2['product_id'] == $product['product_id']) {
						$product_total += $product_2['quantity'];
					}
				}

				if ($product['minimum'] > $product_total) {
					$data['error_warning'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
				}

				

		

				$option_data = array();
				//opsiyon yok ise buraya girmez
				foreach ($product['option'] as $option) { 
					if ($option['type'] != 'file') {
						$value = $option['value'];
					} else {
						$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

						if ($upload_info) {
							$value = $upload_info['name'];
						} else {
							$value = '';
						}
					}

					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);
				}
				//Ürün fotograflarını alır
				if ($product['image']) {
					$image = $this->model_tool_image->resize($product['image'],
					$this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width'), 
					$this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_height'));
				} else {
					$image = '';
				}
				
				// Ürün fiyatını burada al
				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$unit_price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));
					
					$price = $this->currency->format($unit_price, $this->session->data['currency']);
					$total = $this->currency->format($unit_price * $product['quantity'], $this->session->data['currency']);
				} else {
					$price = false;
					$total = false;
				}
				
				$recurring = '';
				//Yinelenen ürünler
				if ($product['recurring']) {
					
					$frequencies = array(
						'day'        => $this->language->get('text_day'),
						'week'       => $this->language->get('text_week'),
						'semi_month' => $this->language->get('text_semi_month'),
						'month'      => $this->language->get('text_month'),
						'year'       => $this->language->get('text_year'),
					);

					if ($product['recurring']['trial']) {
						$recurring = sprintf($this->language->get('text_trial_description'), $this->currency->format($this->tax->calculate($product['recurring']['trial_price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['trial_cycle'], $frequencies[$product['recurring']['trial_frequency']], $product['recurring']['trial_duration']) . ' ';
					}

					if ($product['recurring']['duration']) {
						$recurring .= sprintf($this->language->get('text_payment_description'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
					} else {
						$recurring .= sprintf($this->language->get('text_payment_cancel'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
					}
				}

				$data['products'][] = array(
					'cart_id'   => $product['cart_id'],
					'thumb'     => $image,
					'name'      => $product['name'],
					'model'     => $product['model'],
					'option'    => $option_data,
					'recurring' => $recurring,
					'quantity'  => $product['quantity'],
					'stock'     => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
					'reward'    => ($product['reward'] ? sprintf($this->language->get('text_points'), $product['reward']) : ''),
					'price'     => $price,
					'total'     => $total,
					'href'      => $this->url->link('product/product', 'product_id=' . $product['product_id'])
				);
				
			}

			// Hediye çeki var mı
			$data['vouchers'] = array();
			
			if (!empty($this->session->data['vouchers'])) {
				foreach ($this->session->data['vouchers'] as $key => $voucher) {
					$data['vouchers'][] = array(
						'key'         => $key,
						'description' => $voucher['description'],
						'amount'      => $this->currency->format($voucher['amount'], $this->session->data['currency']),
						'remove'      => $this->url->link('checkout/cart', 'remove=' . $key)
					);
				}
			}
			
		
			// Toplam fiyat
			//$this->load->model('extension/extension');
			$this->load->model('setting/extension');
			

			$totals = array();
			$taxes = $this->cart->getTaxes();
			$total = 0;
			
			$total_data = array(
				'totals' => &$totals,
				'taxes'  => &$taxes,
				'total'  => &$total
			);
			
			// Fiyat göster
			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$sort_order = array();
				//$results = $this->model_extension_extension->getExtensions('total');
				$results = $this->model_setting_extension->getExtensions('total');
			
				foreach ($results as $key => $value) {
					$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
				}
				
				array_multisort($sort_order, SORT_ASC, $results);

				foreach ($results as $result) {
					if ($this->config->get($result['code'] . '_status')) {
						$this->load->model('extension/total/' . $result['code']);
						
						//Toplamları bir diziye koymalıyız, böylece referans olarak geçsinler.
						$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
					}
					
				}
			
				$sort_order = array();

				foreach ($totals as $key => $value) {
					$sort_order[$key] = $value['sort_order'];
				}

				array_multisort($sort_order, SORT_ASC, $totals);
			}

			$data['totals'] = array();

			foreach ($totals as $total) {
				$data['totals'][] = array(
					'title' => $total['title'],
					'text'  => $this->currency->format($total['value'], $this->session->data['currency'])
				);
				
			}

			$data['continue'] = $this->url->link('common/home');

			$data['checkout'] = $this->url->link('checkout/checkout', '', true);
			
			$this->load->model('setting/extension');

			$data['modules'] = array();
			
			$files = glob(DIR_APPLICATION . '/controller/extension/total/*.php');

			if ($files) {
				foreach ($files as $file) {
					$result = $this->load->controller('extension/total/' . basename($file, '.php'));
					
					if ($result) {
						$data['modules'][] = $result;
					}
				}
			}
			//echo "----<pre>----"; print_r($data); echo "----</pre>----"; exit;
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');
			
			$this->response->setOutput($this->load->view('checkout/cart', $data));
			//$this->response->setOutput($this->load->view('extension/quickcheckout/checkout', $data));

		} else {
			$data['heading_title'] = $this->language->get('heading_title');

			$data['text_error'] = $this->language->get('text_empty');

			$data['button_continue'] = $this->language->get('button_continue');

			$data['continue'] = $this->url->link('common/home');

			unset($this->session->data['success']);

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
			
		}
	
	
	
		//Mesafeli Satış Sözleşmesi Bilgileri
		$this->load->model('setting/setting');
		
		//$this->language->load('setting/setting');
		//$this->load->language('information/contact');
		$data['entry_name'] = "Firma:";
		$data['entry_owner'] = "Yetkili/Sahibi:";
		$data['entry_address'] = "Adresi:";
		$data['entry_email'] = "E-Posta:";
		$data['entry_telephone'] = "Telefon:";
		$data['entry_fax'] = "Faks:";
		
		
		$data['config_name'] = $this->config->get('config_name');
		$data['config_owner'] = $this->config->get('config_owner');
		//$data['config_address'] = $this->config->get('config_address');
		$data['config_address'] = nl2br($this->config->get('config_address'));
		$data['config_email'] = $this->config->get('config_email');
		$data['config_telephone'] = $this->config->get('config_telephone');
		$data['config_fax'] = $this->config->get('config_fax');			
	
		
		if($this->customer->isLogged()){
			//Müşteri bilgisi başla
			$this->load->model('account/customer');
			$customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
		
				$data['firstname'] = $customer_info['firstname'];
				$data['lastname'] = $customer_info['lastname'];
				$data['email'] = $customer_info['email'];
				$data['telephone'] = $customer_info['telephone'];
				$data['fax'] = $customer_info['fax'];
		
			$address_id = $this->customer->getAddressId();
			$this->load->model('account/address');
			$address = $this->model_account_address->getAddress($address_id);			
		
				$data['company'] 	=  $address['company'];
				$data['address_1'] 	=  $address['address_1'];
				$data['address_2'] 	=  $address['address_2'];
				$data['postcode'] 	=  $address['postcode'];
				$data['city'] 		=  $address['city'];
				$data['zone'] 		=  $address['zone'];
				$data['zone_code'] 	=  $address['zone_code'];
				$data['country_id'] =  $address['country_id'];
				$data['country'] 	=  $address['country'];
			
			// fatura bilgisinin eklenmesi
			if (isset($this->session->data['payment_address']['address_id'])) {
				$payment_address_id = $this->session->data['payment_address']['address_id'];
			} else {
				$payment_address_id = $this->customer->getAddressId();
			} 
			$this->load->model('account/address');
			$payment_address = $this->model_account_address->getAddress($payment_address_id);
				$data['payment_address_id'] = $payment_address['address_id'];
				$data['payment_firstname'] = $payment_address['firstname'];
				$data['payment_lastname'] = $payment_address['lastname'];	
				$data['payment_company'] = $payment_address['company'];		
				$data['payment_address_1'] = $payment_address['address_1'];
				$data['payment_address_2'] = $payment_address['address_2'];
				$data['payment_postcode'] = $payment_address['postcode'];
				$data['payment_city'] = $payment_address['city'];	
				$data['payment_zone_id'] = $payment_address['zone_id'];
				$data['payment_zone'] = $payment_address['zone'];
				$data['payment_zone_code'] = $payment_address['zone_code'];
				$data['payment_country_id'] = $payment_address['country_id'];
				$data['payment_country'] = $payment_address['country'];
			

			// Teslimat bilgisinin eklenmesi
			if (isset($this->session->data['shipping_address']['address_id'])) {
				$shipping_soz_address_id = $this->session->data['shipping_address']['address_id'];
			} else {
				$shipping_soz_address_id = $this->customer->getAddressId();
			}
			$this->load->model('account/address');
			$shipping_soz_address = $this->model_account_address->getAddress($shipping_soz_address_id);
				$data['shipping_soz_address_id'] = $shipping_soz_address['address_id'];
				$data['shipping_soz_firstname'] = $shipping_soz_address['firstname'];
				$data['shipping_soz_lastname'] = $shipping_soz_address['lastname'];	
				$data['shipping_soz_company'] = $shipping_soz_address['company'];		
				$data['shipping_soz_address_1'] = $shipping_soz_address['address_1'];
				$data['shipping_soz_address_2'] = $shipping_soz_address['address_2'];
				$data['shipping_soz_postcode'] = $shipping_soz_address['postcode'];
				$data['shipping_soz_city'] = $shipping_soz_address['city'];	
				$data['shipping_soz_zone_id'] = $shipping_soz_address['zone_id'];
				$data['shipping_soz_zone'] = $shipping_soz_address['zone'];
				$data['shipping_soz_zone_code'] = $shipping_soz_address['zone_code'];
				$data['shipping_soz_country_id'] = $shipping_soz_address['country_id'];
				$data['shipping_soz_country'] = $shipping_soz_address['country'];
			
	
			//Müşteri düzenleme sonu
		}else {
			//Misafir satışı destekleyen siteler için
			
			// Misafir bilgileri
			if (isset($this->session->data['guest']['firstname'])) {
				$data['firstname'] = $this->session->data['guest']['firstname'];
			} else {
				$data['firstname'] = '';
			}
	
			if (isset($this->session->data['guest']['lastname'])) {
				$data['lastname'] = $this->session->data['guest']['lastname'];
			} else {
				$data['lastname'] = '';
			}
			
			if (isset($this->session->data['guest']['email'])) {
				$data['email'] = $this->session->data['guest']['email'];
			} else {
				$data['email'] = '';
			}
			
			if (isset($this->session->data['guest']['telephone'])) {
				$data['telephone'] = $this->session->data['guest']['telephone'];		
			} else {
				$data['telephone'] = '';
			}
			
			if (isset($this->session->data['guest']['fax'])) {
				$data['fax'] = $this->session->data['guest']['fax'];				
			} else {
				$data['fax'] = '';
			}
	
			if (isset($this->session->data['payment_address']['company'])) {
				$data['company'] = $this->session->data['payment_address']['company'];
			} else {
				$data['company'] = '';
			}

			if (isset($this->session->data['payment_address']['address_1'])) {
				$data['address_1'] = $this->session->data['payment_address']['address_1'];
			} else {
				$data['address_1'] = '';
			}

			if (isset($this->session->data['payment_address']['address_2'])) {
				$data['address_2'] = $this->session->data['payment_address']['address_2'];
			} else {
				$data['address_2'] = '';
			}

			//34000
			if (isset($this->session->data['payment_address']['postcode'])) {
				$data['postcode'] = $this->session->data['payment_address']['postcode'];
			} else {
				$data['postcode'] = '';
			}

			//�sk�dar
			if (isset($this->session->data['payment_address']['city'])) {
				$data['city'] = $this->session->data['payment_address']['city'];
			} else {
				$data['city'] = '';
			}
			
			//�stanbul
			if (isset($this->session->data['payment_address']['zone'])) {
				$data['zone'] = $this->session->data['payment_address']['zone'];
			} else {
				$data['zone'] = '';
			}
			
			//T�rkiye
			if (isset($this->session->data['payment_address']['country_id'])) {
				$data['country_id'] = $this->session->data['payment_address']['country_id'];
			} else {
				$data['country_id'] = $this->config->get('config_country_id');
			}
			if ( isset($data['country_id']) ) {
				$this->load->model('localisation/country');
				$country_info= $this->model_localisation_country->getCountry($data['country_id']);
				$data['country'] = $country_info['name'];
			}
			else {
				$data['country'] = '';
			}
			
			$data['payment_firstname'] = $data['firstname'];
			$data['payment_lastname'] = $data['lastname'];
			$data['payment_fax'] = $data['fax'];			
			$data['payment_company'] = $data['company'];		
			$data['payment_address_1'] = $data['address_1'];
			$data['payment_address_2'] = $data['address_2'];
			$data['payment_postcode'] = $data['postcode'];
			$data['payment_city'] = $data['city'];
			$data['payment_zone'] = $data['zone'];
			$data['payment_country'] = $data['country'];
			
			//Müşteri teslimat bilgileri
			if (isset($this->session->data['shipping_address']['firstname'])) {
				$data['shipping_soz_firstname'] = $this->session->data['shipping_address']['firstname'];
			} else {
				$data['shipping_soz_firstname'] = '';
			}

			if (isset($this->session->data['shipping_address']['lastname'])) {
				$data['shipping_soz_lastname'] = $this->session->data['shipping_address']['lastname'];
			} else {
				$data['shipping_soz_lastname'] = '';
			}

			if (isset($this->session->data['shipping_address']['company'])) {
				$data['shipping_soz_company'] = $this->session->data['shipping_address']['company'];
			} else {
				$data['shipping_soz_company'] = '';
			}

			if (isset($this->session->data['shipping_address']['address_1'])) {
				$data['shipping_soz_address_1'] = $this->session->data['shipping_address']['address_1'];
			} else {
				$data['shipping_soz_address_1'] = '';
			}

			if (isset($this->session->data['shipping_address']['address_2'])) {
				$data['shipping_soz_address_2'] = $this->session->data['shipping_address']['address_2'];
			} else {
				$data['shipping_soz_address_2'] = '';
			}

			
			if (isset($this->session->data['shipping_address']['postcode'])) {
				$data['shipping_soz_postcode'] = $this->session->data['shipping_address']['postcode'];
			} else {
				$data['shipping_soz_postcode'] = '';
			}

			
			if (isset($this->session->data['shipping_address']['city'])) {
				$data['shipping_soz_city'] = $this->session->data['shipping_address']['city'];
			} else {
				$data['shipping_soz_city'] = '';
			}

		
			if (isset($this->session->data['shipping_address']['zone'])) {
				$data['shipping_soz_zone'] = $this->session->data['shipping_address']['zone'];
			} else {
				$data['shipping_soz_zone'] = '';
			}
			
		
			if (isset($this->session->data['shipping_address']['country_id'])) {
				$data['shipping_soz_country_id'] = $this->session->data['shipping_address']['country_id'];
			} else {
				$data['shipping_soz_country_id'] = $this->config->get('config_country_id');
			}
			if ( isset($data['shipping_soz_country_id']) ) {
				$this->load->model('localisation/country');
				$shipping_soz_country_info= $this->model_localisation_country->getCountry($data['shipping_soz_country_id']);
				$data['shipping_soz_country'] = $shipping_soz_country_info['name'];
			}
			else {
				$data['shipping_soz_country'] = '';
			}
						
			
		}					
		$redirect = '';
		
		if ($this->cart->hasShipping()) {
				
			$this->load->model('account/address');
	
			if ($this->customer->isLogged() && isset($this->session->data['shipping_address_id'])) {					
				$shipping_address = $this->model_account_address->getAddress($this->session->data['shipping_address_id']);		
			} elseif (isset($this->session->data['guest'])) {
				$shipping_address = $this->session->data['guest']['shipping_address'];
			}
			
			if (empty($shipping_address)) {								
				$redirect = $this->url->link('checkout/checkout', '', 'SSL');
			}
				
			if (!isset($this->session->data['shipping_method'])) {
				$redirect = $this->url->link('checkout/checkout', '', 'SSL');
			}
		} else {
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
		}

		$this->load->model('account/address');
		
		if ($this->customer->isLogged() && isset($this->session->data['payment_address_id'])) {
			$payment_address = $this->model_account_address->getAddress($this->session->data['payment_address_id']);		
		} elseif ( isset($this->session->data['guest']) && isset($this->session->data['guest']['payment']) ) {
			$payment_address = $this->session->data['guest']['payment'];
		}
		
		if (empty($payment_address)) {
			$redirect = $this->url->link('checkout/checkout', '', 'SSL');
		}			
		
		 
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$redirect = $this->url->link('checkout/cart');				
		}	
		
		$products = $this->cart->getProducts();
				
		foreach ($products as $product) {
			$product_total = 0;
				
			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}		
			
			if ($product['minimum'] > $product_total) {
				$redirect = $this->url->link('checkout/cart');
				
				break;
			}				
		}
		
		if (!$redirect) {
			$total_data = array();
			$total = 0;
			$taxes = $this->cart->getTaxes();
			 
			$this->load->model('setting/extension');
			
			$sort_order = array(); 
			
			$results = $this->model_setting_extension->getExtensions('total');
			
			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
			}
			
			array_multisort($sort_order, SORT_ASC, $results);
			
			foreach ($results as $result) {
				if ($this->config->get($result['code'] . '_status')) {
					$this->load->model('total/' . $result['code']);
		
					$this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
				}
			}
			
			$sort_order = array(); 
		  
			foreach ($total_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}
	
			array_multisort($sort_order, SORT_ASC, $total_data);
	
			$this->language->load('checkout/checkout');
			
			$data = array();
			
			$data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
			$data['store_id'] = $this->config->get('config_store_id');
			$data['store_name'] = $this->config->get('config_name');
			$data['config_address'] = nl2br($this->config->get('config_address'));
			$data['config_owner'] = $this->config->get('config_owner');
			//$data['config_email'] = $this->config->get('config_email');
			$data['config_email'] = 'iletisim@sosyalsahaf.com';
			$data['config_telephone'] = $this->config->get('config_telephone');
			
			/*
			$data['config_telephone'] = $this->config->get('config_telephone');
			$data['config_fax'] = $this->config->get('config_fax');	
			*/
			if ($data['store_id']) {
				$data['store_url'] = $this->config->get('config_url');		
			} else {
				$data['store_url'] = HTTP_SERVER;	
			}

		
		
			if ($this->customer->isLogged()) {
				$this->load->model('account/address');
				$data['customer_id'] = $this->customer->getId();
				$data['customer_group_id'] = $customer_group_id = $this->config->get('config_customer_group_id');
				//$data['customer_group_id'] = $this->customer->getCustomerGroupId();
				$data['firstname'] = $this->customer->getFirstName(); 
				$data['marketing_id'] = 0;
				$data['lastname'] = $this->customer->getLastName();
				$data['email'] = $this->customer->getEmail();
				$data['telephone'] = $this->customer->getTelephone();
				//$data['fax'] = $this->customer->getFax();
				//$this->load->model('account/address');
				//echo "Here"; exit;
				$payment_address = $this->model_account_address->getAddress($this->session->data['payment_address_id']);
			} elseif (isset($this->session->data['guest'])) {
				$data['customer_id'] = 0;
				$data['customer_group_id'] = $this->session->data['guest']['customer_group_id'];
				$data['firstname'] = $this->session->data['guest']['firstname'];
				$data['lastname'] = $this->session->data['guest']['lastname'];
				$data['email'] = $this->session->data['guest']['email'];
				$data['telephone'] = $this->session->data['guest']['telephone'];
				$data['fax'] = $this->session->data['guest']['fax'];
				
				$payment_address = $this->session->data['guest']['payment'];
			}
			
			$data['payment_firstname'] = $payment_address['firstname'];
			$data['payment_lastname'] = $payment_address['lastname'];	
			$data['payment_company'] = $payment_address['company'];	
			$data['payment_company_id'] = isset($payment_address['company_id']) ? $payment_address['company_id'] : '';
			$data['payment_tax_id'] = isset($payment_address['tax_id']) ? $payment_address['tax_id'] : '';
			$data['payment_address_1'] = $payment_address['address_1'];
			$data['payment_address_2'] = $payment_address['address_2'];
			$data['payment_city'] = $payment_address['city'];
			$data['payment_postcode'] = $payment_address['postcode'];
			$data['payment_zone'] = $payment_address['zone'];
			$data['payment_zone_id'] = $payment_address['zone_id'];
			$data['payment_country'] = $payment_address['country'];
			$data['payment_country_id'] = $payment_address['country_id'];
			$data['payment_address_format'] = $payment_address['address_format'];
			
			if (isset($this->session->data['payment_method']['title'])) {
				$data['payment_method'] = $this->session->data['payment_method']['title'];
			} else {
				$data['payment_method'] = '';
			}
			
			if (isset($this->session->data['payment_method']['code'])) {
				$data['payment_code'] = $this->session->data['payment_method']['code'];
			} else {
				$data['payment_code'] = '';
			}
						
			if ($this->cart->hasShipping()) {
				if ($this->customer->isLogged()) {
					$this->load->model('account/address');
					
					$shipping_address = $this->model_account_address->getAddress($this->session->data['shipping_address_id']);	
				} elseif (isset($this->session->data['guest'])) {
					$shipping_address = $this->session->data['guest']['shipping'];
				}			
				
				$data['shipping_firstname'] = $shipping_address['firstname'];
				$data['shipping_lastname'] = $shipping_address['lastname'];	
				$data['shipping_company'] = $shipping_address['company'];	
				$data['shipping_address_1'] = $shipping_address['address_1'];
				$data['shipping_address_2'] = $shipping_address['address_2'];
				$data['shipping_city'] = $shipping_address['city'];
				$data['shipping_postcode'] = $shipping_address['postcode'];
				$data['shipping_zone'] = $shipping_address['zone'];
				$data['shipping_zone_id'] = $shipping_address['zone_id'];
				$data['shipping_country'] = $shipping_address['country'];
				$data['shipping_country_id'] = $shipping_address['country_id'];
				$data['shipping_address_format'] = $shipping_address['address_format'];
			
				if (isset($this->session->data['shipping_method']['title'])) {
					$data['shipping_method'] = $this->session->data['shipping_method']['title'];
				} else {
					$data['shipping_method'] = '';
				}
				
				if (isset($this->session->data['shipping_method']['code'])) {
					$data['shipping_code'] = $this->session->data['shipping_method']['code'];
				} else {
					$data['shipping_code'] = '';
				}				
			} else {
				$data['shipping_firstname'] = '';
				$data['shipping_lastname'] = '';	
				$data['shipping_company'] = '';	
				$data['shipping_address_1'] = '';
				$data['shipping_address_2'] = '';
				$data['shipping_city'] = '';
				$data['shipping_postcode'] = '';
				$data['shipping_zone'] = '';
				$data['shipping_zone_id'] = '';
				$data['shipping_country'] = '';
				$data['shipping_country_id'] = '';
				$data['shipping_address_format'] = '';
				$data['shipping_method'] = '';
				$data['shipping_code'] = '';
			}
			
			$product_data = array();
		
			foreach ($this->cart->getProducts() as $product) {
				$option_data = array();
	
				foreach ($product['option'] as $option) {
					if ($option['type'] != 'file') {
						$value = $option['option_value'];	
					} else {
						$value = $this->encryption->decrypt($option['option_value']);
					}	
					
					$option_data[] = array(
						'product_option_id'       => $option['product_option_id'],
						'product_option_value_id' => $option['product_option_value_id'],
						'option_id'               => $option['option_id'],
						'option_value_id'         => $option['option_value_id'],								   
						'name'                    => $option['name'],
						'value'                   => $value,
						'type'                    => $option['type']
					);					
				}
	 
				$product_data[] = array(
					'product_id' => $product['product_id'],
					'name'       => $product['name'],
					'model'      => $product['model'],
					'option'     => $option_data,
					'download'   => $product['download'],
					'quantity'   => $product['quantity'],
					'subtract'   => $product['subtract'],
					'price'      => $product['price'],
					'total'      => $product['total'],
					'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
					'reward'     => $product['reward']
				); 
			}
			
			$voucher_data = array();
			
			if (!empty($this->session->data['vouchers'])) {
				foreach ($this->session->data['vouchers'] as $voucher) {
					$voucher_data[] = array(
						'description'      => $voucher['description'],
						'code'             => substr(md5(mt_rand()), 0, 10),
						'to_name'          => $voucher['to_name'],
						'to_email'         => $voucher['to_email'],
						'from_name'        => $voucher['from_name'],
						'from_email'       => $voucher['from_email'],
						'voucher_theme_id' => $voucher['voucher_theme_id'],
						'message'          => $voucher['message'],						
						'amount'           => $voucher['amount']
					);
				}
			}  
						
			$data['products'] = $product_data;
			$data['vouchers'] = $voucher_data;
			$data['totals'] = $total_data;
			$data['comment'] = $this->session->data['comment'];
			$data['total'] = $total;

if (isset($this->request->cookie['tracking'])) {
	$data['tracking'] = $this->request->cookie['tracking'];

	$subtotal = $this->cart->getSubTotal();

	$this->load->model('affiliate/affiliate');

	$affiliate_info = $this->model_affiliate_affiliate->getAffiliateByCode($this->request->cookie['tracking']);

	if ($affiliate_info) {
		$data['affiliate_id'] = $affiliate_info['affiliate_id'];
		$data['commission'] = ($subtotal / 100) * $affiliate_info['commission'];
	} else {
		$data['affiliate_id'] = 0;
		$data['commission'] = 0;
	}

	$this->load->model('checkout/marketing');
	$marketing_info = $this->model_checkout_marketing->getMarketingByCode($this->request->cookie['tracking']);

	if ($marketing_info) {
		$data['marketing_id'] = $marketing_info['marketing_id'];
	} else {
		$data['marketing_id'] = 0;
	}
} else {
		$data['affiliate_id'] = 0;
		$data['commission'] = 0;
		$data['marketing_id'] = 0;
		$data['tracking'] = '';
}
			$data['language_id'] = $this->config->get('config_language_id'); 
			$data['currency_id'] = $this->currency->getId($this->session->data['currency']);
			//$data['currency_code'] = '$this->session->data['currency'];'
			$data['currency_code'] = 'TRY';
			$data['currency_value'] = $this->currency->getValue($this->session->data['currency']);
			$data['ip'] = $this->request->server['REMOTE_ADDR'];
	
			if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
				$data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];	
			} elseif(!empty($this->request->server['HTTP_CLIENT_IP'])) {
				$data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];	
			} else {
				$data['forwarded_ip'] = '';
			}
			
			if (isset($this->request->server['HTTP_USER_AGENT'])) {
				$data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];	
			} else {
				$data['user_agent'] = '';
			}
			
			if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
				$data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];	
			} else {
				$data['accept_language'] = '';
			}
						
			$this->load->model('checkout/order');
			$this->session->data['order_id'] = $this->model_checkout_order->addOrder($data);

			$data['column_name'] = $this->language->get('column_name');
			$data['column_model'] = $this->language->get('column_model');
			$data['column_quantity'] = $this->language->get('column_quantity');
			$data['column_price'] = $this->language->get('column_price');
			$data['column_total'] = $this->language->get('column_total');
	
			$data['products'] = array();
	
			foreach ($this->cart->getProducts() as $product) {
				$option_data = array();
	
				foreach ($product['option'] as $option) {
					if ($option['type'] != 'file') {
						$value = $option['option_value'];	
					} else {
						$filename = $this->encryption->decrypt($option['option_value']);
						
						$value = utf8_substr($filename, 0, utf8_strrpos($filename, '.'));
					}
										
					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);
				}  
				
				$data['products'][] = array(
					'product_id' => $product['product_id'],
					//'thumb' => $product['image'],
					'thumb' => 	$image = $this->model_tool_image->resize($product['image'],$this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width'),$this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_height')),
					'name'       => $product['name'],
					'stock'     => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
					'reward'    => ($product['reward'] ? sprintf($this->language->get('text_points'), $product['reward']) : ''),
					'recurring' => $recurring,
					'model'      => $product['model'],
					'option'     => $option_data,
					'quantity'   => $product['quantity'],
					'subtract'   => $product['subtract'],
					//'price'      => $this->currency->format($this->tax->calculate($product['price'],$product['tax_class_id'],$this->config->get('config_tax'))),
					'price'      => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']),
					//'total'      => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity']),
					'total'      => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'], $this->session->data['currency']),
					'href'       => $this->url->link('product/product', 'product_id=' . $product['product_id'])
				); 
				//echo "<pre>"; print_r($product); echo "</pre>"; exit;
			} 
			
			$data['vouchers'] = array();
			
			if (!empty($this->session->data['vouchers'])) {
				foreach ($this->session->data['vouchers'] as $voucher) {
					$data['vouchers'][] = array(
						'description' => $voucher['description'],
						'amount'      => $this->currency->format($voucher['amount'])
					);
				}
			}  
						
			$data['totals'] = $total_data;
		}			
		
		//$this->response->setOutput($this->load->view('checkout/sozlesme', $data));
		//echo "<pre>"; print_r($data); echo "</pre>"; //exit;
		$this->response->setOutput($this->load->view('checkout/sozlesme', $data));
  	}	
}
?>