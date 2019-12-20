/**
 * @file
 * Format Input.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.privilege_card_number = {
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
      var priv_card = $('input[name="privilege_card_number"]', context);
      var priv_card2 = $('input[name="privilege_card_number2"]', context);

      priv_card.on('keypress', validate_privilege_card);
      priv_card.on('keyup', validate_privilege_card);
      priv_card.on('input', validate_privilege_card);
      priv_card.on('paste', validate_privilege_card);

      priv_card2.on('keypress', validate_privilege_card);
      priv_card2.on('keyup', validate_privilege_card);
      priv_card2.on('input', validate_privilege_card);
      priv_card2.on('paste', validate_privilege_card);

      // Show second privilege card number field on change of first field.
      if (context === document) {
        $('#details-privilege-card-wrapper .form-item-privilege-card-number2').hide();
      }

      priv_card.on('change input keypress', function () {
        priv_card2.parent().show();
      });

      $('.checkout-top-button').attr('data-style', 'zoom-in');
      $('#secure-checkout-button > .form-submit').attr('data-style', 'zoom-in');
      var l2 = $('#secure-checkout-button > .form-submit').ladda();
      var l = $('.checkout-top-button').ladda();

      function validate_privilege_card() {
        if (priv_card.val().length == 19 && priv_card.val() == priv_card2.val() && priv_card.val().length == priv_card2.val().length) {
          priv_card2.blur();
        }
      }

      if (typeof Drupal.Ajax !== 'undefined' && typeof Drupal.Ajax.prototype.PrivilegeCardBeforeSend === 'undefined') {
        Drupal.Ajax.prototype.PrivilegeCardBeforeSend = Drupal.Ajax.prototype.beforeSend;
        Drupal.Ajax.prototype.PrivilegeCardsuccess = Drupal.Ajax.prototype.success;
        Drupal.Ajax.prototype.PrivilegeCarderror = Drupal.Ajax.prototype.error;

        // See docroot/core/misc/ajax.js > Drupal.Ajax.prototype.beforeSend()
        Drupal.Ajax.prototype.beforeSend = function (xmlhttprequest, options) {
          if (this.element.name == 'privilege_card_number2') {
            l2.ladda('start');
            l.ladda('start');
          }
          // Invoke the original function.
          this.PrivilegeCardBeforeSend(xmlhttprequest, options);
        };

        Drupal.Ajax.prototype.error = function (xmlhttprequest, uri, customMessage) {
          $(this).cartladdastop(this.element);
          // Invoke the original function.
          this.PrivilegeCarderror(xmlhttprequest, uri, customMessage);
          $('.alias--cart #details-privilege-card-wrapper').once('privilege-card').each(function () {
            $(this).accordion({
              header: '.card__header',
              collapsible: true
            });
          });
        };

        Drupal.Ajax.prototype.success = function (response, status) {
          $(this).cartladdastop(this.element);
          // Invoke the original function.
          this.PrivilegeCardsuccess(response, status);

          $('.coupon-code-wrapper, .alias--cart #details-privilege-card-wrapper').once('privilege-card').each(function () {
            $(this).accordion({
              header: '.card__header',
              collapsible: true
            });
          });

          $('.alias--user-register #details-privilege-card-wrapper, .path--user #details-privilege-card-wrapper').once('privilege-card').each(function () {
            if (context === document) {
              var error = $(this).find('.form-item--error-message');
              var active = false;
              if (error.length > 0) {
                active = 0;
              }

            $(this).accordion({
              header: '.privilege-card-wrapper-title',
              collapsible: true,
              active: active
            });
          });
        };

        $.fn.cartladdastop = function (element) {
          if (element.name == 'privilege_card_number2') {
            $.ladda('stopAll');
          }
        };
      }

      // Add jquery validations for privilege card number.
      $('#edit-privilege-card-number').rules('add', {
        loyalty_card_validate: true
      });

      // Add validation for card number confirmation.
      $('#edit-privilege-card-number2').rules('add', {
        equalTo: '#edit-privilege-card-number',
        messages: {
          equalTo: Drupal.t('Specified PRIVILEGES CLUB card numbers do not match.')
        }
      });

    }
  };

  // Custom validator for loyalty card.
  $.validator.addMethod('loyalty_card_validate', function (value, element, options) {
    var loyalty_card_number = Drupal.alshayaLoyaltyCleanCardNumber(value);
    var alshaya_loyalty_validator_settings = drupalSettings.alshaya_loyalty.card_validate;
    var message = Drupal.t('@number is not a valid PRIVILEGES CLUB card number.', {'@number': value});

    if (loyalty_card_number) {
      // Pass if no value was entered.
      if (parseInt(loyalty_card_number) === parseInt(alshaya_loyalty_validator_settings.value_start_with)) {
        return true;
      }

      // Fail if length doesn't match the set validation length.
      if (loyalty_card_number.length !== alshaya_loyalty_validator_settings.length) {
        $.validator.messages.loyalty_card_validate = message;
        return false;
      }

      // Check prefix matches.
      var loyalty_card_prefix = loyalty_card_number.toString().substr(0, alshaya_loyalty_validator_settings.value_start_with.length);
      if (loyalty_card_prefix !== alshaya_loyalty_validator_settings.value_start_with) {
        $.validator.messages.loyalty_card_validate = message;
        return false;
      }

      // Validate credit card number.
      if (!Drupal.validateCreditCardNumber(loyalty_card_number)) {
        $.validator.messages.loyalty_card_validate = message;
        return false;
      }

      $.validator.messages.loyalty_card_validate = '';
      return true;
    }
    else {
      $.validator.messages.loyalty_card_validate = message;
      return false;
    }
  }, '');

  /**
   * Helper function to remove '-' from card number.
   *
     * @param loyalty_card_number
     */
  Drupal.alshayaLoyaltyCleanCardNumber = function (loyalty_card_number) {
    return loyalty_card_number.replace(/-/g, '');
  };

  /**
   * Helper function to validate credit card numbers.
   *   (Taken from WebformCreditCardNumber::validCreditCardNumber())
   *
     * @param loyalty_card_number
     *
  * @returns {boolean}
     */
  Drupal.validateCreditCardNumber = function (credit_card_number) {
    // Set the string length and parity.
    var number_length = credit_card_number.length;
    var parity = number_length % 2;
    var loyalty_card_digits = credit_card_number.toString().split('');

    // Loop through each digit and do the maths.
    var total = 0;
    for (var i = 0; i < number_length; i++) {
      var digit = parseInt(loyalty_card_digits[i]);
      // Multiply alternate digits by two.
      if ((i % 2) === parity) {
        digit *= 2;
        // If the sum is two digits, add them together (in effect).
        if (digit > 9) {
          digit -= 9;
        }
      }
      // Total up the digits.
      total += digit;
    }

    // If the total mod 10 equals 0, the number is valid.
    return ((total % 10) === 0);
  };
})(jQuery, Drupal);
