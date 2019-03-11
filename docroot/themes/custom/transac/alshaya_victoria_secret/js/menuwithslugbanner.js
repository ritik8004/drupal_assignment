/**
 * @file
 * Custom js file.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.TogglemenuWithSlugBanner = {
    attach: function (context, settings) {
      if ($(window).width() < 1025) {
        $('.hamburger--menu').once().on('click', function () {
          if ($('body').hasClass('header--fixed')) {
            $('.toggle--sign, .mobile--close').css('top', $('.block-alshaya-super-category-menu').outerHeight());
            $('.menu--one__list').css('top', $('.block-alshaya-super-category-menu').outerHeight() + $('.toggle--sign').outerHeight());
          }
          else {
            var TopHeight = $('.block-alshaya-super-category-menu').outerHeight() + $('.mobile-slug-banner').outerHeight();

            $('.toggle--sign, .mobile--close').css('top', TopHeight);
            $('.menu--one__list').css('top', TopHeight + $('.toggle--sign').outerHeight());
          }
        });
      }
    }
  };

})(jQuery, Drupal);
