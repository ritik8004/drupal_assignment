/**
 * @file
 * Custom js file.
 */

(function ($, Drupal) {

  Drupal.behaviors.TogglemenuWithSlugBanner = {
    attach: function (context, settings) {
      if ($(window).width() < 1025) {
        $('.hamburger--menu').once().on('click', function () {
          var SlugBannerHeight = 0;
          if (!$('body').hasClass('header--fixed')) {
            SlugBannerHeight = $('#block-sitewidebanneren').outerHeight();
          }
          $('.toggle--sign, .mobile--close').css('top', $('.block-alshaya-super-category-menu').outerHeight() + SlugBannerHeight);
          $('.menu--one__list, .c-menu-secondary, .c-my-account-nav').css('top', $('.block-alshaya-super-category-menu').outerHeight() + $('.toggle--sign').outerHeight() + SlugBannerHeight);
        });
      }
    }
  };

})(jQuery, Drupal);
