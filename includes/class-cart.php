<?php
/**
 * Extend WooCommerce Cart.
 *
 * @since   0.0.0
 * @package Extend_WooCommerce
 */

/**
 * Extend WooCommerce Cart.
 *
 * @since 0.0.0
 */
class EWC_Cart {
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
		//after cart add cart offers
		add_action('woocommerce_add_to_cart', [$this, 'add_to_cart'], 10, 6);

		//change cart item names for warranty items 
		add_filter('woocommerce_cart_item_name', [$this, 'cart_item_name'], 10, 3);

		//change mini cart item price for warranty items
		add_filter('woocommerce_cart_item_price', [$this, 'cart_item_price'], 10, 3);

		//change order item names for warranty items
		add_filter('woocommerce_order_item_name', [$this, 'order_item_name'], 10, 3);

		//update price for warranty items
		add_action('woocommerce_before_calculate_totals', [$this, 'update_price']);

		//set product and term data
		add_filter('woocommerce_get_item_data', [$this, 'checkout_details'], 10, 2);

		//add properties to warranty products
		add_action('woocommerce_checkout_create_order_line_item', [$this, 'order_item_meta'], 10, 3);
	}

	// order_item_meta($item, $cart_item_key, $cart_item)
	// @param $item : WC_Order_Item, represents order lineItem
    // @param $cart_item_key : cart item unique key
	// @param $cart_item : current cart item
	// This function transfers data from cart items, to order items
	public function order_item_meta($item, $cart_item_key, $cart_item ){
		if(isset($cart_item['extendData'])){
			$item->add_meta_data('_extend_data', $cart_item['extendData']);


			$covered_id = $cart_item['extendData']['covered_product_id'];
			$term = $cart_item['extendData']['term'];
			$title = $cart_item['extendData']['title'];
			$covered = wc_get_product($covered_id);
			$sku = $cart_item['extendData']['planId'];
			$covered_title = $covered->get_title();



			$item->add_meta_data('Warranty', $title);
			$item->add_meta_data('Warranty Term', $term . ' Months');
			$item->add_meta_data('Plan Id', $sku);
			$item->add_meta_data('Covered Product', $covered_title);

		}
	}

	// checkout_details($data, $cart_item)
	// @param $data : order item data
	// @param $cart_item : current cart item
	// @return $data : returns modified item data
	public function checkout_details($data, $cart_item){

		if(!is_cart() && !is_checkout()){
			return $data;
		}

		if(isset($cart_item['extendData'])){
			$covered_id = $cart_item['extendData']['covered_product_id'];
			$term = $cart_item['extendData']['term'];
			$covered = wc_get_product($covered_id);
			$sku = $cart_item['extendData']['planId'];
			$covered_title = $covered->get_title();
			$data[] =[
				'key'=>'Product',
				'value'=>$covered_title
			];
			$data[] =[
				'key'=>'Term',
				'value'=>$term . ' Months'
			];

		}

		return $data;

	}

	// update_price($cart_object)
	// @param $cart_object : WC_Cart, represents current cart object
	public function update_price($cart_object){
		$cart_items = $cart_object->cart_contents;

		if ( ! empty( $cart_items ) ) {

			foreach ( $cart_items as $key => $value ) {
				if(isset($value['extendData'])){
					$value['data']->set_price( round($value['extendData']['price']/100, 2) );
				}

			}
		}
	}

	// order_item_name($name, $cart_item, $cart_item_key)
	// @param $name : current items name
	// @param $cart_item : current cart item
	// @param $cart_item_key : unique key
	// @return $name or Extend Protection Plan for warranties
	public function order_item_name($name, $cart_item, $cart_item_key){

		$meta = $cart_item->get_meta('_extend_data');
		if($meta){
			return $meta['title'];
		}

		return $name;

	}

	// add_to_cart($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data)
	// @param $cart_item_key : unique key for cart item
	// @param $product_id : current cart items product id
	// @param $quantity : current cart items quantity
	// @param $variation_id : current variant id
	// @param $variation : current variant object
	// @param $cart_item_data : data object for cart item
	public function add_to_cart($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data){

		if(isset($_POST['planData'])){
			$plan = json_decode(str_replace('\\', '', $_POST['planData']), true);
			unset($_POST['planData']);
			if(empty($plan)){
				return;
			}
			$plan['covered_product_id'] = $variation_id?$variation_id: $product_id;
			$qty = filter_input(INPUT_POST, 'quantity');
			try{

				WC()->cart->add_to_cart($this->warranty_product_id, $qty, 0, 0, ['extendData'=>$plan] );
				
			}catch(Exception $e){
				error_log($e->getMessage());
			}
		}

		if(isset($_POST['extendData'])){

			$plan = $_POST['extendData'];
			WC()->cart->cart_contents[$cart_item_key]['extendData'] = $plan;
			$price = round($plan['price']/100, 2);

			WC()->cart->cart_contents[$cart_item_key]['data']->set_price($price);

		}

		if(isset($cart_item_data['extendData'])){

			$price = round($cart_item_data['extendData']['price']/100, 2);

			WC()->cart->cart_contents[$cart_item_key]['data']->set_price($price);

		}

	}

	// cart_item_name($name, $cart_item, $cart_item_key)
	// @param $name : current items name
	// @param $cart_item : current cart item
	// @param $cart_item_key : unique key for cart item
	// @return $name or new title for warranties
	public function cart_item_name($name, $cart_item, $cart_item_key){

		if(isset($cart_item['extendData'])){
			$term = $cart_item['extendData']['term'];
			return "Extend Protection Plan - {$term} Months";
		}

		return $name;

	}


	public function cart_item_price($price, $cart_item, $cart_item_key) {
		if(isset($cart_item['extendData'])) {
			$price = round($cart_item['extendData']['price']/100, 2);
			return "\${$number}";
		}
		return $price;
	}
}
