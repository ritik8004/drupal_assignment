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
    var listName = $('body').attr('gtm-list-name');
    var productLinkSelector = $('.spc-recommended-products .block-content a:not(".impression-processed")');
    var productLinkProcessedSelector = $('.spc-recommended-products .block-content a.impression-processed');
    var count = productLinkProcessedSelector.length + 1;
    var label = $('.spc-post-content .spc-checkout-section-title').text();

    if (productLinkSelector.length > 0) {
      productLinkSelector.each(function () {
        // if ($(this).isElementInViewPort(0, 40, true)) {
          console.log('productLinkSelector');
          $(this).addClass('impression-processed');
          // var impression = Drupal.alshayaSeoSpc.getProductValues($(this));
          // Get product info from storage.
          var key = 'recommendedProduct:' + drupalSettings.path.currentLanguage;
          var relatedProductsInfo = JSON.parse(localStorage.getItem(key));
          // Cannot use Drupal.alshayaSeoSpc.gtmProduct as the method expectes
          // product parameter to have gtmAttributes key while in localstorage
          // it has gtm_attributes key.
          var impression = relatedProductsInfo[$(this).attr('data-sku')]['gtm_attributes'];

          impression.list = (productRecommendationsSuffix + listName.replace('placeholder', label)).toLowerCase();
          impression.position = count;
          // Keep variant empty for impression pages. Populated only post add to cart action.
          impression.variant = '';
          impressions.push(impression);
          count++;

        // }
      });
    }

    return impressions;
  };

  // document.addEventListener('recommendedProductsLoad', function (e) {
  //   Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshayaSeoSpc.prepareProductImpression, $('.spc-post-content'), drupalSettings, e);
  //   document.addEventListener('.scroll', Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshayaSeoSpc.prepareProductImpression, $('.spc-post-content'), drupalSettings, e));
  //   document.getElementsByClassName('.spc-recommended-products .nav-next').addEventListener('.spc-recommended-products .nav-next', Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshayaSeoSpc.prepareProductImpression, $('.spc-post-content'), drupalSettings, e));
  //   document.getElementsByClassName('.spc-recommended-products .nav-prev').addEventListener('.spc-recommended-products .nav-next', Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshayaSeoSpc.prepareProductImpression, $('.spc-post-content'), drupalSettings, e));
  // });

  document.addEventListener('recommendedProductsLoad', function (e) {
    Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshayaSeoSpc.prepareProductImpression, $('.spc-post-content'), drupalSettings, e);
    window.addEventListener('.scroll', Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshayaSeoSpc.prepareProductImpression, $('.spc-post-content'), drupalSettings, e));
    // var recommendations = document.getElementsByClassName('spc-recommended-products');
    // console.log(document.getElementsByClassName('spc-recommended-products nav-next'));
    // console.log(document.getElementsByClassName('spc-recommended-products nav-prev'));
    // document.getElementsByClassName('spc-recommended-products nav-next')[0].addEventListener('click', Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshayaSeoSpc.prepareProductImpression, $('.spc-post-content'), drupalSettings, e));
    // document.getElementsByClassName('spc-recommended-products nav-prev')[0].addEventListener('click', Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshayaSeoSpc.prepareProductImpression, $('.spc-post-content'), drupalSettings, e));
  });

  document.addEventListener('refreshCart', function (e) {
    var data = {
      language: drupalSettings.gtm.language,
      country: drupalSettings.gtm.country,
      currency: drupalSettings.gtm.currency,
      pageType: drupalSettings.gtm.pageType,
      event: 'checkout',
      ecommerce: {
        currencyCode: drupalSettings.gtm.currency,
        checkout: {
        },
      },
    };
    var cartData = Drupal.alshayaSeoSpc.cartGtm(
      e.detail.data(),
      Drupal.alshayaSeoSpc.getStepFromContainer()
    );
    Object.assign(data.ecommerce.checkout, cartData.checkout);
    delete cartData.checkout;
    Object.assign(data, cartData);
    dataLayer.push(data);
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
