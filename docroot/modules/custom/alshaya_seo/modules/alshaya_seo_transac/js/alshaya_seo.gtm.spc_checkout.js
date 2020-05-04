/**
 * @file
 * JS code to integrate with GTM.
 */

(function ($, Drupal, dataLayer) {
  'use strict';

  Drupal.alshayaSeoSpc = Drupal.alshayaSeoSpc || {};

  Drupal.alshayaSeoSpc.pushStoreData = function(cart) {
    if (cart.delivery_type === 'cnc' && !!cart.store_info) {
      dataLayer[0].deliveryOption = 'Click and Collect';
      dataLayer[0].deliveryType = 'ship_to_store';
      delete dataLayer[0].deliveryArea;
      delete dataLayer[0].deliveryCity;
      dataLayer[0].storeLocation = cart.store_info.name;
      dataLayer[0].storeAddress = cart.store_info.gtm_cart_address.address_line1 + ' ' +  cart.store_info.gtm_cart_address.administrative_area_display;
    }
  }

  Drupal.alshayaSeoSpc.pushHomeDeliveryData = function(cart) {
    if (cart.delivery_type === 'hd' && !!cart.shipping_methods && !!cart.shipping_address) {
      //Ref: \Drupal\alshaya_addressbook\AlshayaAddressBookManager::getAddressShippingAreaValue
      var area_id = cart.shipping_address[drupalSettings.address_fields.administrative_area.key];
      if (!area_id) {
        return;
      }

      dataLayer[0].deliveryOption = 'Home Delivery';
      dataLayer[0].deliveryType = cart.shipping_methods[0].carrier_title;
      delete dataLayer[0].storeLocation;
      delete dataLayer[0].storeAddress;
      var input = document.querySelector('[data-id="'+ area_id +'"]');
      dataLayer[0].deliveryArea = $(input).data('label');
      dataLayer[0].deliveryCity = $(input).data('parent-label');
    }
  }

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

  document.addEventListener('checkoutCartUpdate', function (e) {
    var step = Drupal.alshayaSeoSpc.getStepFromContainer();
    Drupal.alshayaSeoSpc.cartGtm(e.detail.cart, step);
    Drupal.alshayaSeoSpc.pushStoreData(e.detail.cart);
    Drupal.alshayaSeoSpc.pushHomeDeliveryData(e.detail.cart);
  });

  document.addEventListener('deliveryMethodChange', function (e) {
    var deliveryMethod = e.detail.data;
    Drupal.alshayaSeoSpc.gtmDeliveryMethod(deliveryMethod === 'hd' ? 'Home Delivery' : 'Click & Collect');
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

  document.addEventListener('storeSelected', function (e) {
    dataLayer.push({
      event: 'VirtualPageview',
      virtualPageURL: ' /virtualpv/click-and-collect/step2/select-store',
      virtualPageTitle: 'C&C Step 2 – Select Store'
    },
    {
      event: 'storeSelect',
      storeName: e.detail.store.name,
      storeAddress: e.detail.store.address.replace(/<[^>]+>(\s+)|(\n+)/g, ''),
    });
  });

  Drupal.behaviors.spcCheckoutGtm = {
    attach: function (context, settings) {
      var step = Drupal.alshayaSeoSpc.getStepFromContainer();
      var cart_data = Drupal.alshayaSpc.getCartData();
      $(document).once('spc-checkout-gtm-onetime').each(function() {
        if (cart_data !== null) {
          Drupal.alshayaSeoSpc.cartGtm(cart_data, step);
          if (step === 2) {
            Drupal.alshayaSeoSpc.gtmPushCheckoutOption(
              cart_data.cart.delivery_type === 'hd' ? 'Home Delivery' : 'Click & Collect',
              step
            );
          }
        }

        if (drupalSettings.user.uid !== 0) {
          Drupal.alshayaSeoSpc.gtmPushCheckoutOption('Logged In', 1);
        }
      });
    }
  };

})(jQuery, Drupal, dataLayer);
