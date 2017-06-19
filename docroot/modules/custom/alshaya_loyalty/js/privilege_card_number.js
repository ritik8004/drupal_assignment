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
        };

        Drupal.Ajax.prototype.success = function (response, status) {
          $(this).cartladdastop(this.element);
          // Invoke the original function.
          this.PrivilegeCardsuccess(response, status);
        };

        $.fn.cartladdastop = function(element) {
          if(element.name == 'privilege_card_number2') {
            $.ladda('stopAll');
          }
        }
      }
    }
  };
})(jQuery, Drupal);
