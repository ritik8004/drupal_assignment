/**
 * @file
 * Search.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.toggleSearch = {
    attach: function (context, settings) {
      $('.toggle-search').on('click', function () {
        $('.search-block').toggleClass('search-active');
      });
    }
  };

})(jQuery, Drupal);
