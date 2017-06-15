/**
 * @file
 * Sliders.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.accordion = {
    attach: function (context, settings) {
      $('.form-item-field-address-0-address-administrative-area select')
        .once()
        .each(function () {
          $(this).select2();
        });
    }
  };
})(jQuery, Drupal);
