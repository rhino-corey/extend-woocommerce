let store_id = window.WCExtend.store_id;
let product_type = window.WCExtend.type;
let product_id = window.WCExtend.id;
let product_ids = window.WCExtend.ids;
let environment = window.WCExtend.environment;
let extend_modal_offers_enabled = window.WCExtend.extend_modal_offers_enabled
let extend_pdp_offers_enabled = window.WCExtend.extend_pdp_offers_enabled



jQuery(document).ready(function () {
    Extend.config({
        storeId: store_id,
        environment: environment,
        referenceIds: product_ids
    });

    //find all product forms
    //for each PF, find .extend-offer div, do logic below

    var productForms = document.querySelectorAll('.elementor-widget-wc-add-to-cart');

    productForms.forEach(function (productForm) {
        var extendOffer = productForm.querySelector('#extend-offer');

        if (extend_pdp_offers_enabled === 'no') {
            extendOffer.style.display = 'none';
        }

        if (product_type === 'simple') {
            Extend.buttons.render(extendOffer, {
                referenceId: product_id,
            })
        } else {
            Extend.buttons.render(extendOffer, {
                referenceId: product_id,
            });

            setTimeout(function () {
                let variation_id = jQuery(productForm).find('[name="variation_id"]').val();
                if (variation_id) {
                    let comp = Extend.buttons.instance(extendOffer);
                    comp.setActiveProduct(variation_id)
                }
            }, 500);



            jQuery(productForm).find(".single_variation_wrap").on("show_variation", function (event, variation) {
                let component = Extend.buttons.instance(extendOffer);
                variation_id = variation.variation_id;

                if (variation_id) {
                    component.setActiveProduct(variation.variation_id)
                }
            });

        }

        jQuery(productForm).find('form.cart').append('<input type="hidden" name="planData"  id="planData"/>');

        var addToCartButton = jQuery(productForm).find('button.single_add_to_cart_button');

        addToCartButton.on('click', function extendHandler(e) {
            e.preventDefault();
            e.stopPropagation();

            /** get the users plan selection */
            const plan = Extend.buttons.instance(extendOffer).getPlanSelection();
            const product = Extend.buttons.instance(extendOffer).getActiveProduct();

            if (plan && product) {

                jQuery(productForm).find('#planData').val(JSON.stringify(plan));
                addToCartButton.off('click', extendHandler);
                addToCartButton.trigger('click');
                addToCartButton.on('click', extendHandler);

            } else {
                if (jQuery(productForm).find('#planData').val() === '' && extend_modal_offers_enabled === 'yes') {

                    Extend.modal.open({
                        referenceId: product_id,
                        onClose: function (plan, product) {
                            if (plan && product) {
                                jQuery(productForm).find('#planData').val(JSON.stringify(plan));

                                addToCartButton.off('click', extendHandler);
                                addToCartButton.trigger('click');
                                addToCartButton.on('click', extendHandler);
                            } else {
                                addToCartButton.off('click', extendHandler);
                                addToCartButton.trigger('click');
                                addToCartButton.on('click', extendHandler);


                            }
                        },
                    });
                } else {
                    addToCartButton.off('click', extendHandler);
                    addToCartButton.trigger('click');
                    addToCartButton.on('click', extendHandler);
                }

            }


        });

    })

    // if(extend_pdp_offers_enabled === 'no'){
    //     var extendOffer = document.querySelector('#extend-offer')
    //     extendOffer.style.display = 'none';
    // }

    // if(product_type ==='simple'){
    //     Extend.buttons.render('#extend-offer', {
    //         referenceId: product_id,
    //     })
    // }else{
    //     Extend.buttons.render('#extend-offer', {
    //         referenceId: product_id,
    //     });

    //     setTimeout(function(){
    //         let variation_id = jQuery('[name="variation_id"]').val();
    //         if(variation_id ) {
    //             let comp = Extend.buttons.instance('#extend-offer');
    //                 comp.setActiveProduct(variation_id)
    //         }
    //         }, 500);



    //         jQuery( ".single_variation_wrap" ).on( "show_variation", function ( event, variation )  {
    //             let component = Extend.buttons.instance('#extend-offer');
    //             variation_id = variation.variation_id;

    //             if(variation_id) {
    //                 component.setActiveProduct(variation.variation_id)
    //             }
    //         } );

    // }

    // jQuery('form.cart').append('<input type="hidden" name="planData"  id="planData"/>');


    // jQuery('button.single_add_to_cart_button').on('click', function extendHandler(e) {
    //     e.preventDefault()

    //     // /** get the component instance rendered previously */
    //     const component = Extend.buttons.instance('#extend-offer');

    //     /** get the users plan selection */
    //     const plan = component.getPlanSelection();
    //     const product = component.getActiveProduct();

    //     if (plan) {

    //         jQuery('#planData').val(JSON.stringify(plan));
    //         jQuery('button.single_add_to_cart_button').off('click', extendHandler);
    //         jQuery('button.single_add_to_cart_button').trigger('click');
    //         jQuery('button.single_add_to_cart_button').on('click', extendHandler);

    //     } else{
    //         if(jQuery('#planData').val()==='' && extend_modal_offers_enabled === 'yes'){
    //             Extend.modal.open({
    //                 referenceId: product_id,
    //                 onClose: function(plan, product) {
    //                     if (plan && product) {
    //                         jQuery('#planData').val(JSON.stringify(plan));

    //                         jQuery('button.single_add_to_cart_button').off('click', extendHandler);
    //                         jQuery('button.single_add_to_cart_button').trigger('click');
    //                         jQuery('button.single_add_to_cart_button').on('click', extendHandler);
    //                     } else {
    //                         jQuery('button.single_add_to_cart_button').off('click', extendHandler);
    //                         jQuery('button.single_add_to_cart_button').trigger('click');
    //                         jQuery('button.single_add_to_cart_button').on('click', extendHandler);


    //                     }
    //                 },
    //             });
    //         } else {
    //             jQuery('button.single_add_to_cart_button').off('click', extendHandler);
    //             jQuery('button.single_add_to_cart_button').trigger('click');
    //             jQuery('button.single_add_to_cart_button').on('click', extendHandler);
    //         }

    //     }


    // });

});