/**
 * @file
 * Back To Top.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.backToTop = {
    attach: function () {
      var backToTop = $('#backtotop');
      var footer = $('.c-footer-primary');
      var sticky = false;

      if (backToTop.length > 0) {
        $(window).scroll(function () {
          backToTopScroll();
        });
      }

      function backToTopScroll() {
        var backToTopPosition = backToTop[0].getBoundingClientRect();
        var backToTopBottom = backToTopPosition.bottom;

        var footerPosition = footer[0].getBoundingClientRect();
        var footerTop = footerPosition.top;

        var windowHeight = $(window).height();

        if (backToTopBottom >= (footerTop - 17) || sticky) {
          backToTop.css('bottom', windowHeight - footerTop + 'px');
          sticky = true;

          if (backToTopBottom >= windowHeight) {
            sticky = false;
            backToTop.css('bottom', '');
          }
        }
      }
    }
  };
})(jQuery, Drupal);
