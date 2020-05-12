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
    if (window.location.href.indexOf('checkout') > -1
      && cart_data !== null
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
   * @param qty
   *   Quantity of the product in cart.
   * @returns {{quantity: *, price, name: *, variant: *, id: *, category: *,
   *   brand: *}} Product details object with gtm attributes.
   */
  Drupal.alshayaSeoSpc.gtmProduct = function (product, qty) {
    var productDetails = {
      quantity: qty,
    };
    for (var gtmKey in product.gtmAttributes) {
      productDetails[gtmKey] = product.gtmAttributes[gtmKey];
    }

    if ($.cookie('product-list') !== undefined) {
      var listValues = JSON.parse($.cookie('product-list'));
      if (listValues.hasOwnProperty(product.parentSKU)) {
        productDetails.list = listValues[product.parentSKU];
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
          Drupal.alshayaSpc.getProductData(key, Drupal.alshayaSeoSpc.cartGtmCallback, {
            qty: product.qty,
            finalPrice: product.finalPrice
          });
        });
      }
    }
  };

  /**
   * Callback for product data used in Drupal.alshayaSeoSpc.cartGtm().
   *
   * @param product
   */
  Drupal.alshayaSeoSpc.cartGtmCallback = function(product, extraData) {
    if (product !== undefined && product.sku !== undefined) {
      dataLayer[0].productStyleCode.push(product.parentSKU);
      dataLayer[0].productSKU.push(product.sku);
      var productData = Drupal.alshayaSeoSpc.gtmProduct(product, 1);
      dataLayer[0].ecommerce.checkout.products.push(productData);
      if (typeof dataLayer[0].cartItemsFlocktory !== 'undefined') {
        var flocktory = {
          id: product.parentSKU,
          price: extraData.finalPrice,
          count: extraData.qty,
          title: product.gtmAttributes.name,
          image: product.image,
        };
        dataLayer[0].cartItemsFlocktory.push(flocktory);
      }
    }
  };

  Drupal.alshayaSeoSpc.loginData = function(cart_data) {
    const cartLoginData = {
      language: drupalSettings.path.currentLanguage,
      country: drupalSettings.country_name,
      currency: drupalSettings.alshaya_spc.currency_config.currency_code,
      pageType: 'checkout login page',
      productSKU: [],
      productStyleCode: [],
      cartTotalValue: cart_data.cart_total,
      cartItemsCount: cart_data.items_qty,
    }
    // Copy items object.
    var items = JSON.parse(JSON.stringify(cart_data.items));
    Object.entries(items).forEach(([key, product]) => {
      Drupal.alshayaSpc.getProductData(
        key,
        function(product, extraData) {
          delete items[product.sku];
          cartLoginData.productSKU.push(product.sku);
          cartLoginData.productStyleCode.push(product.parentSKU);
          if (Object.keys(items).length === 0) {
            dataLayer.push(cartLoginData);
          }
        },
        {
        qty: product.qty,
        finalPrice: product.finalPrice
      });
    });
  };

})(jQuery, Drupal, drupalSettings, dataLayer);
