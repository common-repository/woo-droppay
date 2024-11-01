<?php
/*
 * Plugin Name: WooCommerce DropPay
 * Plugin URI:
 * Description: With DropPay you can send and receive money with your smartphone and make payments safely
 * Author: Zero11
 * Author URI: https://www.zero11.it/
 * Version: 1.0.0
 * Text Domain: woo-droppay
 * Domain Path: /languages
 */

add_action('plugins_loaded', 'wc_droppay_init', 0);
function wc_droppay_init() {
	if (!class_exists('WC_Payment_Gateway')) return;

	load_plugin_textdomain('woo-droppay', false, basename(dirname(__FILE__)).'/languages/');

	if (!class_exists("WC_Droppay"))
		include_once('wc-droppay.php');
	if (!class_exists("DropPayCheckout"))		
		include_once('dp-checkout.php');
	if (!class_exists("DroppayLogger"))
    	include_once('dp-logger.php');

	add_filter('woocommerce_payment_gateways', 'wc_droppay_add_gateway');

	function wc_droppay_add_gateway($methods) {
		$methods[] = 'WC_Droppay';
		return $methods;
	}

	add_filter('plugin_action_links_'.plugin_basename( __FILE__ ), 'wc_droppay_action_links');
	function wc_droppay_action_links($links) {
		$plugin_links = array(
			'<a href="'.admin_url('admin.php?page=wc-settings&tab=checkout&section=droppay').'">'.__('Settings', 'woo-droppay').'</a>'
		);
		return array_merge($plugin_links, $links);
	}
}
