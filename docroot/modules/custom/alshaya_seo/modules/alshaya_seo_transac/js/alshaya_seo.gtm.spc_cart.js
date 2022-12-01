/**
 * @file
 * JS code to integrate with GTM.
 */

(function ($, Drupal, drupalSettings, dataLayer) {

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
    var action = gtmEvent === 'removeFromCart' ? 'remove' : 'add';
    var productData = {
      event: gtmEvent,
      ecommerce: {
        currencyCode: drupalSettings.gtm.currency,
        [action]: {
          products: []
        }
      }
    };

    // Get product info from storage.
    var key = 'product:' + drupalSettings.path.currentLanguage + ':' + product.sku;
    var productInfo = Drupal.getItemFromLocalStorage(key);
    // Add inStock infor only for removeFromCart event.
    if(action === 'remove') {
      productInfo['inStock'] = product['in_stock'];
    }
    if (productInfo !== null) {
      var productDetails = Drupal.alshayaSeoSpc.gtmProduct(productInfo, product.qty);
      // metric value will be negative in case of product removal from cart.
      productDetails.metric2 = gtmEvent === 'removeFromCart' ? -1 * product.finalPrice : product.finalPrice;
      productData.ecommerce[action].products.push(productDetails);
      dataLayer.push(productData);
    }
  };

  /**
   * Helper function to prepare productImpressions.
   *
   * @param recommendedProducts
   */
  Drupal.alshayaSeoSpc.prepareProductImpression = function (recommendedProducts, position) {
    var impressions = [];
    var productLinkSelector = $('.spc-recommended-products .block-content .recommended-product:not(".impression-processed")');

    if (productLinkSelector.length > 0) {
      productLinkSelector.each(function () {
        if ($(this).isCarouselElementInViewPort(0, 40)) {
          $(this).addClass('impression-processed');
          // Cannot use Drupal.alshayaSeoSpc.gtmProduct as the method expectes
          // product parameter to have gtmAttributes key while in localstorage
          // it has gtm_attributes key.
          var impression = Drupal.alshayaSeoSpc.getRecommendationGtmAttributes($(this).attr('data-sku'));
          impression.list = Drupal.alshayaSeoSpc.getRecommendationsListName();
          impression.position = Drupal.alshayaSeoSpc.getRecommendationsPosition($(this));
          // Keep variant empty for impression pages. Populated only post add to cart action.
          impression.variant = '';
          impressions.push(impression);
        }
      });
    }

    return impressions;
  };

  /**
   * Function to get the gtm attributes for a recommendation product.
   *
   * @param string sku
   *   The simple sku value whose GTM attributes are required.
   *
   * @return string
   *   The recommendation list name.
   */
  Drupal.alshayaSeoSpc.getRecommendationGtmAttributes = function (sku) {
    var key = 'recommendedProduct:' + drupalSettings.path.currentLanguage;
    var relatedProductsInfo = Drupal.getItemFromLocalStorage(key);
    // Cannot use Drupal.alshayaSeoSpc.gtmProduct as the method expectes
    // product parameter to have gtmAttributes key while in localstorage
    // it has gtm_attributes key.
    return relatedProductsInfo[sku].gtm_attributes;
  };

  /**
   * Function to get the list name for a recommendation product.
   *
   * @return string
   *   The recommendation list name.
   */
  Drupal.alshayaSeoSpc.getRecommendationsListName = function () {
    var gtmListName = $('body').attr('gtm-list-name');
    var label = $('.spc-post-content .spc-checkout-section-title').text();
    return (productRecommendationsSuffix + gtmListName.replace('placeholder', label)).toLowerCase();
  };

  /**
   * Sets the postion of all recommended products in carousel at once.
   */
  Drupal.alshayaSeoSpc.setRecommendationsPosition = function () {
    var count = 1;
    $('.spc-recommended-products .recommended-product').each(function () {
      $(this).data('list-item-position', count++);
    });
  }

  /**
   * Gets the position of an individual element in the carousel.
   *
   * @param object element
   *   The recommendation product javascript object.
   *
   * @return number
   *   The position of the item in the carousel.
   */
  Drupal.alshayaSeoSpc.getRecommendationsPosition = function (element) {
    return parseInt($(element).closest('.spc-recommended-products .recommended-product').data('list-item-position'));
  }

  document.addEventListener('recommendedProductsLoad', function (e) {
    // Set the position of the items in the carousel.
    Drupal.alshayaSeoSpc.setRecommendationsPosition();
    // Process impressions as soon as products load as that section might
    // already be on screen.
    Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshayaSeoSpc.prepareProductImpression, $('.spc-post-content'), drupalSettings, e);
    // Process impressions on scroll for cases where the recommendations have
    // loaded but the items are not visible on screen.
    window.addEventListener('scroll', debounce(function (scrollEvent) {
      Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshayaSeoSpc.prepareProductImpression, $('.spc-post-content'), drupalSettings, scrollEvent);
    }, 500));
    // Process impressions when user swipes the carousel on mobile.
    window.addEventListener('touchmove', debounce(function (touchEvent) {
      Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshayaSeoSpc.prepareProductImpression, $('.spc-post-content'), drupalSettings, touchEvent);
    }, 500));
    // Process impressions when user is leaving the page.
    window.addEventListener('pagehide', function (pagehideEvent) {
      Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshayaSeoSpc.prepareProductImpression, $('.spc-post-content'), drupalSettings, pagehideEvent);
    });
    // Process impressions when user clicks previous button.
    document.querySelectorAll('.spc-recommended-products .nav-prev').forEach(function (element) {
      element.addEventListener('click', function (clickEvent) {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshayaSeoSpc.prepareProductImpression, $('.spc-post-content'), drupalSettings, clickEvent);
      });
    });
    // Process impressions when user clicks next button.
    document.querySelectorAll('.spc-recommended-products .nav-next').forEach(function (element) {
      element.addEventListener('click', function (clickEvent) {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshayaSeoSpc.prepareProductImpression, $('.spc-post-content'), drupalSettings, clickEvent);
      });
    });

    // Add product click handler.
    document.querySelectorAll('.spc-recommended-products a').forEach(function (element, index) {
      element.addEventListener('click', function () {
        var listName = Drupal.alshayaSeoSpc.getRecommendationsListName();
        // Currently the elements do not have GTM attributes. So we fetch them
        // and add them to each element and send them to be processed by the
        // product click handler.
        var elementGtmAttributes = Drupal.alshayaSeoSpc.getRecommendationGtmAttributes(element.getAttribute('data-sku'));
        // Product click handler expects attributes to have 'gtm-' prefix so we
        // send it that way.
        const position = Drupal.alshayaSeoSpc.getRecommendationsPosition(element);
        const attribute_keys = Object.keys(elementGtmAttributes);
        var i;
        for (i = 0; i < attribute_keys.length; i++) {
          element.setAttribute('gtm-' + attribute_keys[i], elementGtmAttributes[attribute_keys[i]]);
        }
        Drupal.alshaya_seo_gtm_push_product_clicks($(element), drupalSettings.gtm.currency, listName, position);
      });
    });
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
    // Add aura common data in checkout step 1 gtm event.
    if (typeof drupalSettings.aura !== 'undefined' && drupalSettings.aura.enabled) {
      Object.assign(data, Drupal.alshayaSeoGtmPrepareAuraCommonDataFromCart());
    }
    setTimeout(() => {
      dataLayer.push(data);
    }, 500);
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
    // Instead of Card number add isAdvantageCard : Yes for pass.
    // Check react/alshaya_spc/js/cart/components/cart-promo-block/index.js.
    if (promoCode.includes(`Advantage_Card_${drupalSettings.userDetails.userID}`)) {
      var isAdvantageCard = 'Yes';
    }
    var data = {
      event: 'promoCode',
      couponCode: promoCode,
      couponStatus: 'pass',
      isAdvantageCard: isAdvantageCard,
    };
    dataLayer.push(data);
  });

  document.addEventListener('promoCodeFailed', function (e) {
    var promoCode = e.detail.data;
    // Instead of Card number add isAdvantageCard : No for fail.
    // Check react/alshaya_spc/js/cart/components/cart-promo-block/index.js.
    if (promoCode.includes(`Advantage_Card_${drupalSettings.userDetails.userID}`)) {
      var isAdvantageCard = 'No';
    }
    var data = {
      event: 'promoCode',
      couponCode: promoCode,
      couponStatus: 'fail',
      isAdvantageCard: isAdvantageCard,
    };
    dataLayer.push(data);
  });

  Drupal.behaviors.spcCartGtm = {
    attach: function (context, settings) {
      var step = Drupal.alshayaSeoSpc.getStepFromContainer();
      var cart_data = Drupal.alshayaSpc.getCartData();
      $('body[gtm-container="cart page"]').once('spc-cart-gtm-onetime').each(function () {
        if (cart_data) {
          Drupal.alshayaSeoSpc.cartGtm(cart_data, step);
        }
      });
    }
  };

})(jQuery, Drupal, drupalSettings, dataLayer);
