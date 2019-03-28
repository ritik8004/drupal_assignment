/**
 * @file
 * Fragrance filter js.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.configurableoxes = {
    attach: function (context, settings) {
      if ($(window).width() < 768) {
        $('.fragrance_name .attribute-detail-link ').once().on('click', function (e) {
          $('body').addClass('fragrance-filter-overlay');
        });

        $('.fragrance-overlay-close-icon').once().on('click', function (e) {
          $('body').removeClass('fragrance-filter-overlay');
        });
      }
    }
  };

})(jQuery, Drupal);
