/**
 * @file
 * Attaches behaviors for the apple pay.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  class CheckoutComApplePay {
    constructor(settings, context) {
      this.context = context;
      this.settings = settings;
    }

    cartData () {
      return {
        storeName: 'Mothercare Kuwait',
        currencyCode: 'KWD',
        billingAddress: {
          countryId: 'KW',
        },
        runningTotal: 10000,
      };
    }

    performValidation (valURL) {
      var controllerUrl = Drupal.url('checkout_com/payment/applepayvalidation');
      var validationUrl = controllerUrl + '?u=' + valURL;

      return new Promise(function(resolve, reject) {
        var xhr = new XMLHttpRequest();
        xhr.onload = function() {
          var data = JSON.parse(this.responseText);
          resolve(data);
        };
        xhr.onerror = reject;
        xhr.open('GET', validationUrl);
        xhr.send();
      });
    }

    sendChargeRequest (paymentData) {
      return new Promise(function(resolve, reject) {
        $.ajax({
          url: Drupal.url('checkout_com/payment/applepayplaceorder'),
          type: "POST",
          data: paymentData,
          success: function(data, textStatus, xhr) {
            if (data.status === true) {
              resolve(data.status);
            }
            else {
              reject;
            }
          },
          error: function(xhr, textStatus, error) {
            reject;
          }
        });
      });
    }

    getLineItems () {
      return [];
    };
  }

  function launchApplePay(checkoutApplePay) {

    var button_target = '#ckoApplePayButton';

    // Apply the button style.
    $(button_target, checkoutApplePay.context).addClass('apple-pay-button-black');

    // Check if the session is available.
    if (window.ApplePaySession) {
      var merchantIdentifier = 'merchant.com.alshaya.checkout.demo';
      var promise = ApplePaySession.canMakePaymentsWithActiveCard(merchantIdentifier);
      promise.then(function (canMakePayments) {
        if (canMakePayments) {
          $(button_target, checkoutApplePay.context).css('display', 'block');
        }
        else {
          $('#got_notactive', checkoutApplePay.context).css('display', 'block');
        }
      });
    }
    else {
      $('#notgot', checkoutApplePay.context).css('display', 'block');
    }

    // Handle the events.
    $(button_target, checkoutApplePay.context).click(function (event) {
      event.preventDefault();
      event.stopPropagation();
      // Prepare the parameters.
      var cartData = checkoutApplePay.cartData();
      var paymentRequest = {
        merchantIdentifier: 'merchant.com.alshaya.checkout.demo',
        version: 6,
        currencyCode: cartData.currencyCode,
        countryCode: cartData.billingAddress.countryId,
        total: {
          label: cartData.storeName,
          amount: cartData.runningTotal
        },
        supportedNetworks: ['visa', 'masterCard', 'amex'],
        merchantCapabilities: ['supports3DS'],
        supportedCountries: ['KW']
      };

      // Start the payment session.
      var session = new ApplePaySession(6, paymentRequest);

      // Merchant Validation.
      session.onvalidatemerchant = function (event) {
        console.log(event);
        var promise = checkoutApplePay.performValidation(event.validationURL);
        promise.then(function (merchantSession) {
          session.completeMerchantValidation(merchantSession);
        });
      };

      // Shipping contact.
      session.onshippingcontactselected = function(event) {
        console.log(event);
        var status = ApplePaySession.STATUS_SUCCESS;

        // Shipping info.
        var shippingOptions = [];

        var newTotal = {
          type: 'final',
          label: cartData.storeName,
          amount: cartData.runningTotal
        };

        session.completeShippingContactSelection(status, shippingOptions, newTotal, checkoutApplePay.getLineItems());
      };

      // Shipping method selection.
      session.onshippingmethodselected = function(event) {
        console.log(event);
        var status = ApplePaySession.STATUS_SUCCESS;
        var newTotal = {
          type: 'final',
          label: cartData.storeName,
          amount: cartData.runningTotal
        };

        session.completeShippingMethodSelection(status, newTotal, checkoutApplePay.getLineItems());
      };

      // Payment method selection
      session.onpaymentmethodselected = function(event) {
        console.log(event);
        var newTotal = {
          type: 'final',
          label: cartData.storeName,
          amount: cartData.runningTotal
        };

        session.completePaymentMethodSelection(newTotal, checkoutApplePay.getLineItems());
      };

      // Payment method authorization
      session.onpaymentauthorized = function (event) {
        console.log(event);
        var promise = checkoutApplePay.sendChargeRequest(event.payment.token);
        promise.then(function (success) {
          var status;
          if (success) {
            status = ApplePaySession.STATUS_SUCCESS;
          } else {
            status = ApplePaySession.STATUS_FAILURE;
          }

          session.completePayment(status);

          if (success) {
            // @todo: Handle success part.
            // redirect to success page
          }
        });
      };

      // Session cancellation
      session.oncancel = function(event) {
        console.log(event);
      };

      // Begin session
      session.begin();
      return false;
    });
  }

  Drupal.behaviors.acqCheckoutComApplePay = {
    attach: function (context, settings) {
      // Proceed if ApplePay is supported.
      if (window.ApplePaySession) {
        let applePay = new CheckoutComApplePay(settings, context);
        $('#payment_method_checkout_com_applepay', context).addClass('supported');
        launchApplePay(applePay);
      }
      else {
        // Handle in case apple pay is last payment option, we need someone
        // to take place of last child for maintaining FE design.
        if ($('#payment_method_checkout_com_applepay', context).is(':last-child')) {
          $('#payment_method_checkout_com_applepay', context).prev().addClass('pseudo-last-child')
        }
        return false;
      }
    }
  };

})(jQuery, Drupal, drupalSettings);
