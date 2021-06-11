<?php
/**
 * Extend WooCommerce Cart Integration.
 *
 * @since   0.0.0
 * @package Extend_WooCommerce
 */

/**
 * Extend WooCommerce Cart Integration.
 *
 * @since 0.0.0
 */
class EWC_Cart_Integration {
	/**
	 * Parent plugin class.
	 *
	 * @since 0.0.0
	 *
	 * @var   Extend_WooCommerce
	 */
	protected $plugin = null;
	protected $warranty_product_id = null;

	/**
	 * Constructor.
	 *
	 * @since  0.0.0
	 *
	 * @param  Extend_WooCommerce $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
		$this->warranty_product_id = get_option('wc_extend_product_id');
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.0.0
	 */
	public function hooks() {
		add_action('woocommerce_after_cart', [$this, 'cart_offers']);
		add_action('woocommerce_after_cart_item_name', [$this, 'after_cart_item_name'], 10, 2);
		add_filter('woocommerce_add_cart_item_data', [$this, 'unique_cart_items'], 10, 2);
	}

	public function after_cart_item_name($cart_item, $key){


		if(!isset($cart_item['extendData'])){

			$item_id = $cart_item['variation_id']?$cart_item['variation_id']:$cart_item['product_id'];
			echo "<div id='offer_$item_id' class='cart-extend-offer' data-covered='$item_id'> ";
		}

	}

	public function cart_offers(){
		$offers = [];
		foreach(WC()->cart->get_cart_contents() as $line) {

			if ( intval( $line['product_id'] ) !== intval( $this->warranty_product_id ) ) {
				$offers[] =
					$line['variation_id']>0?$line['variation_id']:$line['product_id'];
			}
		}

		$store_id = get_option('wc_extend_store_id');
		$extend_enabled = get_option('wc_extend_enabled');
		$extend_cart_offers_enabled = get_option('wc_extend_cart_offers_enabled');

		$warranty_prod_id = $this->warranty_product_id;
		
		$environment = $this->plugin->env;
			

			$ids = array_unique($offers);
			if($store_id && ($extend_enabled === 'yes') && ($extend_cart_offers_enabled === 'yes')){
					wp_enqueue_script('extend_script');
					wp_enqueue_script('extend_cart_integration_script');
					wp_localize_script('extend_cart_integration_script', 'WCCartExtend', compact('store_id',  'ids', 'environment', 'warranty_prod_id'));
			}



	}

	public function unique_cart_items($cart_item_data, $product_id){

		if($product_id === intval($this->warranty_product_id)){
			$unique_cart_item_key = md5( microtime() . rand() );
			$cart_item_data['unique_key'] = $unique_cart_item_key;
	
		}
	
	
			return $cart_item_data;
			
		}
}
