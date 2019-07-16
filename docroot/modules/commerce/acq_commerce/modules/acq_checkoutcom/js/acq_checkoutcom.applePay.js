/**
 * @file
 * Attaches behaviors for the apple pay.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  function launchApplePay(context, settings) {

    var button_target = '#ckoApplePayButton';

    // Apply the button style.
    $(button_target, context).addClass('apple-pay-button-black');

    // Check if the session is available.
    if (window.ApplePaySession) {
      var merchantIdentifier = 'merchant.com.alshaya.checkout.demo';
      var promise = ApplePaySession.canMakePaymentsWithActiveCard(merchantIdentifier);
      promise.then(function (canMakePayments) {
        if (canMakePayments) {
          $(button_target, context).css('display', 'block');
        }
        else {
          $('#got_notactive', context).css('display', 'block');
        }
      });
    }
    else {
      $('#notgot', context).css('display', 'block');
    }

  }

  Drupal.behaviors.acqCheckoutComApplePay = {
    attach: function (context, settings) {
      launchApplePay(context, settings);
    }
  };

})(jQuery, Drupal, drupalSettings);
