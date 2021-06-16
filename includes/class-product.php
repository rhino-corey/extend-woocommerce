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
		$isProductSyncEnabled = get_option('wc_extend_product_sync_enabled');
		if($isProductSyncEnabled == 'yes'){
			add_action('added_post_meta', [$this, 'addNewProduct'], 99, 3);
			add_action('updated_post_meta', [$this, 'addNewProduct'], 99, 3);

			add_action('save_post_product_variation', [$this, 'updateProduct'], 99, 3);
			add_action('woocommerce_save_product_variation', [$this, 'updateProduct']);
			add_action('woocommerce_ajax_save_product_variations', [$this, 'save_variations']);
		}
	}

	public function save_variations($product_id){

		$product = wc_get_product($product_id);
		$variations = $product->get_available_variations();

		foreach($variations as $variation){
			$variation_id = $variation['variation_id'];

			$this->updateProduct($variation_id);
		}

	}

	public function addNewProduct($metaId, $postId, $metaKey){
		if ( $metaKey == '_edit_lock' ) {
			if ( get_post_type( $postId ) == 'product' ) { // we've been editing a product 
				$this->updateProduct($postId);
			}
		}
	}

	public function updateProduct($id){
			
			$data = $this->getProductData($id);
			
			$exists = get_post_meta($id, '_extend_added', true);

			if($exists){
				$res = $this->plugin->remote_request('/products/'. $id, 'PUT',  $data);

				if($res['response_code']=== 404){
					$res = $this->plugin->remote_request('/products', 'POST', $data, ['upsert'=>true]);
				}
			}else{
				$res = $this->plugin->remote_request('/products', 'POST', $data, ['upsert'=>false]);

				if($res['response_code']=== 409 ){

					update_post_meta($id, '_extend_added', true);

					$res = $this->plugin->remote_request('/products/'. $id, 'PUT',  $data);
				}


			}


	}


	/**
	 * @param $product mixed
	 *
	 * @return array
	 */

	private function getProductData($product = null){


		if(is_numeric($product)){
			$id = $product;
			$product = wc_get_product($id);
		}else{
			$id = $product->get_id();
		}

		$image = get_the_post_thumbnail_url($id);
		$title = $product->get_title();
		if($product->get_parent_id()>0){
			$parent = wc_get_product($product->get_parent_id());
			$brand = $parent->get_attribute('pa_product-brand');
			$description = $parent->get_short_description();
			$description = $this->getPlain($description);
			if(empty($description)){
				$description = $parent->get_description();
			}
			if(empty($image)){
				$image = get_the_post_thumbnail_url($product->get_parent_id());
			}
			$category = $this->getCategory($product->get_parent_id());
			$title = $this->get_variation_title($product);
		}else{
			$brand = $product->get_attribute('pa_product-brand');
			$description = $product->get_short_description();
			$description = $this->getPlain($description);
			if(empty($description)){
				$description = $product->get_description();
				$description = $this->getPlain($description);
			}
			$category = $this->getCategory($id);
		}

		$data = [
		'referenceId'=>$id,
		'brand'=>$brand,
		'category'=>$category,
		'description'=>substr($description, 0, 2000),
		'enabled'=>$this->isEnabled($product),
		'price'=>['currencyCode'=>'USD', 'amount'=> ((int)$product->get_price() * (int)100)],
		'title'=>$title,
			'imageUrl'=>$image,
			'identifiers'=>[
				'sku'=>$product->get_sku()

			]

		];

		$warranty =$this->getWarranty($id);
		if(!empty($warranty)){
			$data['mfrWarranty']=$warranty;
		}


		$upc = get_post_meta($id, '_cpf_upc', true);
		if($upc && strpos($upc, '000000')===false ){
			$data['identifiers']['upc'] = $upc;
		}


			$data['parentReferenceId'] = $product->get_parent_id();




		return $data;
		
	}

	private function getPlain($html){
		$text = preg_replace( "/\n\s+/", "\n", rtrim(html_entity_decode(strip_tags($html))) );

		return $text;

	}


		/**
	 * @param $id
	 *
	 * @return string
	 */

	private function getCategory($id){
		
		$primary_cat = get_post_meta($id, '_yoast_wpseo_primary_product_cat', true);
		
		if($primary_cat && is_numeric($primary_cat)){
			$term = get_term($primary_cat, 'product_cat');
			if(is_object($term)){
				return $term->name;
			}

		}

			$cats = wc_get_product_category_list($id);

			$cats = explode(',', $cats);

			$cats = array_map(function($cat){
				return strip_tags($cat);
			}, $cats);
			return implode(',', $cats);


		
	}

	public function get_variation_title($variation){
		$attributes = $variation->get_variation_attributes();
		$atts = [];
		foreach($attributes as $key=>$val){
			$key = ucwords( str_replace('-', ' ',str_replace('attribute_', '', $key)));
			$atts[] = $key .': ' . $val;
		}

		return $variation->get_title() . '(' . implode(', ', $atts) . ')';
	}



		/**
	 * @param $product WC_Product
	 *
	 * @return bool
	 */
	private function isEnabled($product){
		$enabled = true;

		if($product->get_status() !=='publish'){
			return false;
		}


		$stock = $product->get_stock_status();
		if($stock !== 'instock'){
			return false;
		}


		$catonly = get_post_meta($product->get_id(), '_catalog_only', true);
		if($catonly && $catonly!== null){
			$enabled = false;
		}
		return $enabled;
	}

		/**
	 * @param $id
	 *
	 * @return array
	 */
	private function getWarranty($id){

		return [];
	}

}
