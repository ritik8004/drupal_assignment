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
          $('#backtotop').css({
            position: 'fixed',
            bottom: '1px',
            top: 'auto'
          });
        }

        if ($(window).scrollTop() + $(window).height() > $(document).height() - $('.c-footer').height()) {
          $('#backtotop').css({
            position: 'absolute',
            top: '-70px'
          });
        }
      });
    }
  };
})(jQuery, Drupal);
