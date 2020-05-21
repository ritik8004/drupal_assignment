/**
 * @file
 * JS code to integrate with GTM.
 */

(function ($, Drupal, drupalSettings, dataLayer) {
  'use strict';

  Drupal.alshayaSeoSpc = Drupal.alshayaSeoSpc || {};

  /**
   * GTM datalayer remove from cart, addToCart events.
   *
   * @param product
   *   Product object with gtm attributes.
   * @param gtmEvent
   *   GTM event string removeFromcart, addToCart.
   */
  Drupal.alshayaSeoSpc.gtmUpdateCartItem = function (product, gtmEvent) {
    var productData = {
      event: gtmEvent,
      ecommerce: {
        currencyCode: drupalSettings.alshaya_spc.currency_config.currency_code,
        add: {
          product: []
        }
      }
    };

    // Get product info from storage.
    var key = 'product:' + drupalSettings.path.currentLanguage + ':' + product.sku;
    var productInfo = JSON.parse(localStorage.getItem(key));
    if (productInfo !== null) {
      var productDetails = Drupal.alshayaSeoSpc.gtmProduct(productInfo, product.qty);
      productDetails.metric2 = product.finalPrice;
      productData.ecommerce.add.product.push(productDetails);
      dataLayer.push(productData);
    }
  };

  /**
   * Helper function to push productImpression to GTM.
   *
   * @param customerType
   */
  Drupal.alshayaSeoSpc.prepareProductImpression = function (recommendedProducts, position) {
    var impressions = [];
    var currencyCode = drupalSettings.alshaya_spc.currency_config.currency_code;
    var listName = $('body').attr('gtm-list-name');
    if (recommendedProducts !== null) {
      var items = recommendedProducts;
      var count = position + 1;
      var excludeKeys = ['name', 'id', 'price', 'list', 'position'];
      Object.entries(items).forEach(([, product]) => {
        // if (skus.includes(key)) {
          var impression = {};
          impression.id = product.id;
          impression.name = product.title;
          impression.price = product.final_price;
          impression.list = listName;
          impression.position = count;
          for (var gtmKey in product.gtm_attributes) {
            if (!excludeKeys.includes(gtmKey)) {
              impression[gtmKey] = product.gtm_attributes[gtmKey];
            }
          }
          // Keep variant empty for impression pages. Populated only post add to cart action.
          impression.variant = '';
          count++;
          impressions.push(impression);
      });
      if (impressions.length > 0) {
        // To avoid max size in POST data issue we do it in batches of 10.
        while (impressions.length > 0) {
          var data = {
            event: 'productImpression',
            ecommerce: {
              currencyCode: currencyCode,
              impressions: impressions.splice(0, 10)
            }
          };

          dataLayer.push(data);
        }
      }
    }
  };

  document.addEventListener('recommendedProductsLoad', function (e) {
    Drupal.alshayaSeoSpc.prepareProductImpression(e.detail.products, 0);
  });

  document.addEventListener('refreshCart', function (e) {
    var step = Drupal.alshayaSeoSpc.getStepFromContainer();
    Drupal.alshayaSeoSpc.cartGtm(e.detail.data(), step);
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
    Drupal.alshayaSeoSpc.gtmUpdateCartItem(item, gtmEvent);
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

  Drupal.behaviors.spcCartGtm = {
    attach: function (context, settings) {
      var step = Drupal.alshayaSeoSpc.getStepFromContainer();
      var cart_data = Drupal.alshayaSpc.getCartData();
      $('body[gtm-container="cart page"]').once('spc-cart-gtm-onetime').each(function() {
        if (cart_data) {
          Drupal.alshayaSeoSpc.cartGtm(cart_data, step);
        }
      });
    }
  };

})(jQuery, Drupal, drupalSettings, dataLayer);
