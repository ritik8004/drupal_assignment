/**
 * @file
 * Fragrance filter js.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.OptionlistFilter = {
    attach: function (context, settings) {
      if ($(window).width() < 768) {
        $('.attribute-option-details-shop-by .attribute-detail-link ').once().on('click', function (e) {
          $('body').addClass('optionlist-filter-overlay');
        });

        $('.attribute-overlay-close-icon').once().on('click', function (e) {
          $('body').removeClass('optionlist-filter-overlay');
        });
      }
    }
  };

})(jQuery, Drupal);
