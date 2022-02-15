/**
 * @file
 * JS code to integrate with GTM.
 */

(function ($, Drupal, dataLayer) {
  'use strict';

  Drupal.alshayaSeoSpc = Drupal.alshayaSeoSpc || {};

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

  /**
   * Helper function to check if cart contains only virtual products.
   *
   * @param {object} cartData
   *   The cart data object.
   */
  Drupal.alshayaSeoSpc.cartContainsOnlyVirtualProducts = function (cartData) {
    // Flag to check if all products are virtual.
    let allVirtual = true;
    Object.keys(cartData.items).forEach((key) => {
      if (!(cartData.items[key]['isEgiftCard'] || cartData.items[key]['isTopUp'])) {
        allVirtual = false;
      }
    });

    return allVirtual;
  };

  Drupal.alshayaSeoSpc.pushStoreData = function (cart) {
    if (cart.shipping.type !== 'click_and_collect' || !cart.shipping.storeInfo) {
      return;
    }

    return {
      deliveryOption: 'Click and Collect',
      deliveryType: 'ship_to_store',
      storeLocation: cart.shipping.storeInfo.name,
      storeAddress: cart.shipping.storeInfo.gtm_cart_address.address_line1 + ' ' + cart.shipping.storeInfo.gtm_cart_address.administrative_area_display,
    };
  };

  Drupal.alshayaSeoSpc.pushHomeDeliveryData = function (cart) {
    if (cart.shipping.type !== 'home_delivery' || !cart.shipping.methods || !cart.shipping.address) {
      return;
    }

    //Ref: \Drupal\alshaya_addressbook\AlshayaAddressBookManager::getAddressShippingAreaValue
    var area_id = cart.shipping.address[drupalSettings.address_fields.administrative_area.key];
    if (!area_id) {
      return;
    }

    var input = document.querySelector('[data-id="' + area_id + '"]');
    return {
      deliveryOption: 'Home Delivery',
      deliveryType: cart.shipping.methods[0].carrier_title,
      deliveryArea: $(input).data('label'),
      deliveryCity: $(input).data('parent-label'),
    };
  };

  Drupal.alshayaSeoSpc.gtmDeliveryMethod = function (method) {
    var data = {
      event: 'deliveryOption',
      eventLabel: method,
    };
    dataLayer.push(data);
  };

  Drupal.alshayaSeoSpc.checkoutEvent = function (cartData, step) {
    var checkoutPaymentPage = 'checkout payment page';
    var data = {
      language: drupalSettings.gtm.language,
      country: drupalSettings.gtm.country,
      currency: drupalSettings.gtm.currency,
      pageType: step === 3 ? checkoutPaymentPage : drupalSettings.gtm.pageType,
      event: 'checkout',
      ecommerce: {
        currencyCode: drupalSettings.gtm.currency,
        checkout: {
        },
      },
    };
    var storeData = Drupal.alshayaSeoSpc.pushStoreData(cartData);
    if (storeData) {
      Object.assign(data, storeData);
    }

    var homeDeliveryData = Drupal.alshayaSeoSpc.pushHomeDeliveryData(cartData);
    if (homeDeliveryData) {
      Object.assign(data, homeDeliveryData);
    }
    var additionalCartData = Drupal.alshayaSeoSpc.cartGtm(cartData, step);
    Object.assign(data.ecommerce.checkout, additionalCartData.checkout);
    delete additionalCartData.checkout;
    Object.assign(data, additionalCartData);
    if (step === 2) {
      dataLayer.push(data);

      // Trigger checkoutOption event for step 2 when delivery info is available.
      if (data.deliveryOption) {
        Drupal.alshayaSeoSpc.gtmPushCheckoutOption(
          data.deliveryOption === 'Home Delivery' ? 'Home Delivery' : 'Click & Collect',
          2
        );
      } else if (Drupal.alshayaSeoSpc.cartContainsOnlyVirtualProducts(cartData)) {
        Drupal.alshayaSeoSpc.gtmPushCheckoutOption('Virtual eGift Card', 2);
      }
    }

    // Trigger checkout event for step 3 when payment method is available.
    if (cartData.payment.method || step === 3) {
      var step3_data = JSON.parse(JSON.stringify(data));
      step3_data.ecommerce.checkout.actionField.step = 3;
      step3_data.pageType = checkoutPaymentPage;
      dataLayer.push(step3_data);
    }
  }

  document.addEventListener('checkoutCartUpdate', function (e) {
    var step = Drupal.alshayaSeoSpc.getStepFromContainer();
    Drupal.alshayaSeoSpc.checkoutEvent(e.detail.cart, step);
  });

  document.addEventListener('deliveryMethodChange', function (e) {
    var deliveryMethod = e.detail.data;
    Drupal.alshayaSeoSpc.gtmDeliveryMethod(deliveryMethod === 'home_delivery' ? 'Home Delivery' : 'Click & Collect');
  });

  document.addEventListener('refreshCartOnCnCSelect', function (e) {
    Drupal.alshayaSeoSpc.gtmPushCheckoutOption('Click & Collect', 2);
  });

  document.addEventListener('refreshCartOnAddress', function (e) {
    Drupal.alshayaSeoSpc.gtmPushCheckoutOption('Home Delivery', 2);
  });

  document.addEventListener('changeShippingMethod', function (e) {
    var deliveryType = e.detail.data.carrier_title;
    Drupal.alshayaSeoSpc.gtmPushCheckoutOption(deliveryType, 2);
  });

  document.addEventListener('refreshCartOnPaymentMethod', function (e) {
    // Clone "checkout" datalayer event to trigger it again for payment.
    Drupal.alshayaSeoSpc.checkoutEvent(e.detail.cart, 3);
  });

  document.addEventListener('orderPaymentMethod', function (e) {
    Drupal.alshayaSeoSpc.gtmPushCheckoutOption(e.detail.payment_method, 3);
  });

  document.addEventListener('egiftLinkedCardRedeemed', function (e) {
    dataLayer.push({
      event: 'Pay using eGift Card',
      status: e.detail.status,
    });
  });

  document.addEventListener('storeSelected', function (e) {
    if (e.detail.store === undefined || e.detail.store.name === undefined) {
      // Return if data not proper.
      // We have added logs in the place from which event is dispatched.
      return;
    }

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
      $('body').once('spc-checkout-gtm-onetime').each(function () {
        if (settings.user.uid > 0) {
          Drupal.alshayaSeoSpc.gtmPushCheckoutOption('Logged In', 1);
        }
      });
    }
  };

})(jQuery, Drupal, dataLayer);
