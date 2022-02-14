<?php
/**
 * Extend WooCommerce Product Integration.
 *
 * @since   0.0.0
 * @package Extend_WooCommerce
 */

/**
 * Extend WooCommerce Product Integration.
 *
 * @since 0.0.0
 */
class EWC_Product_Integration {
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
		add_action('woocommerce_before_add_to_cart_button', [$this, 'product_offer']);
	}

	// product_offer()
	// grabs required variables, and enqueue's product scripts
	public function product_offer(){
		global $product;

		$id = $product->get_id();

		$store_id = get_option('wc_extend_store_id');
		$type = $product->get_type();
		$extend_enabled = get_option('wc_extend_enabled');
		
		$environment = $this->plugin->env;
		$extend_pdp_offers_enabled = get_option('wc_extend_pdp_offers_enabled');

		if($type === 'variable'){
			$ids = $product->get_children();
		}else{
			$ids = [$id];
		}
		if($store_id){
			if($extend_enabled === 'yes') {
				$extend_modal_offers_enabled = get_option('wc_extend_modal_offers_enabled');
				wp_enqueue_script('extend_script');
				wp_enqueue_script('extend_product_integration_script');
				wp_localize_script('extend_product_integration_script', 'WCExtend', compact('store_id', 'id', 'type', 'ids', 'environment', 'extend_modal_offers_enabled', 'extend_pdp_offers_enabled'));
				echo "<div id=\"extend-offer\"></div>";
			}
		}


	}
}
