/**
 * @file
 * Banner margin override..
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.bannerMarginOverride = {
    attach: function () {
      $('.node--type-advanced-page .c-promo__item__override').each(function () {
        $(this).parents('.c-promo__item').addClass('c-promo__item__override_processed');
      });
    }
  };
})(jQuery, Drupal);
