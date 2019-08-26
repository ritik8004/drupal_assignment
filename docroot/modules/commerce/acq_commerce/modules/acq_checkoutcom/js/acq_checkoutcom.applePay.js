/**
 * @file
 * Attaches behaviors for the apple pay.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  var applePaySessionObject;

  class CheckoutComApplePay {
    constructor(settings, context) {
      this.context = context;
      this.settings = settings;
    }

    performValidation (valURL) {
      var controllerUrl = Drupal.url('checkoutcom/applepay/validate');
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
          url: Drupal.url('checkoutcom/applepay/save-payment'),
          type: 'POST',
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
      var promise = ApplePaySession.canMakePaymentsWithActiveCard(drupalSettings.checkoutCom.merchantIdentifier);
      promise.then(function (canMakePayments) {
        if (canMakePayments) {
          $(button_target, checkoutApplePay.context).css('display', 'block');
        }
      });
    }

    // Handle the events.
    $(button_target, checkoutApplePay.context).once('bind-js').on('click', function (event) {
      event.preventDefault();
      event.stopPropagation();

      Drupal.checkoutComProcessed = false;
      Drupal.checkoutComValidateBeforeCheckout($(this).closest('form'));

      // Prepare the parameters.
      var paymentRequest = {
        merchantIdentifier: drupalSettings.checkoutCom.merchantIdentifier,
        currencyCode: drupalSettings.checkoutCom.currencyCode,
        countryCode: drupalSettings.checkoutCom.countryId,
        total: {
          label: drupalSettings.checkoutCom.storeName,
          amount: drupalSettings.checkoutCom.runningTotal
        },
        supportedNetworks: drupalSettings.checkoutCom.supportedNetworks.split(','),
        merchantCapabilities: drupalSettings.checkoutCom.merchantCapabilities.split(','),
        supportedCountries: drupalSettings.checkoutCom.supportedCountries.split(',')
      };

      // Start the payment session.
      applePaySessionObject = new ApplePaySession(1, paymentRequest);

      // Merchant Validation.
      applePaySessionObject.onvalidatemerchant = function (event) {
        var promise = checkoutApplePay.performValidation(event.validationURL);
        promise.then(function (merchantSession) {
          applePaySessionObject.completeMerchantValidation(merchantSession);
        });
      };

      // Payment method authorization
      applePaySessionObject.onpaymentauthorized = function (event) {
        var promise = checkoutApplePay.sendChargeRequest(event.payment.token);
        promise.then(function (success) {
          var status;
          if (success) {
            status = ApplePaySession.STATUS_SUCCESS;
          } else {
            status = ApplePaySession.STATUS_FAILURE;
          }

          applePaySessionObject.completePayment(status);

          if (success) {
            $('#ckoApplePayButton').closest('form').submit();
          }
          else {
            $(document).trigger('apple_pay_authorisation_fail');
          }
        });
      };

      // Session cancellation
      applePaySessionObject.oncancel = function(event) {
        $(document).trigger('apple_pay_cancel');
      };

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
    }
  };

  // Submit form on success.
  $.fn.checkoutPaymentSuccess = function () {
    // Begin session
    applePaySessionObject.begin();
  };

})(jQuery, Drupal, drupalSettings);
