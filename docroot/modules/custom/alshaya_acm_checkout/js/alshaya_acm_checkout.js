(function ($, Drupal) {
  'use strict';

  /**
   * All custom js for checkout flow.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   All custom js for checkout flow.
   */
  Drupal.behaviors.ZZAlshayaAcmCheckout = {
    attach: function (context, settings) {
      // Bind this only once after every ajax call.
      $('[data-drupal-selector="edit-payment-methods-payment-details-cc-number"]').once('validate-cc').each(function () {
        $(this).validateCreditCard(function (result) {
          // Reset error and card type active class.
          $(this).parent().removeClass('cc-error');
          $('.card-type').removeClass('active');

          // Don't do anything if card_type is null.
          if (result.card_type !== null) {
            switch (result.card_type.name) {
              case 'diners_club_carte_blanche':
              case 'diners_club_international':
                $('.card-type-diners-club').addClass('active');
                break;
              case 'visa':
              case 'mastercard':
              default:
                $('.card-type-' + result.card_type.name).addClass('active');
                break;
            }

            // Set error class on wrapper if invalid card number.
            if (!result.valid || !result.length_valid || !result.luhn_valid) {
              $(this).parent().addClass('cc-error');
            }
          }
        });
      });

      // Show/hide fields based on availability of shipping methods.
      if ($('#shipping_methods_wrapper').length) {
        if ($('#shipping_methods_wrapper input:radio').length > 0) {
          $('#shipping_methods_wrapper fieldset').show();
          $('[data-drupal-selector="edit-actions-get-shipping-methods"]').hide();
          $('[data-drupal-selector="edit-actions-next"]').show();

          // Select the first method by default.
          if ($('#shipping_methods_wrapper input[type="radio"]:checked').length === 0) {
            $('#shipping_methods_wrapper input[type="radio"]:first').trigger('click');
          }
        }
        else {
          $('#shipping_methods_wrapper fieldset').hide();
          $('[data-drupal-selector="edit-actions-get-shipping-methods"]').show();
          $('[data-drupal-selector="edit-actions-next"]').hide();
        }
      }

      $('#change-address').once('bind-events').each(function () {
        $('#add-address-button').hide();
        $('#edit-member-delivery-home-addresses').hide();
        $('#edit-member-delivery-home-header-add-profile').hide();

        $(this).on('click', function (e) {
          e.preventDefault();

          $('#add-address-button').show();
          $('#selected-address-wrapper').slideUp();
          $('[data-drupal-selector="edit-actions-next"]').hide();
          $('#shipping_methods_wrapper').slideUp();
          $('#edit-member-delivery-home-addresses').slideDown();
          $('.delivery-address-title').html(Drupal.t('choose delivery address'));
          $('#edit-member-delivery-home-header-add-profile').show();
        });
      });

      $('#address-book-form-wrapper').once('bind-events').each(function () {
        $(this).hide();

        $('#add-address-button').on('click', function (event) {
          event.preventDefault();
          $('#address-book-form-wrapper').slideDown();
        });

        $('#cancel-address-add-edit').on('click', function (event) {
          event.preventDefault();
          $('#address-book-form-wrapper').slideUp();
        });
      });

      if (typeof Drupal.Ajax !== 'undefined' && typeof Drupal.Ajax.prototype.beforeSendAcmCheckout === 'undefined') {
        Drupal.Ajax.prototype.beforeSendAcmCheckout = Drupal.Ajax.prototype.beforeSend;
        Drupal.Ajax.prototype.successAcmCheckout = Drupal.Ajax.prototype.success;

        // See docroot/core/misc/ajax.js > Drupal.Ajax.prototype.beforeSend()
        Drupal.Ajax.prototype.beforeSend = function (xmlhttprequest, options) {
          // Invoke the original function.
          this.beforeSendAcmCheckout(xmlhttprequest, options);

          // Disable submit button.
          $('#edit-actions .form-submit').each(function () {
            if ($(this).prop('disabled') === false) {
              $(this).addClass('acm-checkout-ajax-disabled');
              $(this).prop('disabled', true);
            }
          });
        };

        // See docroot/core/misc/ajax.js > Drupal.Ajax.prototype.success()
        Drupal.Ajax.prototype.success = function (response, status) {
          // Invoke the original function.
          this.successAcmCheckout(response, status);

          // Disable submit button.
          $('.acm-checkout-ajax-disabled').each(function () {
            $(this).removeClass('acm-checkout-ajax-disabled');
            $(this).prop('disabled', false);
          });
        };
      }
    }
  };

})(jQuery, Drupal);
