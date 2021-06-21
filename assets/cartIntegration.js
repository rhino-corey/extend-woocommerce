let store_id = window.WCCartExtend.store_id;
let product_id = window.WCCartExtend.id;
let product_ids = window.WCCartExtend.ids;
let environment = window.WCCartExtend.environment;
let warranty_prod_id = window.WCCartExtend.warranty_prod_id;


 function warrantyAlreadyInCheckout (variantId, cart) {
    var checkoutItems = Object.values(cart['cart_contents']);
    const extendWarranties = checkoutItems.filter(function (lineItem) {
      //filter through the customAttributes and grab the referenceId
      var extendData = lineItem.extendData;
      if (extendData && extendData['covered_product_id'])
        var referenceId = extendData['covered_product_id'];
      return (
        extendData &&
        !extendData.leadToken &&
        referenceId &&
        referenceId.toString() === variantId.toString()
      );
    });
    return extendWarranties.length > 0;
  }

jQuery( document.body ).on( 'updated_cart_totals', function(){
    jQuery('.cart-extend-offer').each(function(ix, val){
        let ref_id =  jQuery(val).data('covered');
        let qty = jQuery(val).parents('.cart_item').find('input.qty').val();


        if(Extend.buttons.instance('#'+val.id)){
            Extend.buttons.instance('#'+val.id).destroy();
        }

        jQuery.post(WCCartExtend.ajaxurl, {action: "get_cart"})
            .then(function(cart){
                window.WCCartExtend.cart = JSON.parse(cart);

                if(warrantyAlreadyInCheckout(ref_id, window.WCCartExtend.cart)){
                    return;
                }

                /** initialize offer */
                Extend.buttons.renderSimpleOffer('#'+val.id, {
                    referenceId: ref_id,
                    onAddToCart:
                        function({ plan, product }) {
                            if (plan && product) {

                                plan['covered_product_id'] = ref_id

                                var data = {
                                    product_id: warranty_prod_id,
                                    quantity: qty,
                                    extendData: plan
                                };

                                console.log(plan, product);
                                jQuery.post( wc_add_to_cart_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'add_to_cart' ), data, function( response ) {
                                    if (!response) {
                                        return;
                                    }
                                    jQuery("[name='update_cart']").removeAttr('disabled');
                                    jQuery("[name='update_cart']").trigger("click");

                                });

                            }
                        },
                });
            })
    })
});

jQuery(document).ready(function() {
    Extend.config({
        storeId: store_id,
        environment: environment,
        referenceIds: product_ids
    });


    jQuery('.cart-extend-offer').each(function(ix, val){
        let ref_id =  jQuery(val).data('covered');
        let qty = jQuery(val).parents('.cart_item').find('input.qty').val();

        if(warrantyAlreadyInCheckout(ref_id, window.WCCartExtend.cart)){
            return;
        }

    /** initialize offer */
        Extend.buttons.renderSimpleOffer('#'+this.id, {
            referenceId: ref_id,
            onAddToCart:
                function({ plan, product }) {
                    if (plan && product) {

                        plan['covered_product_id'] = ref_id

                        var data = {
                            product_id: warranty_prod_id,
                            quantity: qty,
                            extendData: plan
                        };
                        jQuery.post( wc_add_to_cart_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'add_to_cart' ), data, function( response ) {
                            if (!response) {
                                return;
                            }
                            jQuery("[name='update_cart']").removeAttr('disabled');
                            jQuery("[name='update_cart']").trigger("click");

                        });

                    }
                },
        });
    })

});