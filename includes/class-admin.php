<?php
/**
 * Extend WooCommerce Admin.
 *
 * @since   0.0.0
 * @package Extend_WooCommerce
 */

/**
 * Extend WooCommerce Admin.
 *
 * @since 0.0.0
 */
class EWC_Admin {
	/**
	 * Parent plugin class.
	 *
	 * @since 0.0.0
	 *
	 * @var   Extend_WooCommerce
	 */
	protected $plugin = null;
	protected $settings_tab_id = 'warranties';

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
		add_action( 'woocommerce_get_sections_products', array( $this, 'add_extend_settings_tab' ), 50 );
		add_action( 'woocommerce_get_settings_products', array( $this, 'add_extend_settings' ), 50, 2 );
	}

	public function add_extend_settings_tab($settings_tabs){
		$settings_tabs[ $this->settings_tab_id ] = __( 'Extend Settings', 'extend-woocommerce' );
		return $settings_tabs;
	}

	public function add_extend_settings( $settings, $current_section ){


		if($current_section !== $this->settings_tab_id){
			return $settings;
		}

		$cat_terms = get_terms([
			'parent'=>0,
			'taxonomy'=>'product_cat'
		]);



		$top_level_cats = [];

		foreach($cat_terms as $term){
			$top_level_cats[$term->term_id] = $term->name;
		}
		
		$new_settings = array(
			array(
				'name' => __( 'Integration Configuration', 'extend-woocommerce' ),
				'desc'=>'',
				'type' => 'title',
				'id'   => 'wc_extend_title',
			),
			array(
				'title'	  => __( 'Enable Extend', 'extend-woocommerce' ),
				'type'    => 'checkbox',
				'id'	  => 'wc_extend_enabled',
			),
			array(
				'title'	  => __( 'Enable Cart Offers', 'extend-woocommerce' ),
				'type'    => 'checkbox',
				'id'	  => 'wc_extend_cart_offers_enabled',
			),
			array(
				'title'	  => __( 'Enable PDP Offers', 'extend-woocommerce' ),
				'type'    => 'checkbox',
				'id'	  => 'wc_extend_pdp_offers_enabled',
			),
			array(
				'title'	  => __( 'Enable Modal Offers', 'extend-woocommerce' ),
				'type'    => 'checkbox',
				'id'	  => 'wc_extend_modal_offers_enabled',
			),
			array(
				'title'	  => __( 'Automated Product Sync', 'extend-woocommerce' ),
				'type'    => 'checkbox',
				'id'	  => 'wc_extend_product_sync_enabled',
			),
			array(
				'title'	  => __( 'Automated Contract Creation/Refunding', 'extend-woocommerce' ),
				'type'    => 'checkbox',
				'id'	  => 'wc_extend_contracts_enabled',
			),
			array(
				'title'   => __( 'Environment', 'wcpf' ),
				'type'    => 'select',
				'id'      => 'wc_extend_environment',
				'options' => array(
								'live' => __("LIVE"),
								'demo' => __("DEMO"),
				),
				'default' => 'live',
			),
			array(
				'name' => __( 'Extend Store Id', 'extend-woocommerce' ),
				'type'        => 'text',
				'desc'        => __( '', 'extend-woocommerce' ),
				'default'     => '',
				'placeholder' => __( '', 'extend-woocommerce' ),
				'id'          => 'wc_extend_store_id',
				'desc_tip'    => true,
			),
			array(
				'name' => __( 'Extend API Key', 'extend-woocommerce' ),
				'type'        => 'text',
				'desc'        => __( '', 'extend-woocommerce' ),
				'default'     => '',
				'placeholder' => __( '', 'extend-woocommerce' ),
				'id'          => 'wc_extend_api_key',
				'desc_tip'    => true,
			),
			array(
				'name' => __( 'Extend Product Id', 'extend-woocommerce' ),
				'type'        => 'text',
				'desc'        => __( 'This is the product id for the Extend Warranty product', 'extend-woocommerce' ),
				'default'     => '',
				'placeholder' => __( '', 'extend-woocommerce' ),
				'id'          => 'wc_extend_product_id',
				'desc_tip'    => true,
			),
			array( 'type' => 'sectionend', 'id' => 'wc_extend_defaults' ),
		);		
		return $new_settings;
	}
}
