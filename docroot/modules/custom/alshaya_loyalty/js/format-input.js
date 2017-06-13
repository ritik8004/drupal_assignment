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
    }
  };
})(jQuery, Drupal);
