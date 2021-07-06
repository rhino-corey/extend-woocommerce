# Extend WooCommerce #
**Contributors:**      Dustin Graham  
**Donate link:**       https://www.extend.com  
**Tags:**  
**Requires at least:** 4.4  
**Tested up to:**      4.8.1 
**Stable tag:**        0.0.0  
**License:**           GPLv2  
**License URI:**       http://www.gnu.org/licenses/gpl-2.0.html  

## Description ##

WooCommerce plugin to connect Extend and enable WooCommerce merchants to sell extended protection plans!

## Installation ##

### Manual Installation ###

1. Upload the entire `/extend-woocommerce` directory to the `/wp-content/plugins/` directory.
2. Activate Extend WooCommerce through the 'Plugins' menu in WordPress.
3. Create Extend Product in WooCommerce and copy the Product ID and set the image to be the Extend Logo
    * Note: The price and title does not play a factor here. We change the title and price depending on the plan that is selected.
4. Navigate to Extend settings via **WooCommerce** -> **Settings** -> **Products** -> **Extend Settings**
5. Enable Extend, and configure settings to the sites needing.
5. Set the environment, Extend store id, and API Key. Set the Product ID from the Extend Product to the Coverage Product Id.

## Settings ##

### Toggles ###

1. Enable Extend
    * This is the master toggle. This must be enabled to use the plugin in it's entirety. 

2. Enable Cart Offers
    * This toggles SDK offers on the cart page.

3. Enable PDP Offers
    * This toggles SDK offers on the pdp page.

4. Enable Modal Offers
    * This toggles modal offers on the pdp page when a product is added to cart without a warranty.

5. Automated Product Sync
    * This toggles our automated product sync. If this is disabled products will have to be synced a different way.

6. Automated Contract Creation/Refunding
    * This toggles our automated contract creation and refunding. If this is disabled contract creation and refunding will have to be handled a different way.

### Inputs ###

1. Environment
    * This should be *Demo* when in active development, and *Live* only when Extend is live.

2. Extend Store Id
    * This should be the respective storeId dependant on the Environment. (This can be found in your merchant dashboard)

3. Extend API Key
    * This should be the API Key from the respective environment. (This can be found in your merchant dashboard)

4. Extend Product Id
    * This should be the ID of the product that you create to represent warranties in WooCommerce.

## Changelog ##

### 0.0.0 ###
* First release

## Upgrade Notice ##

### 0.0.0 ###
First Release
