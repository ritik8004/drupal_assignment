/**
 * @file
 * Format Input.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.formatInput = {
    attach: function (context, settings) {

      var defaultValue = settings.alshaya_loyalty.card_validate.init_value;
      var privilegeCard = $('.c-input__privilege-card');
      privilegeCard.toArray().forEach(function (field) {
        new Cleave(field, {
          prefix: defaultValue,
          blocks: [4, 4, 4, 4],
          delimiter: '-',
          numericOnly: true
        });
      });

      // Display ajax loader for checkout button when privilege card number validation ajax triggers.
      var priv_card = $('input[name="privilege_card_number"]');
      var priv_card2 = $('input[name="privilege_card_number2"]');

      priv_card.on('keypress', validate_privilege_card);
      priv_card.on('keyup', validate_privilege_card);
      priv_card.on('input', validate_privilege_card);
      priv_card.on('paste', validate_privilege_card);

      priv_card2.on('keypress', validate_privilege_card);
      priv_card2.on('keyup', validate_privilege_card);
      priv_card2.on('input', validate_privilege_card);
      priv_card2.on('paste', validate_privilege_card);

      $('.checkout-top-button').attr( 'data-style', 'zoom-in');
      $('#secure-checkout-button > .form-submit').attr( 'data-style', 'zoom-in');
      var l2 = $('#secure-checkout-button > .form-submit').ladda();
      var l = $('.checkout-top-button').ladda();

      function validate_privilege_card() {
        if (priv_card.val().length == 19 && priv_card.val() == priv_card2.val() && priv_card.val().length == priv_card2.val().length) {
          priv_card2.blur();
          l2.ladda('start');
          l.ladda('start');
        }
      }

      $(document).ajaxComplete(function(event, xhr, settings) {
        if ((settings.hasOwnProperty('extraData')) && settings.extraData._triggering_element_name == 'privilege_card_number2') {
          $.ladda('stopAll');
        }
      });

    }
  };
})(jQuery, Drupal);
