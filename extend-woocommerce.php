<?php
/**
 * Plugin Name: Extend WooCommerce
 * Plugin URI:  https://www.extend.com
 * Description: WooCommerce plugin to connect Extend
 * Version:     0.0.0
 * Author:      Dustin Graham
 * Author URI:  https://www.extend.com
 * Donate link: https://www.extend.com
 * License:     GPLv2
 * Text Domain: extend-woocommerce
 * Domain Path: /languages
 *
 * @link    https://www.extend.com
 *
 * @package Extend_WooCommerce
 * @version 0.0.0
 *
 * Built using generator-plugin-wp (https://github.com/WebDevStudios/generator-plugin-wp)
 */

/**
 * Copyright (c) 2021 Dustin Graham (email : dustin@extend.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */


/**
 * Autoloads files with classes when needed.
 *
 * @since  0.0.0
 * @param  string $class_name Name of the class being requested.
 */
function extend_woocommerce_autoload_classes( $class_name ) {

	// If our class doesn't have our prefix, don't load it.
	if ( 0 !== strpos( $class_name, 'EWC_' ) ) {
		return;
	}

	// Set up our filename.
	$filename = strtolower( str_replace( '_', '-', substr( $class_name, strlen( 'EWC_' ) ) ) );

	// Include our file.
	Extend_WooCommerce::include_file( 'includes/class-' . $filename );
}
spl_autoload_register( 'extend_woocommerce_autoload_classes' );

/**
 * Main initiation class.
 *
 * @since  0.0.0
 */
final class Extend_WooCommerce {

	/**
	 * Current version.
	 *
	 * @var    string
	 * @since  0.0.0
	 */
	const VERSION = '0.0.0';

	/**
	 * URL of plugin directory.
	 *
	 * @var    string
	 * @since  0.0.0
	 */
	protected $url = '';

	/**
	 * Path of plugin directory.
	 *
	 * @var    string
	 * @since  0.0.0
	 */
	protected $path = '';

	/**
	 * Plugin basename.
	 *
	 * @var    string
	 * @since  0.0.0
	 */
	protected $basename = '';

	/**
	 * Detailed activation error messages.
	 *
	 * @var    array
	 * @since  0.0.0
	 */
	protected $activation_errors = array();

	/**
	 * Singleton instance of plugin.
	 *
	 * @var    Extend_WooCommerce
	 * @since  0.0.0
	 */
	protected static $single_instance = null;

	/**
	 * Instance of EWC_Contracts
	 *
	 * @since0.0.0
	 * @var EWC_Contracts
	 */
	protected $contracts;

	/**
	 * Instance of EWC_Cart
	 *
	 * @since0.0.0
	 * @var EWC_Cart
	 */
	protected $cart;

	/**
	 * Instance of EWC_Product
	 *
	 * @since0.0.0
	 * @var EWC_Product
	 */
	protected $product;

	/**
	 * Instance of EWC_Admin
	 *
	 * @since0.0.0
	 * @var EWC_Admin
	 */
	protected $admin;

	protected $api_host = '';
	protected $env = '';
	protected $api_key ='';
	protected $store_id;

	/**
	 * Instance of EWC_Product_Integration
	 *
	 * @since0.0.0
	 * @var EWC_Product_Integration
	 */
	protected $product_integration;

