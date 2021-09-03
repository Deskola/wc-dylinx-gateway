<?php

/*
*Plugin Name: Dylinx Payment gateway
*Plugin URI: https://ipayafrica.com
*Description: iPay Payment Gateway (WooCommerce Marketplace Compatible)
*version: 0.1.0
*Author: iPay
*Author URI: htpps://ipayafrica.com
*text-domain: dylinx-pay-woo
 */

if (! in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
	return;
}

add_action('plugins_loaded', 'wc_dylinx_gateway_init');

function wc_dylinx_gateway_init()
{
	if (!class_exists('WC_Dylinx_Payment_Gateway')) {
		class WC_Dylinx_Payment_Gateway extends WC_Payment_Gateway{
			public function __construct()
			{
				$this->id = 'dylinx_payment';
				$this->icon = apply_filters('woocommerce_ipay_icon', plugins_url('assets/icon.png',__FILE__));
				$this->has_fields = false;
				$this->method_title = __('Dylinx Payment', 'dylinx-pay-woo');
				$this->method_description = __('Dylinx Payment', 'Dylinx Payment Gateway (WooCommerce Marketplace Compatible)', 'dylinx-pay-woo');
				$this->title = $this->get_option('title');
				$this->description = $this->get_option('description');
				$this->instructions = $this->get_option('instructions', $this->description);

				$this->init_form_fields();
				$this->init_settings();

				add_action('woocommerce_update_options_payment_gateways_'.$this->id, array($this, 'process_admin_options'));
				add_action('woocommerce_thank_you_'.$this->id, array($this, 'thank_you_page'));
			}

			public function init_form_fields()
			{
				$this->form_fields = apply_filters(
					'woo_dylinx_pay_fields', array(
						'enabled' => array(
							'title' => __( 'Enable/Disable', 'dylinx-pay-woo'),
							'type' => 'checkbox',
							'label' => __( 'Enable or Disable Dylinx Payment', 'dylinx-pay-woo'),
							'default' => 'no'
						),
						'title' => array(
							'title' => __( 'Dylinx Payment Gateway', 'dylinx-pay-woo'),
							'type' => 'text',
							'description' => __( 'Add new title for the Dylinx Payment that customers will see when they are in checkout page', 'dylinx-pay-woo'),
							'default' => __( 'Dylinx Payment Gateway', 'dylinx-pay-woo'),
							'desc_tip' => true
						),
						'description' => array(
							'title' => __( 'Description', 'dylinx-pay-woo'),
							'type' => 'textarea',
							'description' => __( 'Add new title for the Dylinx Payment that customers will see when they are in checkout page', 'dylinx-pay-woo'),
							'default' => __( 'Please remit your payment to the shop to allow for the delivery to be made', 'dylinx-pay-woo'),
							'desc_tip' => true
						),
						'instructions' => array(
							'title' => __( 'Instructions', 'dylinx-pay-woo'),
							'type' => 'textarea',
							'description' => __( 'Instructions that will be added to the thank you page and order email'),
							'default' => __( 'Default Instructions', 'dylinx-pay-woo'),
							'desc_tip' => true
						),
					)
				);
			}

			public function process_payment($order_id)
			{
				$order = wc_get_order($order_id);

				$order->update_status('pending', __('Awaiting Dylinx Payment', 'dylinx-pay-woo'));

				//Add our own methods like call to api endpoint
				$this->clear_payment_with_api();

				$order->reduce_order_stock();

				WC()->cart->empty_cart();

				return array(
					'result' => 'success',
					'redirect' => $this->get_return_url($order),
				);
			}

			public function clear_payment_with_api()
			{
				// code...
			}
		}
	}
}

add_filter('woocommerce_payment_gateways', 'add_to_woo_dylinx_payment_gateway');

function add_to_woo_dylinx_payment_gateway($gateways){
	$gateways[] = 'WC_Dylinx_Payment_Gateway';
	return $gateways;
}