let store_id = window.ExtendWooCommerce.store_id;
let environment = window.ExtendWooCommerce.environment;
let ajaxurl = window.ExtendWooCommerce.ajaxurl;

jQuery(document).ready(function() {
    Extend.config({
		storeId: store_id,
		environment: environment
	});

	window.ExtendWooCommerce = {
		...window.ExtendWooCommerce,
		addPlanToCart,
		getCart,
		warrantyAlreadyInCart
	}
})

async function addPlanToCart (opts) {
	return await jQuery.post(ajaxurl, {
		action: "add_to_cart_extend",
		quantity: opts.quantity,
		extendData: opts.plan
	}).promise()
}

async function getCart() {
	return JSON.parse(
		await jQuery.post(ajaxurl, {
			action: "get_cart_extend"
		}).promise()
	);
}

function warrantyAlreadyInCart (variantId, cart) {
    var cartItems = Object.values(cart['cart_contents']);
    const extendWarranties = cartItems.filter(function (lineItem) {
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
