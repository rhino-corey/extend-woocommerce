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
        add_action('woocommerce_before_add_to_cart_form', [$this, 'product_offer']);
    }

    // product_offer()
    // grabs required variables, and enqueue's product scripts
    public function product_offer(){
        global $product;

        $id = $product->get_id();

        $type = $product->get_type();

        $extend_enabled = get_option('wc_extend_enabled');
        $extend_pdp_offers_enabled = get_option('wc_extend_pdp_offers_enabled');
        $extend_modal_offers_enabled = get_option('wc_extend_modal_offers_enabled');

        if($extend_enabled === 'yes') {
            wp_enqueue_script('extend_script');
            wp_enqueue_script('extend_product_integration_script');
            wp_localize_script('extend_product_integration_script', 'ExtendProductIntegration', compact('id', 'type', 'extend_modal_offers_enabled', 'extend_pdp_offers_enabled'));
            echo "<div style=\"padding: 20px 0 10px 0; margin: 20px 0; border-top: 1px solid #eee; border-bottom: 1px solid #eee;\" id=\"extend-offer\"></div>";
        }
    }
}
