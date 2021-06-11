<?php
/**
 * Extend WooCommerce Contracts.
 *
 * @since   0.0.0
 * @package Extend_WooCommerce
 */

/**
 * Extend WooCommerce Contracts.
 *
 * @since 0.0.0
 */
class EWC_Contracts {
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
		add_action('woocommerce_order_status_completed', [$this, 'maybe_send_contract']);
		add_action('woocommerce_order_fully_refunded', [$this, 'process_full_refund']);
		add_action('woocommerce_order_status_refunded', [$this, 'process_full_refund']);
		add_action('woocommerce_create_refund', [$this, 'process_partial_refund'], 10, 2);
	}

		/**
	 * @param $refund WC_Order_Refund
	 * @param $args array
	 */
	public function process_partial_refund($refund, $args){




		$order_id = $refund->get_parent_id();

		$extend_data = get_post_meta($order_id, '_extend_contracts', true);

		if($extend_data){

			$refund_details = [];
			foreach($args['line_items'] as $item_id=> $item){
				if( $item['refund_total']>0 && isset($extend_data[$item_id])){

					$contract_id = $extend_data[$item_id];

					$res = $this->plugin->remote_request('/contracts/' . $contract_id . '/refund', 'POST', [], ['commit'=>true]);

				$refund_details[$item_id]=  $this->capture_refund_data($res);

				}
			}
			update_post_meta($order_id, '_extend_refund_data', $refund_details);
		}


	}

	public function maybe_send_contract($order_id){

		$sent = get_post_meta($order_id, '_extend_contracts', true);

		if(!$sent){

			$this->send_contracts($order_id);
		}

	}

	public function process_full_refund($order_id){



		$contracts = get_post_meta($order_id, '_extend_contracts', true);

		if($contracts){

			$refund_details = [];
			foreach($contracts as $item_id=>$contract_id){

				$res = $this->plugin->remote_request('/contracts/' . $contract_id . '/refund', 'POST', [], ['commit'=>true]);
				$refund_details[$item_id]= $this->capture_refund_data($res);


			}


			update_post_meta($order_id, '_extend_refund_data', $refund_details);
		}


	}
}
