/**
 * @file
 * JS code to integrate with GTM.
 */

(function ($, Drupal, drupalSettings, dataLayer) {
  'use strict';

  Drupal.alshayaSeoSpc = Drupal.alshayaSeoSpc || {};

  /**
   * Helper function to get step number from body attr gtm-container.
   */
  Drupal.alshayaSeoSpc.getStepFromContainer = function () {
    var step = 1;
    var cart_data = Drupal.alshayaSpc.getCartData();
    if (window.location.href.indexOf('checkout') > -1) {
      step = 2;
    }
    if (cart_data !== null
      && cart_data.hasOwnProperty('cart_payment_method')
      && cart_data.cart_payment_method !== null
    ) {
      step = 3;
    }
    return step;
  };

  /**
   * Helper function to get product GTM attributes.
   *
   * @param product
   *   Product Object with gtm attributes.
   * @returns {{quantity: *, price, name: *, variant: *, id: *, category: *,
   *   brand: *}} Product details object with gtm attributes.
   */
  Drupal.alshayaSeoSpc.gtmProduct = function (product) {
    var productDetails = {
      quantity: product.qty,
    };
    for (var gtmKey in product.gtm_attributes) {
      productDetails[gtmKey] = product.gtm_attributes[gtmKey];
    }

    if ($.cookie('product-list') !== undefined) {
      var listValues = JSON.parse($.cookie('product-list'));
      if (listValues.hasOwnProperty(product.parent_sku)) {
        productDetails.list = listValues[product.parent_sku];
      }
    }
    return productDetails;
  };

  /**
   * GTM dataLayer checkout event.
   *
   * @param cart_data
   *   Cart data Object from localStorage.
   * @param step
   *   Checkout step for gtm checkout event.
   */
  Drupal.alshayaSeoSpc.cartGtm = function(cart_data, step) {
    // GTM data for SPC cart.
    if (cart_data !== undefined) {
      dataLayer[0].ecommerce.checkout.actionField.step = step;
      dataLayer[0].privilegeCustomer = 'Regular Customer';
      dataLayer[0].privilegesCardNumber = '';
      dataLayer[0].productSKU = [];
      dataLayer[0].productStyleCode = [];
      dataLayer[0].cartTotalValue = cart_data.cart_total;
      dataLayer[0].cartItemsCount = cart_data.items_qty;
      var items = cart_data.items;
      if (items !== undefined) {
        dataLayer[0].ecommerce.checkout.products = [];
        if (!drupalSettings.gtm.disabled_vars.includes('cartItemsFlocktory')) {
          dataLayer[0].cartItemsFlocktory = [];
        }

        Object.entries(items).forEach(([key, product]) => {
          dataLayer[0].productStyleCode.push(product.parent_sku);
          dataLayer[0].productSKU.push(key);
          var productData = Drupal.alshayaSeoSpc.gtmProduct(product);
          dataLayer[0].ecommerce.checkout.products.push(productData);
          if (typeof dataLayer[0].cartItemsFlocktory !== 'undefined') {
            var flocktory = {
              id: product.parent_sku,
              price: product.final_price,
              count: product.qty,
              title: product.gtm_attributes.name,
              image: product.extra_data.cart_image,
            };
            dataLayer[0].cartItemsFlocktory.push(flocktory);
          }
        });
      }
    }
  };

})(jQuery, Drupal, drupalSettings, dataLayer);
