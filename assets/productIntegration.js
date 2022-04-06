jQuery(document).ready(function(){
    if(!ExtendWooCommerce || !ExtendProductIntegration) return;

    const { type: product_type, id: product_id, extend_modal_offers_enabled, extend_pdp_offers_enabled } = ExtendProductIntegration;

    let atcButton = 'button.addToCart';

    if(extend_pdp_offers_enabled === 'no'){
        var extendOffer = document.querySelector('#extend-offer')
        extendOffer.style.display = 'none';
    }

    if(product_type ==='simple'){
        Extend.buttons.render('#extend-offer', {
            referenceId: product_id,
        })
    }else{

        Extend.buttons.render('#extend-offer', {
            referenceId: product_id,
        });

        setTimeout(function(){
            let variation_id = jQuery('[name="variation_id"]').val();
            if(variation_id ) {
                let comp = Extend.buttons.instance('#extend-offer');
                comp.setActiveProduct(variation_id)
            }
        }, 500);

        jQuery( ".single_variation_wrap" ).on( "show_variation", function ( event, variation )  {
            let component = Extend.buttons.instance('#extend-offer');
            variation_id = variation.variation_id;

            if(variation_id) {
                component.setActiveProduct(variation.variation_id)
            }
        });

    }

    jQuery(atcButton).on('click', function extendHandler(e) {
        e.preventDefault()

        function triggerAddToCart() {
            jQuery(atcButton).off('click', extendHandler);
            jQuery(atcButton).trigger('click');
            jQuery(atcButton).on('click', extendHandler);
        }

        // /** get the component instance rendered previously */
        const component = Extend.buttons.instance('#extend-offer');

        /** get the users plan selection */
        const plan = component.getPlanSelection();
        const product = component.getActiveProduct();

        if (plan) {
            var planCopy = { ...plan, covered_product_id: product.id }
            var data = {
                quantity: 1,
                plan: planCopy
            }
            ExtendWooCommerce.addPlanToCart(data)
              .then(() => {
                  triggerAddToCart()
              })
        } else{
            if(extend_modal_offers_enabled === 'yes'){
                Extend.modal.open({
                    referenceId: product.id,
                    onClose: function(plan, product) {
                        if (plan && product) {
                            var planCopy = { ...plan, covered_product_id: product.id }
                            var data = {
                                quantity: 1,
                                plan: planCopy
                            }
                            ExtendWooCommerce.addPlanToCart(data)
                              .then(() => {
                                  triggerAddToCart()
                              })
                        } else {
                            triggerAddToCart()
                        }
                    },
                });
            } else {
                triggerAddToCart()
            }
        }
    });
});
