/**
 * @file
 * JS code to integrate with GTM.
 */

(function ($, Drupal, dataLayer) {

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

  Drupal.alshayaSeoSpc.checkoutEvent = function (cartData, step, paymentMethod = '') {
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

    // 1st condition is triggered when we refresh checkout page with payment
    // method selected. Next conditions are specifically for step 3 and 4.
    if ((cartData.payment.method && step === 2) || step === 3 || step === 4) {
      var stepData = JSON.parse(JSON.stringify(data));
      stepData.ecommerce.checkout.actionField.step = (step === 4) ? 4 : 3;
      if (step === 4) {
        var totals = window.spcStaticStorage.cart_raw.totals;
        var auraPaymentAmount = totals.total_segments ? totals.total_segments.filter(item => item.code === 'aura_payment') : null;
        auraPaymentAmount = typeof auraPaymentAmount[0] !== 'undefined' ? auraPaymentAmount[0].value : null;
        var gtmPaymentName = drupalSettings.payment_methods[cartData.payment.method] ?
          drupalSettings.payment_methods[cartData.payment.method].gtm_name
          : 'hps_payment';
        // When full payment is done using pseudo methods.
        if (paymentMethod === 'hps_payment') {
          if (totals && totals.extension_attributes.hps_redeemed_amount > 0) {
            // For egift payment only.
            stepData.paymentOption = 'egiftcard';
            // For egift + aura payment.
            if (auraPaymentAmount > 0) {
              stepData.paymentOption = [stepData.paymentOption, 'aura'].join('_');
            }
          }
          // When full payment is done using aura.
          else if (auraPaymentAmount > 0) {
            stepData.paymentOption = 'aura';
          }
        }
        // When combination of payments involved.
        else if (totals && totals.extension_attributes.hps_redeemed_amount > 0) {
          // When egift + other payment method.
          stepData.paymentOption = [gtmPaymentName, 'egiftcard'].join('_');
          // When aura + egift + other payment method.
          if (auraPaymentAmount > 0) {
            stepData.paymentOption = [stepData.paymentOption, 'aura'].join('_');
          }
        }
        // When aura + other payment method.
        else if (auraPaymentAmount > 0) {
          stepData.paymentOption = [gtmPaymentName, 'aura'].join('_');
        }
        // When completely non-pseudo payment used.
        else {
          stepData.paymentOption = drupalSettings.payment_methods[cartData.payment.method] ?
            drupalSettings.payment_methods[cartData.payment.method].gtm_name
            : '';
        }
      }
      stepData.pageType = checkoutPaymentPage;
      dataLayer.push(stepData);
    }
  };

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
    var payment_method = e.detail.payment_method;
    // Get the cart data to check if some payment is done via E-Gift card.
    if (drupalSettings.hasOwnProperty('egiftCard') && drupalSettings.egiftCard.enabled) {
      var totals = window.spcStaticStorage.cart_raw.totals;
      // If some amount is paid via egift then hps_redeemed_amount will be
      // present.
      if (totals
        && totals.extension_attributes.hps_redeemed_amount > 0
        && payment_method != 'hps_payment') {
        payment_method = [payment_method, 'egiftcard'].join('_');
      }
    }
    // Change hps_payment to egift_card.
    if (payment_method == 'hps_payment') {
      payment_method = 'egiftcard';
    }
    Drupal.alshayaSeoSpc.gtmPushCheckoutOption(payment_method, 3);
  });

  // Add checkout event step 4 for the click on complete purchase button.
  document.addEventListener('orderValidated', function (e) {
    if (e.detail.cart) {
      var paymentMethod = e.detail.cartPaymentMethod ? e.detail.cartPaymentMethod : '';
      Drupal.alshayaSeoSpc.checkoutEvent(e.detail.cart, 4, paymentMethod);
    }
  });

  document.addEventListener('egiftCardRedeemed', function (e) {
    dataLayer.push({
      event: 'egift_card',
      eventCategory: 'egift_card',
      eventAction: `egift_${e.detail.action}`,
      eventLabel: e.detail.label,
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
