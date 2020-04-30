/**
 * @file
 * JS code to integrate with GTM.
 */

(function ($, Drupal, dataLayer) {
  'use strict';

  document.addEventListener('checkoutCartUpdate', function (e) {
    var step = Drupal.alshayaSeoSpc.getStepFromContainer();
    Drupal.alshayaSeoSpc.cartGtm(e.detail.cart, step);
    Drupal.alshayaSeoSpc.pushStoreData(e.detail.cart);
    Drupal.alshayaSeoSpc.pushHomeDeliveryData(e.detail.cart);

    if (drupalSettings.user.uid !== 0) {
      Drupal.alshayaSeoSpc.gtmPushCheckoutOption('Logged In', 1);
    }
  });

  document.addEventListener('deliveryMethodChange', function (e) {
    var deliveryMethod = e.detail.data;
    Drupal.alshayaSeoSpc.gtmDeliveryMethod(deliveryMethod === 'home_delivery' ? 'Home Delivery' : 'Click & Collect');
  });

  document.addEventListener('refreshCartOnCnCSelect', function (e) {
    var cart_data = e.detail;
    Drupal.alshayaSeoSpc.pushStoreData(cart_data.cart);
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
    Drupal.alshayaSeoSpc.pushHomeDeliveryData(e.detail.cart);
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
    Drupal.alshayaSeoSpc.gtmPushCheckoutOption(deliveryType, 2);
  });

  document.addEventListener('refreshCartOnPaymentMethod', function (e) {
    Drupal.alshayaSeoSpc.cartGtm(e.detail.cart.cart, 3);
  });

  document.addEventListener('orderPaymentMethod', function (e) {
    Drupal.alshayaSeoSpc.gtmPushCheckoutOption(e.detail.payment_method, 3);
  });

  Drupal.alshayaSeoSpc.pushStoreData = function(cart) {
    if (cart.delivery_type !== 'click_and_collect' || !cart.shipping.storeInfo) {
      return;
    }

    dataLayer[0].deliveryOption = 'Click and Collect';
    dataLayer[0].deliveryType = 'ship_to_store';
    dataLayer[0].storeLocation = cart.shipping.storeInfo.name;
    dataLayer[0].storeAddress = cart.shipping.storeInfo.gtm_cart_address.address_line1 + ' ' +  cart.shipping.storeInfo.gtm_cart_address.administrative_area_display;
  };

  Drupal.alshayaSeoSpc.pushHomeDeliveryData = function(cart) {
    if (cart.delivery_type !== 'home_delivery' || !cart.shipping.methods || !cart.shipping.address) {
      return;
    }
    //Ref: \Drupal\alshaya_addressbook\AlshayaAddressBookManager::getAddressShippingAreaValue
    var area_id = cart.shipping.address[drupalSettings.address_fields.administrative_area.key];
    if (!area_id) {
      return;
    }

    dataLayer[0].deliveryOption = 'Home Delivery';
    dataLayer[0].deliveryType = cart.shipping.methods[0].carrier_title;

    var input = document.querySelector('[data-id="'+ area_id +'"]');
    dataLayer[0].deliveryArea = $(input).data('label');
    dataLayer[0].deliveryCity = $(input).data('parent-label');
  };

  Drupal.alshayaSeoSpc.gtmDeliveryMethod = function (method) {
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
  Drupal.alshayaSeoSpc.gtmPushCheckoutOption = function (optionLabel, step) {
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

})(jQuery, Drupal, dataLayer);
