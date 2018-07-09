/**
 * @file
 * Promo panel js.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.promoPanel = {
    attach: function (context, settings) {
      var $offer_toggler = $('.block-promo-panel-wrapper .promo-panel-label', context);
      var $mobile_link = $('.block-promo-panel-wrapper .promo-panel-label a');
      var $offer_content = $('.block-promo-panel-wrapper > .field--name-field-paragraph-content');
      var $body = $('body');

      $($offer_toggler).once().on('click', function () {
        $(window).trigger('resize');
        $(this).parent().toggleClass('active-promo-panel');
        $($body).toggleClass('active-promo-panel-content');
      });

      $($mobile_link).click(function(e) {
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
        if ($('.block-promo-panel-wrapper .promo-panel-label').offset().top + $('.block-promo-panel-wrapper .promo-panel-label').height() >= $('.block-promo-panel-wrapper').offset().top - 25) {
          $('.block-promo-panel-wrapper .promo-panel-label').addClass('label-not-fixed'); // restore on scroll down
          $('.block-promo-panel-wrapper').removeClass('promo-panel-fixed').addClass('promo-static'); // restore on scroll down
        }

        if ($(document).scrollTop() + window.innerHeight < $('.block-promo-panel-wrapper').offset().top) {
          $('.block-promo-panel-wrapper .promo-panel-label').removeClass('label-not-fixed');
          $('.block-promo-panel-wrapper').addClass('promo-panel-fixed').removeClass('promo-static');
        }
      }

      checkOffset();

      $(window, context).scroll(function () {
        checkOffset();
      });
    }
  };

})(jQuery, Drupal);
