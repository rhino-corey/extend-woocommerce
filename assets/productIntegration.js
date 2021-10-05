let store_id = window.WCExtend.store_id;
let product_type = window.WCExtend.type;
let product_id = window.WCExtend.id;
let product_ids = window.WCExtend.ids;
let environment = window.WCExtend.environment;
let extend_modal_offers_enabled = window.WCExtend.extend_modal_offers_enabled
let extend_pdp_offers_enabled = window.WCExtend.extend_pdp_offers_enabled



jQuery(document).ready(function(){

    $('<style>').text("#extend-offers-modal-iframe {z-index: 999999999!important}#extend-learn-more-modal-iframe {z-index: 999999999!important}").appendTo(document.head)

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
                    comp.setActiveProduct(variation_id)
            }
            }, 500);



            jQuery( ".single_variation_wrap" ).on( "show_variation", function ( event, variation )  {
                let component = Extend.buttons.instance('#extend-offer');
                product_id = variation.variation_id;
                component.setActiveProduct(variation.variation_id)
            } );

    }

    jQuery('form.cart').append('<input type="hidden" name="planData"  id="planData"/>');


    jQuery('button.add_to_cart_button').on('click', function extendHandler(e) {
        e.preventDefault()

        // /** get the component instance rendered previously */
        const component = Extend.buttons.instance('#extend-offer');

        /** get the users plan selection */
        const plan = component.getPlanSelection();
        const product = component.getActiveProduct();

        if (plan) {

            jQuery('#planData').val(JSON.stringify(plan));
            jQuery('button.add_to_cart_button').off('click', extendHandler);
            jQuery('button.add_to_cart_button').trigger('click');

        } else{
            if(jQuery('#planData').val()==='' && extend_modal_offers_enabled === 'yes'){
                Extend.modal.open({
                    referenceId: product_id,
                    onClose: function(plan, product) {
                        if (plan && product) {
                            jQuery('#planData').val(JSON.stringify(plan));

                            jQuery('button.add_to_cart_button').off('click', extendHandler);
                            jQuery('button.add_to_cart_button').trigger('click');
                        } else {
                            jQuery('button.add_to_cart_button').off('click', extendHandler);
                            jQuery('button.add_to_cart_button').trigger('click');


                        }
                    },
                });
            } else {
                jQuery('button.add_to_cart_button').off('click', extendHandler);
                jQuery('button.add_to_cart_button').trigger('click');
            }

        }


    });

});