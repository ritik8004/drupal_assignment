/**
 * @file
 * Back To Top.
 */

/* global debounce */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.backToTop = {
    attach: function () {
      var winWidth = $(window).width();
      var backToTop = $('#backtotop');
      var cFooter = $('.c-footer');

      backToTop.prependTo(cFooter);

      if (winWidth < 768 && cFooter.hasClass('language-switcher-enabled')) {
        var windowScrollTop = 0;
        var footerHeight = 0;

        $(window).once('back-to-top').on('scroll', debounce(function () {
          windowScrollTop = $(window).scrollTop() + $(window).height();
          footerHeight = $(document).height() - cFooter.height();
          if (windowScrollTop < footerHeight) {
            backToTop.removeClass('backtotop-withfooter').addClass('backtotop-nofooter');
          }
          else if (windowScrollTop > footerHeight) {
            backToTop.removeClass('backtotop-nofooter').addClass('backtotop-withfooter');
          }
        }, 200));
      }
    }
  };
})(jQuery, Drupal);
