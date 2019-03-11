/**
 * @file
 * Custom js file.
 */

(function ($, Drupal) {
  'use strict';

  if ($(window).width() < 1025) {
    var TopHeight = $('.block-alshaya-super-category-menu').outerHeight() + $('.mobile-slug-banner').outerHeight();

    $('.toggle--sign, .mobile--close').css('top', TopHeight);
    $('.menu--one__list').css('top', TopHeight + $('.toggle--sign').outerHeight());
  }

})(jQuery, Drupal);
