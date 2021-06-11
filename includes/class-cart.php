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
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.0.0
	 */
	public function hooks() {
		add_action('woocommerce_add_to_cart', [$this, 'add_to_cart'], 10, 6);
		add_filter('woocommerce_cart_item_name', [$this, 'cart_item_name'], 10, 3);
		add_filter('woocommerce_order_item_name', [$this, 'order_item_name'], 10, 3);
		add_action('woocommerce_before_calculate_totals', [$this, 'update_price']);
		add_filter('woocommerce_get_item_data', [$this, 'checkout_details'], 10, 2);
		add_action('woocommerce_checkout_create_order_line_item', [$this, 'order_item_meta'], 10, 3);
	}

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
				'key'=>'Covered Product',
				'value'=>$covered_title
			];
			$data[] =[
				'key'=>'Coverage Term',
				'value'=>$term . ' Months'
			];

		}



		return $data;
	}

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

	public function order_item_name($name, $cart_item, $cart_item_key){
		$meta = $cart_item->get_meta('_extend_data');
		if($meta){
			return $meta['title'];
		}

		return $name;
	}

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

	public function cart_item_name($name, $cart_item, $cart_item_key){

		if(isset($cart_item['extendData'])){
			$term = $cart_item['extendData']['term'];
			return "Extend Protection Plan - {$term} Months";
		}
		return $name;
	}
}
