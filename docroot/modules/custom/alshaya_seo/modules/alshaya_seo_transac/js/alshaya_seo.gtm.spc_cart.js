/**
 * @file
 * JS code to integrate with GTM.
 */

(function ($, Drupal, dataLayer) {
  'use strict';

  Drupal.behaviors.spcCartGtm = {
    attach: function (context, settings) {
      var cart_data = JSON.parse(localStorage.getItem('cart_data'));
      var step = Drupal.alshayaSpcGetStepFromContainer();
      if (cart_data !== null && cart_data && cart_data.cart && cart_data.cart.cart_id) {
        Drupal.alshayaSpcCartGtm(cart_data.cart, step);
      }

      if (localStorage.hasOwnProperty('userID')) {
        if (step === 2) {
          Drupal.alshayaSeoGtmPushCheckoutOption('Home Delivery', 2);
        }
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
          Drupal.alshayaSpcPrepareProductImpression(context, settings, productSkus, position);
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
          Drupal.alshayaSpcPrepareProductImpression(context, settings, productSkus, position);
        }
      }, 500));

      /**
       * Fire checkoutOption on cart page.
       */
      if (drupalSettings.user.uid !== 0) {
        Drupal.alshayaSeoGtmPushCheckoutOption('Logged In', 1);
      }
    }
  };

  document.addEventListener('refreshCart', function (e) {
    var step = Drupal.alshayaSpcGetStepFromContainer();
    Drupal.alshayaSpcCartGtm(e.detail.data(), step);
  });

  document.addEventListener('refreshCartOnPaymentMethod', function (e) {
    Drupal.alshayaSpcCartGtm(e.detail.cart, 3);
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

  document.addEventListener('deliveryMethodChange', function (e) {
    var deliveryMethod = e.detail.data;
    if (deliveryMethod === 'hd') {
      Drupal.alshayaSpcCheckoutGtmDeliveryMethod('Home Delivery');
    }
    else {
      Drupal.alshayaSpcCheckoutGtmDeliveryMethod('Click & Collect');
    }
  });

  document.addEventListener('refreshCartOnCnCSelect', function (e) {
    var cart_data = e.detail;
    var data = {
      event: 'storeSelect',
      storeName: cart_data.cart.store_info.name,
      storeAddress: cart_data.cart.store_info.address,
    };
    dataLayer.push(data);
    var data = {
      event: 'checkoutOption',
      ecommerce: {
        checkout_option: {
          actionField: {
            step: 2,
            option: 'Click & Collect',
            action: 'checkout_option',
          }
        }
      }
    };
    dataLayer.push(data);
  });

  document.addEventListener('refreshCartOnAddress', function (e) {
    var data = {
      event: 'checkoutOption',
      ecommerce: {
        checkout_option: {
          actionField: {
            step: 2,
            option: 'Home Delivery - subdelivery',
            action: 'checkout_option',
          }
        }
      }
    };
    dataLayer.push(data);
  });

  document.addEventListener('changeShippingMethod', function (e) {
    var deliveryType = e.detail.data.carrier_title;
    Drupal.alshayaSeoGtmPushCheckoutOption(deliveryType, 2);
  });

  document.addEventListener('orderPaymentMethod', function (e) {
    Drupal.alshayaSeoGtmPushCheckoutOption(e.detail.payment_method, 3);
  });

  /**
   * Helper function to get step number from body attr gtm-container.
   */
  Drupal.alshayaSpcGetStepFromContainer = function () {
    var step = 1;
    var cart_data = JSON.parse(localStorage.getItem('cart_data'));
    if (window.location.href.indexOf('checkout') > -1) {
      step = 2;
    }
    if (cart_data !== null && cart_data.cart.hasOwnProperty('cart_payment_method') && cart_data.cart.cart_payment_method !== null) {
      step = 3;
    }
    return step;
  };

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
   * @param step
   *   Checkout step for gtm checkout event.
   */
  Drupal.alshayaSpcCartGtm = function(cart_data, step) {
    // GTM data for SPC cart.
    if (cart_data !== undefined) {
      dataLayer[0].ecommerce.checkout.actionField.step = step;
      dataLayer[0].productSKU = [];
      dataLayer[0].productStyleCode = [];
      dataLayer[0].cartTotalValue = cart_data.cart_total;
      dataLayer[0].cartItemsCount = cart_data.items_qty;
      var items = cart_data.items;
      if (items !== undefined) {
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
    if (product.gtm_attributes.hasOwnProperty('dimension4') && product.gtm_attributes.dimension4) {
      productDetails.dimension4 = product.gtm_attributes.dimension4;
    }
    if (product.gtm_attributes.hasOwnProperty('dimension5') && product.gtm_attributes.dimension5) {
      productDetails.dimension5 = product.gtm_attributes.dimension5;
    }
    if (product.gtm_attributes.hasOwnProperty('dimension6') && product.gtm_attributes.dimension6) {
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

  Drupal.alshayaSpcCheckoutGtmDeliveryMethod = function (method) {
    var data = {
      event: 'deliveryOption',
      eventLabel: method,
    };
    dataLayer.push(data);
  };

  /**
   * Helper function to push productImpression to GTM.
   *
   * @param customerType
   */
  Drupal.alshayaSpcPrepareProductImpression = function (context, settings, skus, position) {
    var impressions = [];
    var currencyCode = settings.alshaya_spc.currency_config.currency_code;
    var listName = $('body').attr('gtm-list-name');
    const key = 'recommendedProduct:' + settings.path.currentLanguage;
    var recommendedProducts = JSON.parse(localStorage.getItem('cart_data'));
    if (recommendedProducts !== null) {
      var items = recommendedProducts;
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

})(jQuery, Drupal, dataLayer);
