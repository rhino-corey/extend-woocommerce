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
				<button type="button" onclick="<?php do_action('extend_product_sync') ?>"> Product Sync </button>
                <p class="description"><?php esc_html_e( 'This should only be used for the initial product sync. New products should automatically be synced.', 'woocommerce' ); ?></p>
			</fieldset>
		</td>
	</tr>
</table>
