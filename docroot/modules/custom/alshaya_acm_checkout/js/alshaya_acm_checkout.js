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
    }
  };

})(jQuery, Drupal);
