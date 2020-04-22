/**
 * @file
 * JS code to integrate with GTM.
 */

(function ($, Drupal, dataLayer) {
  'use strict';

  document.addEventListener('checkoutCartUpdate', function (e) {
    var step = Drupal.alshaya_seo_spc.getStepFromContainer();
    Drupal.alshaya_seo_spc.cartGtm(e.detail.cart, step);
    Drupal.alshaya_seo_spc.pushStoreData(e.detail.cart);
    Drupal.alshaya_seo_spc.pushHddata(e.detail.cart);
  });

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
    Drupal.alshaya_seo_spc.pushStoreData(cart_data.cart);
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
    Drupal.alshaya_seo_spc.pushHddata(e.detail.cart);
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

  Drupal.alshaya_seo_spc.pushStoreData = function(cart) {
    if (cart.delivery_type !== 'cnc' || !cart.store_info) {
      return;
    }

    dataLayer[0].deliveryOption = 'Click and Collect';
    dataLayer[0].deliveryType = 'ship_to_store';
    dataLayer[0].storeLocation = cart.store_info.name;
    dataLayer[0].storeAddress = cart.store_info.gtm_cart_address.address_line1 + ' ' +  cart.store_info.gtm_cart_address.administrative_area_display;
  }

  Drupal.alshaya_seo_spc.pushHddata = function(cart) {
    if (cart.delivery_type !== 'hd' || !cart.shipping_methods) {
      return;
    }
    //Ref: \Drupal\alshaya_addressbook\AlshayaAddressBookManager::getAddressShippingAreaValue
    var area_id = cart.shipping_address[drupalSettings.address_fields.administrative_area.key];
    if (!area_id) {
      return;
    }

    dataLayer[0].deliveryOption = 'Home Delivery';
    dataLayer[0].deliveryType = cart.shipping_methods[0].carrier_title;

    var input = document.querySelector('[data-id="'+ area_id +'"]');
    dataLayer[0].deliveryArea = $(input).data('label');
    dataLayer[0].deliveryCity = $(input).data('parent-label');
  }

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
