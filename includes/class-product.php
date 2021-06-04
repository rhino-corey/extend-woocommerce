<?php
/**
 * Extend WooCommerce Product.
 *
 * @since   0.0.0
 * @package Extend_WooCommerce
 */

/**
 * Extend WooCommerce Product.
 *
 * @since 0.0.0
 */
class EWC_Product {
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
		add_action('extend_product_sync', array( $this, 'main_product_sync' ));   
	}

	//main_product_sync gets all products and passes into udateProduct function
	public function main_product_sync() {
		$args = array(
			'orderby'  => 'name',
		);
		//woocommerce get all products in order by name
		$products = wc_get_products($args);

		//forEach product create an Extend product
		foreach ($products as $product) {
			$this->debug_to_console($product->get_id());
		};
	}

	function debug_to_console($data) {
		$output = $data;
		if (is_array($output))
			$output = implode(',', $output);
	
		echo "console.log(" . $output . ");";
	}

}
