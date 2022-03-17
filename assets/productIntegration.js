let store_id = window.WCExtend.store_id;
let product_type = window.WCExtend.type;
let product_id = window.WCExtend.id;
let product_ids = window.WCExtend.ids;
let environment = window.WCExtend.environment;
let extend_modal_offers_enabled = window.WCExtend.extend_modal_offers_enabled
let extend_pdp_offers_enabled = window.WCExtend.extend_pdp_offers_enabled



jQuery(document).ready(function(){
    Extend.config({
        storeId: store_id ,
        environment: environment ,
        referenceIds: product_ids
    });

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
                    comp.setActiveProduct(product_id)
            }
            }, 1000);



            jQuery( ".single_variation_wrap" ).on( "show_variation", function ( event, variation )  {
                let component = Extend.buttons.instance('#extend-offer');
                variation_id = variation.variation_id;
				
                if(variation_id) {
                    component.setActiveProduct(variation.variation_id)
                }
            } );

    }

    jQuery('form.cart').append('<input type="hidden" name="planData"  id="planData"/>');
	//jQuery('.sticky-add-to-cart #extend-offer').remove();
	var styleEl = document.createElement('style');
	styleEl.innerHTML = '.sticky-add-to-cart--active > #extend-offer { display: none;}';
	document.head.appendChild(styleEl);

    jQuery('button.single_add_to_cart_button').on('click', function extendHandler(e) {
        e.preventDefault()

        // /** get the component instance rendered previously */
        const component = Extend.buttons.instance('#extend-offer');

        /** get the users plan selection */
        const plan = component.getPlanSelection();
        const product = component.getActiveProduct();

        if (plan) {

            jQuery('#planData').val(JSON.stringify(plan));
            jQuery('button.single_add_to_cart_button').off('click', extendHandler);
            jQuery('button.single_add_to_cart_button').trigger('click');
            jQuery('button.single_add_to_cart_button').on('click', extendHandler);

        } else{
            if(jQuery('#planData').val()==='' && extend_modal_offers_enabled === 'yes'){
                Extend.modal.open({
                    referenceId: product_id,
                    onClose: function(plan, product) {
                        if (plan && product) {
                            jQuery('#planData').val(JSON.stringify(plan));

                            jQuery('button.single_add_to_cart_button').off('click', extendHandler);
                            jQuery('button.single_add_to_cart_button').trigger('click');
                            jQuery('button.single_add_to_cart_button').on('click', extendHandler);
                        } else {
                            jQuery('button.single_add_to_cart_button').off('click', extendHandler);
                            jQuery('button.single_add_to_cart_button').trigger('click');
                            jQuery('button.single_add_to_cart_button').on('click', extendHandler);


                        }
                    },
                });
            } else {
                jQuery('button.single_add_to_cart_button').off('click', extendHandler);
                jQuery('button.single_add_to_cart_button').trigger('click');
                jQuery('button.single_add_to_cart_button').on('click', extendHandler);
            }

        }


    });

});
