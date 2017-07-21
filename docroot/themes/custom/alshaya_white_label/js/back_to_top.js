/**
 * @file
 * Back To Top.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.backToTop = {
    attach: function () {
      $('#backtotop').prependTo('.c-footer');

      $(window).scroll(function () {
        if ($(window).scrollTop() + $(window).height() < $(document).height() - $('.c-footer').height()) {
          $('#backtotop').addClass('backtotop-nofooter');
          $('#backtotop').removeClass('backtotop-withfooter');
        }

        if ($(window).scrollTop() + $(window).height() > $(document).height() - $('.c-footer').height()) {
          $('#backtotop').addClass('backtotop-withfooter');
          $('#backtotop').removeClass('backtotop-nofooter');
        }
      });
    }
  };
})(jQuery, Drupal);
