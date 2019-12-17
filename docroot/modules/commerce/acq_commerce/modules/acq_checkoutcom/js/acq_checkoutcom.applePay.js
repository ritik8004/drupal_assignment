/**
 * @file
 * Attaches behaviors for the apple pay.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  var applePaySessionObject;

  function CheckoutComApplePay(settings, context){
    this.context = context;
    this.settings = settings;
    this.performValidation = function (valURL) {
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
    };

    this.sendChargeRequest = function (paymentData) {
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

      // Some features are not supported in some versions, this is browser
      // specific so done in JS.
      var version = Drupal.getApplePaySupportedVersion();

      // Mada is supported only from version 5 onwards.
      var networks = drupalSettings.checkoutCom.supportedNetworks.split(',');
      if (version < 5) {
        networks = networks.filter(function (element) {
          return element !== 'mada';
        });
      }

      // Prepare the parameters.
      var paymentRequest = {
        merchantIdentifier: drupalSettings.checkoutCom.merchantIdentifier,
        currencyCode: drupalSettings.checkoutCom.currencyCode,
        countryCode: drupalSettings.checkoutCom.countryId,
        total: {
          label: drupalSettings.checkoutCom.storeName,
          amount: drupalSettings.checkoutCom.runningTotal
        },
        supportedNetworks: networks,
        merchantCapabilities: drupalSettings.checkoutCom.merchantCapabilities.split(',')
      };

      // Start the payment session.
      try {
        applePaySessionObject = new ApplePaySession(1, paymentRequest);
      }
      catch(e) {
        Drupal.checkoutComShowGlobalError(Drupal.t('Sorry, we are unable to process your payment. Please contact our customer service team for assistance.'));
        $(document).trigger('apple_pay_cancel');
        return false;
      }

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
            // Hide the payment button now to avoid double click from user.
            $('#ckoApplePayButton').closest('form').find('.form-submit').hide();
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

  /**
   * Get supported version in specific device / browser.
   *
   * @returns {number}
   *   Supported version.
   *
   * @see https://developer.apple.com/documentation/apple_pay_on_the_web/applepaysession/1778014-supportsversion
   */
  Drupal.getApplePaySupportedVersion = function () {
    // When this code is written max version supported is 6. We would need
    // to test new versions whenever required to upgrade so not making it a
    // config. Same for minimum version.
    for (var i = 6; i > 1; i--) {
      if (ApplePaySession.supportsVersion(i)) {
        break;
      }
    }

    return i;
  };

  // Submit form on success.
  $.fn.checkoutPaymentSuccess = function () {
    // Begin session
    applePaySessionObject.begin();
  };

  try {
    // If config says we don't show apple pay on anywhere, don't do anything.
    if (window.ApplePaySession && drupalSettings.checkoutCom.applePayAllowedIn != 'none') {
      var isMobile = ('ontouchstart' in document.documentElement && navigator.userAgent.match(/Mobi/));

      // Show only in mobile if config says to show only in mobile.
      if (drupalSettings.checkoutCom.applePayAllowedIn == 'all' || isMobile) {
        // Show Apple pay at once, we will hide again quickly if something
        // goes wrong.
        $('#payment_method_checkout_com_applepay').addClass('supported');


        // Do next check only if user has selected apple pay.
        if ($('#ckoApplePayButton').length > 0) {
          let applePay = new CheckoutComApplePay(drupalSettings, $(document));
          launchApplePay(applePay);
        }
      }
    }
  }
  catch (e) {
    // Do nothing as something wrong in JS. We will simply not show apple pay.
  }

})(jQuery, Drupal, drupalSettings);
