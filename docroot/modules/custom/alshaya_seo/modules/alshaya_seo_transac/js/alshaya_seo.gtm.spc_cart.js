/**
 * @file
 * JS code to integrate with GTM.
 */

(function ($, Drupal, dataLayer) {
  'use strict';

  Drupal.behaviors.spcCartGtm = {
    attach: function (context, settings) {
      if (localStorage.hasOwnProperty('cart_data')) {
        var cart_data = JSON.parse(localStorage.getItem('cart_data'));
        Drupal.alshayaSpcCartGtm(cart_data.cart);
      }
    }
  };

  document.addEventListener('refreshCart', function (e) {
    Drupal.alshayaSpcCartGtm(e.detail.data());
  });

  document.addEventListener('updateCartItemData', function (e) {
    var gtmEvent = '';
    var item = e.detail.data.item;
    var qty = e.detail.data.qty;
    if (item.qty > qty) {
      item.qty = item.qty - qty;
      gtmEvent = 'removeFromCart';
    }
    else if (item.qty < qty) {
      item.qty = qty - item.qty;
      gtmEvent = 'addToCart';
    }
    Drupal.alshayaSpcGtmUpdateCartItem(item, gtmEvent);
  });

  document.addEventListener('promoCodeSuccess', function (e) {
    // Push promoCode event into dataLayer.
    var promoCode = e.detail.data;
    var data = {
      event: 'promoCode',
      couponCode: promoCode,
      couponStatus: 'pass',
    };
    dataLayer.push(data);
  });

  document.addEventListener('promoCodeFailed', function (e) {
    var promoCode = e.detail.data;
    var data = {
      event: 'promoCode',
      couponCode: promoCode,
      couponStatus: 'fail',
    };
    dataLayer.push(data);
  });

  document.addEventListener('changeShippingMethod', function (e) {
    var deliveryType = e.detail.data.carrier_title;
    Drupal.alshayaSeoGtmPushCheckoutOption(deliveryType, 2);
  });

  document.addEventListener('placeOrderConfirmation', function (e) {
    Drupal.alshayaSeoGtmPushCheckoutOption(e.detail.data.payment.method, 2);
  });

  /**
   * Helper function to push checkout option to GTM.
   *
   * @param optionLabel
   * @param step
   */
  Drupal.alshayaSeoGtmPushCheckoutOption = function (optionLabel, step) {
    var data = {
      event: 'checkoutOption',
      ecommerce: {
        checkout_option: {
          actionField: {
            step: step,
            option: optionLabel
          }
        }
      }
    };

    dataLayer.push(data);
  };

  /**
   * GTM dataLayer checkout event.
   *
   * @param cart_data
   *   Cart data Object from localStorage.
   */
  Drupal.alshayaSpcCartGtm = function(cart_data) {
    // GTM data for SPC cart.
    if (cart_data !== undefined) {
      dataLayer[0].productSKU = [];
      dataLayer[0].productStyleCode = [];
      dataLayer[0].cartTotalValue = cart_data.cart_total;
      dataLayer[0].cartItemsCount = cart_data.items_qty;
      var items = cart_data.items;
      dataLayer[0].ecommerce.checkout.products = [];
      dataLayer[0].cartItemsFlocktory = [];
      Object.entries(items).forEach(([key, product]) => {
        dataLayer[0].productStyleCode.push(product.parent_sku);
        dataLayer[0].productSKU.push(key);
        var productData = Drupal.alshayaSpcGtmProduct(product);
        dataLayer[0].ecommerce.checkout.products.push(productData);
        var flocktory = {
          id: product.parent_sku,
          price: product.final_price,
          count: product.qty,
          title: product.gtm_attributes['gtm-name'],
          image: product.extra_data.cart_image,
        };
        dataLayer[0].cartItemsFlocktory.push(flocktory);
      });
    }
  };

  /**
   * GTM datalayer removeFromcart, addToCart events.
   *
   * @param product
   *   Product object with gtm attributes.
   * @param gtmEvent
   *   GTM event string removeFromcart, addToCart.
   */
  Drupal.alshayaSpcGtmUpdateCartItem = function (product, gtmEvent) {
    var productData = {
      event: gtmEvent,
      ecommerce: {
        currencyCode: drupalSettings.alshaya_spc.currency_config.currency_code,
        add: {
          product: []
        }
      }
    };
    var productDetails = Drupal.alshayaSpcGtmProduct(product);
    productDetails.metric2 = product.final_price;
    productData.ecommerce.add.product.push(productDetails);
    dataLayer.push(productData);
  };

  /**
   * Helper function to get product GTM attributes.
   *
   * @param product
   *   Product Object with gtm attributes.
   * @returns {{quantity: *, price, name: *, variant: *, id: *, category: *,
   *   brand: *}} Product details object with gtm attributes.
   */
  Drupal.alshayaSpcGtmProduct = function (product) {
    var attributes = product.attributes;
    var i = 0;
    for (i = 0; i < attributes.length; i++) {
      if (attributes[i]['key'] === 'product_brand') {
        var productBrand = attributes[i]['value'];
      }
    }
    var productDetails = {
      name: product.gtm_attributes['gtm-name'],
      id: product.parent_sku,
      price: product.final_price,
      brand: productBrand,
      category: product.gtm_attributes['gtm-category'],
      variant: product.sku,
      quantity: product.qty,
    };
    productDetails.dimension1 = product.gtm_attributes.dimension1;
    if (product.hasOwnProperty('configurable_values')) {
      productDetails.dimension2 = 'configurable';
    }
    if (product.final_price != product.original_price) {
      productDetails.dimension3 = 'discounted';
    }
    if (product.gtm_attributes.hasOwnProperty('dimension4')) {
      productDetails.dimension4 = product.gtm_attributes.dimension4;
    }
    if (product.gtm_attributes.hasOwnProperty('dimension5')) {
      productDetails.dimension5 = product.gtm_attributes.dimension5;
    }
    if (product.gtm_attributes.hasOwnProperty('dimension6')) {
      productDetails.dimension6 = product.gtm_attributes.dimension6;
    }
    if ($.cookie('product-list') !== undefined) {
      var listValues = JSON.parse($.cookie('product-list'));
      if (listValues.hasOwnProperty(product.parent_sku)) {
        productDetails.list = listValues[product.parent_sku];
      }
    }
    return productDetails;
  };

})(jQuery, Drupal, dataLayer);
