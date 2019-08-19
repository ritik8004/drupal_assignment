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
          var SlugBannerHeight = 0;
          if (!$('body').hasClass('header--fixed')) {
            SlugBannerHeight = $('.mobile-slug-banner').outerHeight();
          }
          $('.toggle--sign, .mobile--close').css('top', $('.block-alshaya-super-category-menu').outerHeight() + SlugBannerHeight);
          $('.menu--one__list').css('top', $('.block-alshaya-super-category-menu').outerHeight() + $('.toggle--sign').outerHeight() + SlugBannerHeight);
        });
      }
    }
  };

})(jQuery, Drupal);
