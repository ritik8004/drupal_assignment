/**
 * @file
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.AlshayaCheckoutCom = {
    attach: function (context, settings) {

      // In order to show the form between radio buttons we do it
      // using custom markup. Here we update the radio buttons on
      // click of payment method names in custom markup.
      $('#payment_details_checkout_com', context).once('bind-events').each(function () {
        $('.payment-card-wrapper-div:not(.expired)', $(this)).on('click', function () {
          var selected_option = $(this).data('value');
          // Check if this payment method is already active, if yes return.
          // We don't want to remove payment_details in this case else active payment form is lost.
          if ($(this).hasClass('card-selected')) {
            return false;
          }
          $(this).parent().siblings().find('.card-selected').removeClass('card-selected');
          $(this).addClass('card-selected');
          $('[data-drupal-selector="edit-acm-payment-methods-payment-details-wrapper-payment-method-checkout-com"]').find('input[value="' + selected_option + '"]').trigger('click');
          $(this).showCheckoutLoader();
        });
      });

      // Bind this only once after every ajax call.
      $('.checkoutcom-credit-card-input')
        .once('validate-cc')
        .each(function () {
          $(this).validateCreditCard(function (result) {
            $('#payment_method_checkout_com').find('.card-type').removeClass('active');

            if (result.card_type !== null) {
              $('#payment_method_checkout_com').find('.card-type.card-type-' + result.card_type.name).addClass('active');
            }
          });
        });
    }
  };

  if (typeof CheckoutKit !== 'undefined') {
    // Handle api error which triggered on card tokenisation fail.
    CheckoutKit.addEventHandler(CheckoutKit.Events.API_ERROR, function(event) {
      $(this).removeCheckoutLoader();
    });
  }

  // Display loader while payment form is submitted.
  $(document).on('checkoutcom_form_validated', function(e) {
    $(this).showCheckoutLoader();
  });

  // Remove loader to allow user to edit form on form error.
  $(document).on('checkoutcom_form_error', function (e) {
    $(this).removeCheckoutLoader();
    Drupal.setFocusToFirstError($('form.multistep-checkout'));
  });

  $(document).on('checkoutcom_form_ajax', function(e, response) {
    // Remove ajax loader when the ajax call does not contain
    // checkoutPaymentSuccess, that means there are some form errors and
    // can not continue with place order.
    var checkFinalCall = _.where(response, {method: 'checkoutPaymentSuccess'});
    if (checkFinalCall.length < 1) {
      $(this).removeCheckoutLoader();
    }
  });

})(jQuery, Drupal, drupalSettings);
