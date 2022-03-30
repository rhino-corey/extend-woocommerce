<?php
/**
 * Extend WooCommerce Global.
 *
 * @since   0.0.0
 * @package Extend_WooCommerce
 */

/**
 * Extend WooCommerce Global.
 *
 * @since 0.0.0
 */
class EWC_Global {
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
		// add to cart for users without permissions
		add_action('wp_ajax_nopriv_add_to_cart_extend', [$this, 'add_to_cart_extend'], 10);

		// add to cart for users with permissions
		add_action('wp_ajax_add_to_cart_extend', [$this, 'add_to_cart_extend'], 10);

		// get cart for users without permissions
		add_action('wp_ajax_nopriv_get_cart_extend', [$this, 'get_cart_extend'], 10);

		// get cart for users with permissions
		add_action('wp_ajax_get_cart_extend', [$this, 'get_cart_extend'], 10);

		// Initialize global ExtendWooCommerce
		add_action('wp_head',[$this, 'init_global_extend']);
	}

	// get_cart_extend()
    // echos the cart to be used on the FE from ajax request
	public static function get_cart_extend(){
		$cart = WC()->cart;
		echo json_encode($cart, JSON_PRETTY_PRINT);
		die();
	}

	public static function add_to_cart_extend(){
		$warranty_product_id = get_option('wc_extend_product_id');
		$quantity = $_REQUEST['quantity'];
		$extend_data = $_REQUEST['extendData'];

		if(!isset($warranty_product_id) || !isset($quantity) || !isset($extend_data)) {
			return;
		}

		$cart_item_key = WC()->cart->add_to_cart( $warranty_product_id, $quantity, 0, 0, ['extendData' => $extend_data]);
	}

	public function init_global_extend() {
		if ( is_admin() ) { return; }
		$store_id = get_option('wc_extend_store_id');
		$extend_enabled = get_option('wc_extend_enabled');
		$environment = $this->plugin->env;
		$ajaxurl = admin_url( 'admin-ajax.php' );

		if($store_id && ($extend_enabled === 'yes')){
			wp_enqueue_script('extend_script');
			wp_enqueue_script('extend_global_script');
			wp_localize_script('extend_global_script', 'ExtendWooCommerce', compact('store_id' , 'ajaxurl', 'environment'));
		}
	}
}
