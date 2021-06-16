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
	protected $products = [];
	protected $updates = [];

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
		add_action('woocommerce_check_cart_items', [$this, 'validate_cart']);
		add_action('wp_ajax_get_cart', [$this, 'get_cart']);
		add_action( 'wp_ajax_nopriv_get_cart', [$this, 'get_cart'] );
	}

	public function get_cart(){
		$cart = WC()->cart;
		echo json_encode($cart, JSON_PRETTY_PRINT);
		die();
	}

	public function get_cart_updates() {

		foreach(WC()->cart->get_cart_contents() as $line){
			//if we're on a warranty item
			if(intval($line['product_id']) === intval($this->warranty_product_id) && isset($line['extendData'])){
				//Grab reference id
				$product_reference_id = 
					$line['extendData']['covered_product_id'];

				//If this product doesn't exist, create it with the warranty quantity and warranty added
				if(!isset($products[$product_reference_id])) {
					$products[$product_reference_id] = ['quantity'=>0, 'warranty_quantity'=>$line['quantity'], 'warranties'=>[$line]];
				} else {
					$products[$product_reference_id]['warranty_quantity'] += $line['quantity'];
					array_push($products[$product_reference_id]['warranties'], $line);
				}
			} else {
				$id = $line['variation_id']>0?$line['variation_id']:$line['product_id'];
				if(!isset($products[$id])) {
					$products[$id] = ['quantity'=>$line['quantity'], 'warranty_quantity'=>0, 'warranties'=>[]];
				} else {
					$products[$id]['quantity'] += $line['quantity'];
				}
			}
		}



		foreach($products as $product){

			if(intval($product['warranty_quantity'])>0 && intval($product['quantity'])==0) {
				foreach($product['warranties'] as $warranty){
					$updates[$warranty['key']] = ['quantity'=>0];
				}
			}else {
				$diff = $product['warranty_quantity'] - $product['quantity'];

				if($diff!==0){
					if($diff>0){
						foreach($product['warranties'] as $warranty){
							$new_quantity_diff = max([0, $diff - $warranty['quantity']]);

							$removed_quantity = $diff - $new_quantity_diff;
							$updates[$warranty['key']] = ['quantity'=>$warranty['quantity']-$removed_quantity];
							$diff=$new_quantity_diff;
						}
					}
				}
			}
		}

		if(isset($updates)){
			return $updates;
		}
	}

	public function normalize_cart(){
		$newUpdates = $this->get_cart_updates();
		
		if(isset($newUpdates)){
			$cart = WC()->cart->get_cart_contents();
			foreach($cart as $line){
				foreach($newUpdates as $key=>$value) {
					if($key==$line['key']){
						WC()->cart->set_quantity($key, $value['quantity'], true);
					}
				}
			}

		}
		return WC()->cart;
	}

	public function validate_cart(){
		$newCart = $this->normalize_cart();
	}

	public function after_cart_item_name($cart_item, $key){

		if(!isset($cart_item['extendData'])){

			$item_id = $cart_item['variation_id']?$cart_item['variation_id']:$cart_item['product_id'];
			echo "<div id='offer_$item_id' class='cart-extend-offer' data-covered='$item_id'> ";
		}

	}

	public function cart_offers(){
		$offers = [];

		$cart = WC()->cart;

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
					$ajaxurl = admin_url( 'admin-ajax.php' );
					wp_localize_script('extend_cart_integration_script', 'WCCartExtend', compact('store_id',  'ids', 'environment', 'warranty_prod_id', 'cart', 'ajaxurl'));
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
