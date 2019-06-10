/**
 * @file
 * Promo panel js.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.promoPanel = {
    attach: function (context, settings) {
      var $offer_toggler = $('.block-promo-panel-wrapper .promo-panel-label', context);
      var $mobile_link = $('.block-promo-panel-wrapper .promo-panel-label a', context);
      var $body = $('body');

      $($offer_toggler).once('alshaya_promo_panel').on('click', function () {
        $(window).trigger('resize');
        $(this).parent().toggleClass('active-promo-panel');
        $($body).toggleClass('active-promo-panel-content');
      });

      $($mobile_link).once('alshaya_promo_mobile_link').on('click', function(e) {
        if(/Android|webOS|iPhone|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
          return true;
        }
        else {
          e.preventDefault();
        }
      });

      /*
       * Promo panel sticky on scroll.
       */
      function checkOffset() {
        if ($('.c-footer').length > 0) {
          if ($('.block-promo-panel-wrapper .promo-panel-label').offset().top >= $('.c-footer').offset().top - 49) {
            $('.block-promo-panel-wrapper .promo-panel-label').addClass('label-not-fixed'); // restore on scroll down
            $('.block-promo-panel-wrapper').removeClass('promo-panel-fixed').addClass('promo-static'); // restore on scroll down
          }

          if ($(document).scrollTop() + window.innerHeight < $('.c-footer').offset().top) {
            $('.block-promo-panel-wrapper .promo-panel-label').removeClass('label-not-fixed');
            $('.block-promo-panel-wrapper').addClass('promo-panel-fixed').removeClass('promo-static');
          }
        }
      }

      checkOffset();

      $(window, context).scroll(function () {
        checkOffset();
      });

      // Close Promo panel when clicked anywhere outside.
      var $promo_panel_content = $('.promo__panel > .field--name-field-paragraph-content', context);
      $('body', context).on('click', function(e) {
        if (!$(e.target).closest($promo_panel_content).length && !$(e.target).closest($offer_toggler).length) {
          $($offer_toggler).parent().removeClass('active-promo-panel');
          $($body).removeClass('active-promo-panel-content');
        }
      });
    }
  };

})(jQuery, Drupal);
