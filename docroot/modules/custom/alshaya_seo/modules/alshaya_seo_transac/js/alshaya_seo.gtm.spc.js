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
    const dataLayer = {};
    // GTM data for SPC cart.
    if (cart_data !== undefined) {
      dataLayer.privilegeCustomer = 'Regular Customer';
      dataLayer.privilegesCardNumber = '';
      dataLayer.productSKU = [];
      dataLayer.productStyleCode = [];
      dataLayer.cartTotalValue = cart_data.cart_total;
      dataLayer.cartItemsCount = cart_data.items_qty;
      var items = cart_data.items;
      dataLayer.checkout = { actionField: { step }};
      if (items !== undefined) {
        dataLayer.checkout.products = [];
        if (!drupalSettings.gtm.disabled_vars.indexOf('cartItemsFlocktory')) {
          dataLayer.cartItemsFlocktory = [];
        }

        Drupal.alshayaSeoSpc.cartGtmCallback.apply(null, [dataLayer, ])
        Object.entries(items).forEach(function(productItem) {
          const product = productItem[1];
          Drupal.alshayaSpc.getProductData(product.sku, Drupal.alshayaSeoSpc.cartGtmCallback, {
            qty: product.qty,
            finalPrice: product.finalPrice,
            dataLayer,
          });
        });
      }
      return dataLayer;
    }
  };

  /**
   * Callback for product data used in Drupal.alshayaSeoSpc.cartGtm().
   *
   * @param product
   */
  Drupal.alshayaSeoSpc.cartGtmCallback = function(product, extraData) {
    if (product !== undefined && product.sku !== undefined) {
      // gtmAttributes.id contains value of "getSkuForNode", which we need
      // to pass for productStyleCode.
      extraData.dataLayer.productStyleCode.push(product.gtmAttributes.id);
      extraData.dataLayer.productSKU.push(product.sku);
      var productData = Drupal.alshayaSeoSpc.gtmProduct(product, extraData.qty);
      extraData.dataLayer.checkout.products.push(productData);
      if (typeof extraData.dataLayer.cartItemsFlocktory !== 'undefined') {
        var flocktory = {
          id: product.parentSKU,
          price: extraData.finalPrice,
          count: extraData.qty,
          title: product.gtmAttributes.name,
          image: product.image,
        };
        extraData.dataLayer.cartItemsFlocktory.push(flocktory);
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
    Object.entries(items).forEach(function(productItem) {
      const product = productItem[1];
      Drupal.alshayaSpc.getProductData(
        product.sku,
        function(product, extraData) {
          delete items[product.sku];
          cartLoginData.productSKU.push(product.sku);
          // gtmAttributes.id contains value of "getSkuForNode", which we need
          // to pass for productStyleCode.
          cartLoginData.productStyleCode.push(product.gtmAttributes.id);
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
