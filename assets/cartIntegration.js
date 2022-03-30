jQuery( document.body ).on( 'updated_cart_totals', function(){
	if(!ExtendWooCommerce || !ExtendCartIntegration) {
		return;
	}

    jQuery('.cart-extend-offer').each(function(ix, val){
        let ref_id =  jQuery(val).data('covered');
        let qty = jQuery(val).parents('.cart_item').find('input.qty').val();


        if(Extend.buttons.instance('#'+val.id)){
            Extend.buttons.instance('#'+val.id).destroy();
		}
		
		ExtendWooCommerce.getCart()
			.then(cart => {

				if(ExtendWooCommerce.warrantyAlreadyInCart(ref_id, cart) || ExtendCartIntegration.extend_cart_offers_enabled === 'no'){
                    return;
                }

                /** initialize offer */
                Extend.buttons.renderSimpleOffer('#'+val.id, {
                    referenceId: ref_id,
                    onAddToCart:
                        function({ plan, product }) {
                            if (plan && product) {

								var planCopy = { ...plan, covered_product_id: ref_id }

								var data = {
									quantity: qty,
									plan: planCopy
								};

								ExtendWooCommerce.addPlanToCart(data)
									.then(() => {
										jQuery("[name='update_cart']").removeAttr('disabled');
										jQuery("[name='update_cart']").trigger("click");
									})
                            }
                        },
                });
			})
    })
});

jQuery(document).ready(function() {
	if(!ExtendWooCommerce || !ExtendCartIntegration) {
		return;
	}

    jQuery('.cart_item').each(function(ix, val){
        var title = jQuery(val).find('.product-name');
        var image = jQuery(val).find('.product-thumbnail')
        if(title.text().indexOf('Extend Protection Plan') > -1){
            image.css('pointer-events', 'none')
        }
    })


    jQuery('.cart-extend-offer').each(function(ix, val){
        let ref_id =  jQuery(val).data('covered');
		let qty = jQuery(val).parents('.cart_item').find('input.qty').val();
		
		// check if warranty already exists with initial cart (sent from php)
        if(warrantyAlreadyInCart(ref_id, window.ExtendCartIntegration.cart) || ExtendCartIntegration.extend_cart_offers_enabled === 'no'){
            return;
        }

    	/** initialize offer */
        Extend.buttons.renderSimpleOffer('#'+this.id, {
            referenceId: ref_id,
            onAddToCart:
                function({ plan, product }) {
                    if (plan && product) {
						var planCopy = { ...plan, covered_product_id: ref_id }

                        var data = {
                            quantity: qty,
                            plan: planCopy
                        };

						ExtendWooCommerce.addPlanToCart(data)
							.then(() => {
								jQuery("[name='update_cart']").removeAttr('disabled');
								jQuery("[name='update_cart']").trigger("click");
							})
                    }
                },
        });
    })

});
