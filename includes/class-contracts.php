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
		add_action('woocommerce_order_status_completed', [$this, 'maybe_send_contract']);
		add_action('woocommerce_order_fully_refunded', [$this, 'process_full_refund']);
		add_action('woocommerce_order_status_refunded', [$this, 'process_full_refund']);
		add_action('woocommerce_create_refund', [$this, 'process_partial_refund'], 10, 2);
		add_action('add_meta_boxes', [$this, 'meta_boxes']);
	}

	public function extend_metabox(){
		global $post;

		$contracts = get_post_meta($post->ID, '_extend_contracts', true);

		if($contracts){
			$refunds = get_post_meta($post->ID, '_extend_refund_data', true);
			echo " <ul>";

			foreach($contracts as $cart_item_id=>$contract_id){
				echo "<li>Contract id: $contract_id";

				if(isset($refunds[$cart_item_id])){
					echo "<br>Status: Refunded";
				}else{
					echo "<br>Status: Active";
				}

				echo "</li>";
			}

			echo "</ul>";




		}else{
			echo '<p>No Extend Contracts found</p>';
		}

	}

	public function meta_boxes(){
		add_meta_box('extend_metabox',
			'Extend Info',
			[$this, 'extend_metabox'],
			'shop_order', 'side');
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


		/**
	 * @param $order_id integer
	 * @param $order WC_Order
	 */
	private function send_contracts( $order_id, $order = null) {

		if($order === null){
			$order = wc_get_order($order_id);
		}
		$items     = $order->get_items();
		$contracts = [];
		$prices    = [];
		$covered = [];
		$leads = [];

		foreach ( $items as $item ) {
			if ( intval($item->get_product_id()) === intval($this->warranty_product_id)) {
				$contracts[] = $item;
			} else {
				$prod_id = $item->get_variation_id()?$item->get_variation_id():$item->get_product_id();
				$prices[$prod_id] = $item->get_subtotal() / $item->get_quantity();
			}
		}

		if ( ! empty( $contracts ) ) {
			$contract_ids = [];
			foreach ( $contracts as $item ) {
				$item_id = $item->get_id();
				$data = $item->get_meta( '_extend_data' );
				if ( $data ) {

					$covered_id = $data['covered_product_id'];
					$covered[] = $covered_id;

					$contract_data = [
						'transactionId'    => $order_id,
						'poNumber'         => $order->get_order_number(),
						'transactionTotal' => [
							'currencyCode' => 'USD',
							'amount'       => $order->get_total()*100
						],
						'customer'         => [
							'name'            => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
							'email'           => $order->get_billing_email(),
							'phone'           => $order->get_billing_phone(),
							'billingAddress'  => [
								'address1'     => $order->get_billing_address_1(),
								'address2'     => $order->get_billing_address_2(),
								'city'         => $order->get_billing_city(),
								'countryCode'  => $order->get_billing_country(),
								'postalCode'   => $order->get_billing_postcode(),
								'provinceCode' => $order->get_billing_state()
								],
							'shippingAddress' => [
								'address1'     => $order->get_shipping_address_1(),
								'address2'     => $order->get_shipping_address_2(),
								'city'         => $order->get_shipping_city(),
								'countryCode'  => $order->get_shipping_country(),
								'postalCode'   => $order->get_shipping_postcode(),
								'provinceCode' => $order->get_shipping_state()
								],
							],
							'product'         => [
								'referenceId'   => $covered_id,
								'purchasePrice' => [
									'currencyCode' => 'USD',
									'amount'       => $prices[ $covered_id ] * 100
								]
							],
							'currency'        => 'USD',
							'source'          => [
								'agentId'      => '',
								'channel'      => 'web',
								'integratorId' => 'netsuite',
								'locationId'   => $this->plugin->store_id,
								'platform'     => 'woocommerce'
							],
							'transactionDate' => strtotime( $order->get_date_paid() ),
							'plan'            => [
								'purchasePrice' => [
									'currencyCode' => 'USD',
									'amount'       => $data['price']
								],
								'planId'        => $data['planId']
							]

					];



				$res =	$this->plugin->remote_request( '/contracts', 'POST', $contract_data );

				if(intval($res['response_code']) === 201){
					$item->add_meta_data("Extend Status", $res['response_body']->status);
					$contract_ids[$item_id]=	$res['response_body']->id;
				}


				}


			}

			if(!empty($contract_ids)){

				update_post_meta($order_id, '_extend_contracts', $contract_ids);

			}
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
	
	private function capture_refund_data($data){

		$body = $data['response_body'];

		$refunded = $body->refundedAt;
		$status = $body->status;
		$id = $body->id;

		return compact('refunded', 'status', 'id');

	}

}


