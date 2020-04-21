/**
 * @file
 * JS code to integrate with GTM.
 */

(function ($, Drupal, drupalSettings, dataLayer) {
  'use strict';

  Drupal.alshaya_seo_spc = Drupal.alshaya_seo_spc || {};
  /**
   * GTM datalayer removeFromcart, addToCart events.
   *
   * @param product
   *   Product object with gtm attributes.
   * @param gtmEvent
   *   GTM event string removeFromcart, addToCart.
   */
  Drupal.alshaya_seo_spc.gtmUpdateCartItem = function (product, gtmEvent) {
    var productData = {
      event: gtmEvent,
      ecommerce: {
        currencyCode: drupalSettings.alshaya_spc.currency_config.currency_code,
        add: {
          product: []
        }
      }
    };
    var productDetails = Drupal.alshaya_seo_spc.gtmProduct(product);
    productDetails.metric2 = product.final_price;
    productData.ecommerce.add.product.push(productDetails);
    dataLayer.push(productData);
  };

  /**
   * Helper function to push productImpression to GTM.
   *
   * @param customerType
   */
  Drupal.alshaya_seo_spc.prepareProductImpression = function (context, settings, skus, position) {
    var impressions = [];
    var currencyCode = settings.alshaya_spc.currency_config.currency_code;
    var listName = $('body').attr('gtm-list-name');
    var cart_data = JSON.parse(localStorage.getItem('cart_data'));
    if (cart_data.cart.recommended_products !== null) {
      var items = cart_data.cart.recommended_products;
      var count = position + 1;
      Object.entries(items).forEach(([key, product]) => {
        if (skus.includes(key)) {
          var impression = {};
          impression.name = product.title;
          impression.id = product.id;
          impression.price = product.final_price;
          impression.category = product.gtm_attributes['gtm-category'];
          impression.dimension1 = product.gtm_attributes.dimension1;
          if (product.hasOwnProperty('configurable_values')) {
            impression.dimension2 = 'configurable';
          }
          if (product.final_price !== product.original_price) {
            impression.dimension3 = 'discounted';
          }
          if (product.gtm_attributes.hasOwnProperty('dimension4') && product.gtm_attributes.dimension4) {
            impression.dimension4 = product.gtm_attributes.dimension4;
          }
          if (product.gtm_attributes.hasOwnProperty('dimension5') && product.gtm_attributes.dimension5) {
            impression.dimension5 = product.gtm_attributes.dimension5;
          }
          if (product.gtm_attributes.hasOwnProperty('dimension6') && product.gtm_attributes.dimension6) {
            impression.dimension6 = product.gtm_attributes.dimension6;
          }
          if (product.gtm_attributes.hasOwnProperty('gtm-brand') && product.gtm_attributes['gtm-brand']) {
            impression.brand = product.gtm_attributes['gtm-brand'];
          }
          impression.list = listName;
          impression.position = count;
          // Keep variant empty for impression pages. Populated only post add to cart action.
          impression.variant = '';
          count++;
          impressions.push(impression);
        }
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

  document.addEventListener('refreshCart', function (e) {
    var step = Drupal.alshaya_seo_spc.getStepFromContainer();
    Drupal.alshaya_seo_spc.cartGtm(e.detail.data(), step);
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
    Drupal.alshaya_seo_spc.gtmUpdateCartItem(item, gtmEvent);
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
      var step = Drupal.alshaya_seo_spc.getStepFromContainer();
      var cart_data = JSON.parse(localStorage.getItem('cart_data'));
      if (cart_data !== null && cart_data && cart_data.cart && cart_data.cart.cart_id) {
        Drupal.alshaya_seo_spc.cartGtm(cart_data.cart, step);
      }

      // Track Product impressions.
      $(window).once('alshaya-seo-gtm-cart-pi').on('scroll', debounce(function (event) {
        var productSkus = [];
        var productLinkProcessedSelector = $('.impression-processed');
        var position = productLinkProcessedSelector.length;
        $('.recommended-product:not(".impression-processed"):visible').each(function () {
          if ($(this).isElementInViewPort(0)) {
            $(this).addClass('impression-processed');
            productSkus.push($(this).attr('data-sku'));
          }
        });
        if (step === 1 && productSkus.length > 0) {
          Drupal.alshaya_seo_spc.prepareProductImpression(context, settings, productSkus, position);
        }
      }, 500));

      $('.spc-recommended-products .block-content').once('alshaya-seo-gtm-impressions').on('scroll', debounce(function (event) {
        var productSkus = [];
        var productLinkProcessedSelector = $('.impression-processed');
        var position = productLinkProcessedSelector.length;
        $('.recommended-product:not(".impression-processed"):visible').each(function () {
          if ($(this).isElementInViewPort(0)) {
            $(this).addClass('impression-processed');
            productSkus.push($(this).attr('data-sku'));
          }
        });
        if (step === 1 && productSkus.length > 0) {
          Drupal.alshaya_seo_spc.prepareProductImpression(context, settings, productSkus, position);
        }
      }, 500));
    }
  };

})(jQuery, Drupal, drupalSettings, dataLayer);
