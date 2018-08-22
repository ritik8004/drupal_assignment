/**
 * @file
 * Custom JS for HM brand so we don't have to duplicate common JS from MC.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.custom_overrides = {
    attach: function (context, settings) {
      $('.coupon-code-wrapper, .alias--cart #details-privilege-card-wrapper').each(function () {
        $(this).find('.details-privilege-card-wrapper-inside').css('height', 'auto');
      });

      $('.alias--user-register #details-privilege-card-wrapper').each(function () {
        $(this).find('.details-privilege-card-wrapper-inside').css('height', 'auto');
      });

      $('.path--user #details-privilege-card-wrapper').each(function () {
        $(this).find('.details-privilege-card-wrapper-inside').css('height', 'auto');
      });
    }
  };

})(jQuery, Drupal);