	/**
	 * Instance of EWC_Cart_Integration
	 *
	 * @since0.0.0
	 * @var EWC_Cart_Integration
	 */
	protected $cart_integration;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since   0.0.0
	 * @return  Extend_WooCommerce A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Sets up our plugin.
	 *
	 * @since  0.0.0
	 */
	protected function __construct() {
		$this->basename = plugin_basename( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->path     = plugin_dir_path( __FILE__ );

		$env = get_option('wc_extend_environment');

		add_option('extend_products_synced', 'no');
		

		if($env === 'dev'){ 
			$this->api_host = 'https://api-dev.helloextend.com';
			$this->sdk_url = 'https://sdk.helloextend.com/extend-sdk-client/v1/dev/extend-sdk-client.min.js';
		}elseif($env === 'demo'){
			$this->api_host = 'https://api-demo.helloextend.com';
			$this->sdk_url = 'https://sdk.helloextend.com/extend-sdk-client/v1/extend-sdk-client.min.js';
		}else {
			$this->api_host = 'https://api.helloextend.com';
			$this->sdk_url = 'https://sdk.helloextend.com/extend-sdk-client/v1/extend-sdk-client.min.js';
		}
		$this->env = $env;
		$store_id = get_option('wc_extend_store_id');
		if($store_id){
			$this->api_host .= '/stores/' . $store_id ;
		}
		$this->api_key = get_option('wc_extend_api_key');

		$this->store_id = $store_id;
	}

	/**
	 * Attach other plugin classes to the base plugin class.
	 *
	 * @since  0.0.0
	 */
	public function plugin_classes() {

		$this->contracts = new EWC_Contracts( $this );
		$this->cart = new EWC_Cart( $this );
		$this->product = new EWC_Product( $this );
		$this->admin = new EWC_Admin( $this );
		$this->product_integration = new EWC_Product_Integration( $this );
		$this->cart_integration = new EWC_Cart_Integration( $this );
	} // END OF PLUGIN CLASSES FUNCTION

	/**
	 * Add hooks and filters.
	 * Priority needs to be
	 * < 10 for CPT_Core,
	 * < 5 for Taxonomy_Core,
	 * and 0 for Widgets because widgets_init runs at init priority 1.
	 *
	 * @since  0.0.0
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'init' ), 0 );
		add_action('wp_enqueue_scripts', [$this, 'scripts']);
	}

/**
	 * @param $url
	 * @param string $method
	 * @param array $url_args
	 * @param array $body_fields
	 * @param array $headers
	 *
	 * @return array
	 */
	public function remote_request($path, $method = 'GET', $body_fields = array(),  $url_args = array() ) {

	    $url = $this->api_host . $path;
		$headers = array(
			'Accept'=> 'application/json; version=2021-04-01',
			'Content-Type' => 'application/json; charset=utf-8',

		);
		

		$headers['X-Extend-Access-Token']=$this->api_key;

		// Add url args (get parameters) to the main url
		if ( $url_args ) $url = add_query_arg( $url_args, $url );

		// Prepare arguments for wp_remote_request
		$args = array();

		if ( $method ) $args['method'] = $method;
		if ( $headers ) $args['headers'] = $headers;
		if ( $body_fields ) $args['body'] = json_encode( $body_fields );
		$args['timeout'] = 45;

		// Make the request
		$response = wp_remote_request($url, $args);

		// Get the results
		$response_code = wp_remote_retrieve_response_code( $response );
		$response_message = wp_remote_retrieve_response_message( $response );
		$response_body = wp_remote_retrieve_body( $response );

		// Decode the JSON in the body, if it is json
		if ( $response_body ) {
			$j = json_decode( $response_body );

			if ( $j ) $response_body = $j;
		}

		// Return this information in the same format for success or error. Includes debugging information.
		return array(
			'response_body' => $response_body,
			'response_code' => $response_code,
			'response_message' => $response_message,
			'response' => $response,
			'debug' => array(
				'file' => __FILE__,
				'line' => __LINE__,
				'function' => __FUNCTION__,
				'args' => array(
					'url' => $url,
					'method' => $method,
					'url_args' => $url_args,
					'body_fields' => $body_fields,
					'headers' => $headers,
				),
			)
		);

	}

	
	public function scripts(){
		wp_register_script('extend_script', $this->sdk_url);
		wp_register_script('extend_product_integration_script', $this->url . 'assets/productIntegration.js', ['jquery', 'extend_script'], filemtime($this->path .'assets/productIntegration.js' ));
		wp_register_script('extend_cart_integration_script', $this->url . 'assets/cartIntegration.js', ['jquery', 'extend_script'], filemtime($this->path .'assets/cartIntegration.js' ), true);
	}


	/**
	 * Activate the plugin.
	 *
	 * @since  0.0.0
	 */
	public function _activate() {
		// Bail early if requirements aren't met.
		if ( ! $this->check_requirements() ) {
			return;
		}

		// Make sure any rewrite functionality has been loaded.
		flush_rewrite_rules();
	}

	/**
	 * Deactivate the plugin.
	 * Uninstall routines should be in uninstall.php.
	 *
	 * @since  0.0.0
	 */
	public function _deactivate() {
		// Add deactivation cleanup functionality here.
	}

	/**
	 * Init hooks
	 *
	 * @since  0.0.0
	 */
	public function init() {

		// Bail early if requirements aren't met.
		if ( ! $this->check_requirements() ) {
			return;
		}



		// Load translated strings for plugin.
		load_plugin_textdomain( 'extend-woocommerce', false, dirname( $this->basename ) . '/languages/' );

		// Initialize plugin classes.
		$this->plugin_classes();

		add_action('admin_init', array( $this, 'after_woo_init' )); 

	}

	public function after_woo_init() {
		if (isset($_GET['action'])) {
			switch ($_GET['action']) {
				case 'productsync':
					do_action('extend_product_sync');
					break;
			}
		}
	}

	public function write_log($log) {
		if (true === WP_DEBUG) {
			if (is_array($log) || is_object($log)) {
				error_log(print_r($log, true));
			} else {
				error_log($log);
			}
		}
	}
	/**
	 * Check if the plugin meets requirements and
	 * disable it if they are not present.
	 *
	 * @since  0.0.0
	 *
	 * @return boolean True if requirements met, false if not.
	 */
	public function check_requirements() {

		// Bail early if plugin meets requirements.
		if ( $this->meets_requirements() ) {
			return true;
		}

		// Add a dashboard notice.
		add_action( 'all_admin_notices', array( $this, 'requirements_not_met_notice' ) );

		// Deactivate our plugin.
		add_action( 'admin_init', array( $this, 'deactivate_me' ) );

		// Didn't meet the requirements.
		return false;
	}

	/**
	 * Deactivates this plugin, hook this function on admin_init.
	 *
	 * @since  0.0.0
	 */
	public function deactivate_me() {

		// We do a check for deactivate_plugins before calling it, to protect
		// any developers from accidentally calling it too early and breaking things.
		if ( function_exists( 'deactivate_plugins' ) ) {
			deactivate_plugins( $this->basename );
		}
	}

	/**
	 * Check that all plugin requirements are met.
	 *
	 * @since  0.0.0
	 *
	 * @return boolean True if requirements are met.
	 */
	public function meets_requirements() {

		// Do checks for required classes / functions or similar.
		// Add detailed messages to $this->activation_errors array.
		return true;
	}

	

	/**
	 * Adds a notice to the dashboard if the plugin requirements are not met.
	 *
	 * @since  0.0.0
	 */
	public function requirements_not_met_notice() {

		// Compile default message.
		$default_message = sprintf( __( 'Extend WooCommerce is missing requirements and has been <a href="%s">deactivated</a>. Please make sure all requirements are available.', 'extend-woocommerce' ), admin_url( 'plugins.php' ) );

		// Default details to null.
		$details = null;

		// Add details if any exist.
		if ( $this->activation_errors && is_array( $this->activation_errors ) ) {
			$details = '<small>' . implode( '</small><br /><small>', $this->activation_errors ) . '</small>';
		}

		// Output errors.
		?>
		<div id="message" class="error">
			<p><?php echo wp_kses_post( $default_message ); ?></p>
			<?php echo wp_kses_post( $details ); ?>
		</div>
		<?php
	}

	/**
	 * Magic getter for our object.
	 *
	 * @since  0.0.0
	 *
	 * @param  string $field Field to get.
	 * @throws Exception     Throws an exception if the field is invalid.
	 * @return mixed         Value of the field.
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'version':
				return self::VERSION;
			case 'basename':
			case 'url':
			case 'path':
			case 'contracts':
			case 'cart':
			case 'product':
			case 'admin':
			case 'store_id':
			case 'env':
			case 'product_integration':
			case 'cart_integration':
				return $this->$field;
			default:
				throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
		}
	}

	/**
	 * Include a file from the includes directory.
	 *
	 * @since  0.0.0
	 *
	 * @param  string $filename Name of the file to be included.
	 * @return boolean          Result of include call.
	 */
	public static function include_file( $filename ) {
		$file = self::dir( $filename . '.php' );
		if ( file_exists( $file ) ) {
			return include_once( $file );
		}
		return false;
	}

	/**
	 * This plugin's directory.
	 *
	 * @since  0.0.0
	 *
	 * @param  string $path (optional) appended path.
	 * @return string       Directory and path.
	 */
	public static function dir( $path = '' ) {
		static $dir;
		$dir = $dir ? $dir : trailingslashit( dirname( __FILE__ ) );
		return $dir . $path;
	}

	/**
	 * This plugin's url.
	 *
	 * @since  0.0.0
	 *
	 * @param  string $path (optional) appended path.
	 * @return string       URL and path.
	 */
	public static function url( $path = '' ) {
		static $url;
		$url = $url ? $url : trailingslashit( plugin_dir_url( __FILE__ ) );
		return $url . $path;
	}
}

/**
 * Grab the Extend_WooCommerce object and return it.
 * Wrapper for Extend_WooCommerce::get_instance().
 *
 * @since  0.0.0
 * @return Extend_WooCommerce  Singleton instance of plugin class.
 */
function extend_woocommerce() {
	return Extend_WooCommerce::get_instance();
}

// Kick it off.
add_action( 'plugins_loaded', array( extend_woocommerce(), 'hooks' ) );

// Activation and deactivation.
register_activation_hook( __FILE__, array( extend_woocommerce(), '_activate' ) );
register_deactivation_hook( __FILE__, array( extend_woocommerce(), '_deactivate' ) );
