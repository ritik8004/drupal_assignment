/**
 * @file
 * JS code to integrate with GTM.
 */

(function ($, Drupal, dataLayer) {
  'use strict';

  document.addEventListener('deliveryMethodChange', function (e) {
    var deliveryMethod = e.detail.data;
    if (deliveryMethod === 'hd') {
      Drupal.alshaya_seo_spc.gtmDeliveryMethod('Home Delivery');
    }
    else {
      Drupal.alshaya_seo_spc.gtmDeliveryMethod('Click & Collect');
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
    Drupal.alshaya_seo_spc.gtmPushCheckoutOption(deliveryType, 2);
  });

  document.addEventListener('refreshCartOnPaymentMethod', function (e) {
    Drupal.alshaya_seo_spc.cartGtm(e.detail.cart, 3);
  });

  document.addEventListener('orderPaymentMethod', function (e) {
    Drupal.alshaya_seo_spc.gtmPushCheckoutOption(e.detail.payment_method, 3);
  });

  Drupal.alshaya_seo_spc.gtmDeliveryMethod = function (method) {
    var data = {
      event: 'deliveryOption',
      eventLabel: method,
    };
    dataLayer.push(data);
  };

  /**
   * Helper function to push checkout option to GTM.
   *
   * @param optionLabel
   * @param step
   */
  Drupal.alshaya_seo_spc.gtmPushCheckoutOption = function (optionLabel, step) {
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

  Drupal.behaviors.spcCheckoutGtm = {
    attach: function (context, settings) {
      var cart_data = JSON.parse(localStorage.getItem('cart_data'));
      var step = Drupal.alshaya_seo_spc.getStepFromContainer();

      if (localStorage.hasOwnProperty('userID')) {
        if (step === 2) {
          Drupal.alshaya_seo_spc.gtmPushCheckoutOption('Home Delivery', 2);
        }
      }

      /**
       * Fire checkoutOption on cart page.
       */
      if (drupalSettings.user.uid !== 0) {
        Drupal.alshaya_seo_spc.gtmPushCheckoutOption('Logged In', 1);
      }
    }
  };

})(jQuery, Drupal, dataLayer);