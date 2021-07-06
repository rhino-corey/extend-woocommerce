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

		//after cart add cart offers
		add_action('woocommerce_after_cart', [$this, 'cart_offers']);

		//after cart item name add offer element
		add_action('woocommerce_after_cart_item_name', [$this, 'after_cart_item_name'], 10, 2);

		//ensure unique cart items for warranties
		add_filter('woocommerce_add_cart_item_data', [$this, 'unique_cart_items'], 10, 2);

		//run normalization on check
		add_action('woocommerce_check_cart_items', [$this, 'normalize_cart']);

		//get cart for users without permissions
		add_action('wp_ajax_nopriv_get_cart', [$this, 'get_cart']);

		//get cart for users with permissions
		add_action('wp_ajax_get_cart', [$this, 'get_cart']);

	}

	// get_cart()
    // echos the cart to be used on the FE from ajax request
	public static function get_cart(){
		$cart = WC()->cart;
		echo json_encode($cart, JSON_PRETTY_PRINT);
		die();
	}

	// get_cart_updates()
    // goes through the cart and get's updates to products/plans for normalization
	public function get_cart_updates() {

		foreach(WC()->cart->get_cart_contents() as $line){
			//if we're on a warranty item
			if(intval($line['product_id']) === intval($this->warranty_product_id) && isset($line['extendData'])){
				//Grab reference id
				$product_reference_id = 
					$line['extendData']['covered_product_id'];

				//If this product doesn't exist, create it with the warranty quantity and warranty added, else add to warranty quantity, and add warranty to warranty list
				if(!isset($products[$product_reference_id])) {
					$products[$product_reference_id] = ['quantity'=>0, 'warranty_quantity'=>$line['quantity'], 'warranties'=>[$line]];
				} else {
					$products[$product_reference_id]['warranty_quantity'] += $line['quantity'];
					array_push($products[$product_reference_id]['warranties'], $line);
				}
			//if we're on a non-warranty check if the product exists in list, if so add quantity, if not add to product list
			} else {
				$id = $line['variation_id']>0?$line['variation_id']:$line['product_id'];
				if(!isset($products[$id])) {
					$products[$id] = ['quantity'=>$line['quantity'], 'warranty_quantity'=>0, 'warranties'=>[]];
				} else {
					$products[$id]['quantity'] += $line['quantity'];
				}
			}
		}

		//if we have products, go through each and check for updates
		if(isset($products)){
			foreach($products as $product){
				
				//if warranty quantity is greater than 0 and product quantity is 0 set warranty quantity to 0
				if(intval($product['warranty_quantity'])>0 && intval($product['quantity'])==0) {
					foreach($product['warranties'] as $warranty){
						$updates[$warranty['key']] = ['quantity'=>0];
					}
				}else {
					//grab difference of warranty_quantity and product quantity
					$diff = $product['warranty_quantity'] - $product['quantity'];
					
					//if there's a difference & that difference is greater than 0, we remove warranties till we reach the product quantity
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
		}

		//if there's updates return updates
		if(isset($updates)){
			return $updates;
		}
	}

	// normalize_cart()
    // grabs & applies cart updates
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

	// after_cart_item_name($cart_item, $key)
	// @param $cart_item : cart_item contains item information
    // @param $key : key is the cart_item's key and is not used
    // echos the offer element to the cart page
	public function after_cart_item_name($cart_item, $key){
		//if it's not a warranty, add offer element
		if(!isset($cart_item['extendData'])){
			$item_id = $cart_item['variation_id']?$cart_item['variation_id']:$cart_item['product_id'];
			echo "<div id='offer_$item_id' class='cart-extend-offer' data-covered='$item_id'> ";
		}

	}

	// cart_offers()
    // renders cart offers
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
			if($store_id && ($extend_enabled === 'yes')){

					wp_enqueue_script('extend_script');
					wp_enqueue_script('extend_cart_integration_script');
					$ajaxurl = admin_url( 'admin-ajax.php' );
					wp_localize_script('extend_cart_integration_script', 'WCCartExtend', compact('store_id',  'ids', 'environment', 'warranty_prod_id', 'cart', 'ajaxurl', 'extend_cart_offers_enabled'));
			
			}

	}

	// unique_cart_items($cart_item_data, $product_id)
	// @param $cart_item_data : cart_item_data contains unique_key, if we have a warranty we need to randomize this
    // @param $product_id : contains wordpress post id for product
    // @return $cart_item_data : with warranties being altered with a new unique_key
	public function unique_cart_items($cart_item_data, $product_id){

		if($product_id === intval($this->warranty_product_id)){

			$unique_cart_item_key = md5( microtime() . rand() );
			$cart_item_data['unique_key'] = $unique_cart_item_key;
	
		}

			return $cart_item_data;

		}
}
