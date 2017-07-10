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
          if ($(window).width() <= 768) {
            $('#backtotop').css({
              position: 'absolute',
              top: '-65px'
            });

            $('.nodetype--acq_product #backtotop').css({
              position: 'absolute',
              top: '-30px'
            });
          }
          else {
            $('#backtotop').css({
              position: 'absolute',
              top: '-70px'
            });
          }
        }
      });
    }
  };
})(jQuery, Drupal);
