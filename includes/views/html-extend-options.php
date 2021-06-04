<?php
/**
 * Admin View: Page - Admin options.
 *
 * @package Extend For WooCommerce
 */

defined( 'ABSPATH' ) || exit;

?>

<table class="form-table">
	<tr valign="top">
        <h2>Integration Actions</h2>
		<th scope="row" class="titledesc">
			<label><?php esc_html_e( 'Product Sync', 'woocommerce' ); ?></label>
		</th>
		<td class="forminp">
			<fieldset>
				<legend class="screen-reader-text"><span><?php esc_html_e( 'Product Sync', 'woocommerce' ); ?></span></legend>
				<button class="extendProductSync" type="button" onclick="jQuery.ajax({
                url: 'admin.php?page=wc-settings&tab=products&section=warranties',
                type: 'GET', // get method
                data: 'action=productsync',
                  success: function(data) {
                      var productSyncBtn = document.querySelector('.extendProductSync');
                      var success = document.createElement('h2');
                      success.innerText = 'Product Sync Success';
                      productSyncBtn.parentNode.replaceChild(success, productSyncBtn);
                  },
                  error: function(data) {
                    // error
                  }
            })"> Product Sync </button>
                <p class="description"><?php esc_html_e( 'This should only be used for the initial product sync. New products should automatically be synced.', 'woocommerce' ); ?></p>
			</fieldset>
		</td>
	</tr>
</table>
