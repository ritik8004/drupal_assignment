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
    return (window.location.href.indexOf('checkout') > -1) ? 2 : 1;
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
  Drupal.alshayaSeoSpc.cartGtm = function (cart_data, step) {
    // GTM data for SPC cart.
    if (cart_data !== undefined) {
      const cartDataLayer = {
        privilegeCustomer: 'Regular Customer',
        privilegesCardNumber: '',
        productSKU: [],
        productStyleCode: [],
        cartTotalValue: cart_data.cart_total,
        cartItemsCount: cart_data.items_qty,
        checkout: {
          actionField: { step: step },
          products: [],
        }
      };
      if (cart_data.items !== undefined) {
        if (!drupalSettings.gtm.disabled_vars.indexOf('cartItemsFlocktory')) {
          cartDataLayer.cartItemsFlocktory = [];
        }

        Object.entries(cart_data.items).forEach(function (productItem) {
          const product = productItem[1];
          // Skip the get product data for virtual product ( This is applicable
          // when egift card module is enabled and cart item is virtual.)
          if (typeof drupalSettings.egiftCard !== 'undefined'
            && typeof drupalSettings.egiftCard.enabled !== 'undefined'
            && drupalSettings.egiftCard.enabled
            && ((typeof product.product_type !== 'undefined'
            && product.product_type === 'virtual')
            || (Object.prototype.hasOwnProperty.call(product, 'isEgiftCard')
            && product.isEgiftCard))) {
            return;
          }
          Drupal.alshayaSpc.getProductData(product.sku, Drupal.alshayaSeoSpc.cartGtmCallback, {
            qty: product.qty,
            finalPrice: product.finalPrice,
            cartDataLayer: Object.assign({}, cartDataLayer),
          });
        });
      }
      return cartDataLayer;
    }
  };

  /**
   * Callback for product data used in Drupal.alshayaSeoSpc.cartGtm().
   *
   * @param product
   */
  Drupal.alshayaSeoSpc.cartGtmCallback = function (product, extraData) {
    if (product !== undefined && product.sku !== undefined && product.gtmAttributes !== undefined) {
      // gtmAttributes.id contains value of "getSkuForNode", which we need
      // to pass for productStyleCode.
      extraData.cartDataLayer.productStyleCode.push(product.gtmAttributes.id);
      extraData.cartDataLayer.productSKU.push(product.sku);
      var productData = Drupal.alshayaSeoSpc.gtmProduct(product, extraData.qty);
      extraData.cartDataLayer.checkout.products.push(productData);
      if (typeof extraData.cartDataLayer.cartItemsFlocktory !== 'undefined') {
        var flocktory = {
          id: product.parentSKU,
          price: extraData.finalPrice,
          count: extraData.qty,
          title: product.gtmAttributes.name,
          image: product.image,
        };
        extraData.cartDataLayer.cartItemsFlocktory.push(flocktory);
      }
    }
  };

  Drupal.alshayaSeoSpc.loginData = function (cart_data) {
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
    Object.entries(items).forEach(function (productItem) {
      const product = productItem[1];
      // Skip the get product data for virtual product ( This is applicable
      // when egift card module is enabled and cart item is virtual.)
      if (typeof drupalSettings.egiftCard !== 'undefined'
        && typeof drupalSettings.egiftCard.enabled !== 'undefined'
        && drupalSettings.egiftCard.enabled
        && ((typeof product.product_type !== 'undefined'
          && product.product_type === 'virtual')
          || (Object.prototype.hasOwnProperty.call(product, 'isEgiftCard')
            && product.isEgiftCard))) {
        return;
      }
      Drupal.alshayaSpc.getProductData(
        product.sku,
        function (product, extraData) {
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
