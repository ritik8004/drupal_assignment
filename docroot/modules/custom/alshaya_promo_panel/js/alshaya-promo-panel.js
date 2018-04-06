/**
 * @file
 * Promo panel js.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.promoPanel = {
    attach: function (context, settings) {
      var $offer_toggler = $('.block-content--block-content-promo-panel h2');
      var $offer_content = $('.block-content--block-content-promo-panel .field-paragraph-content');
      var $overlay_content = $('.empty-overlay');
      var $body = $('body');

      $($offer_toggler).on('click', function () {
        $(this).parent().toggleClass('active-offer');
        $($offer_content).slideToggle('500');
        $($body).toggleClass('active-todays-offer');
        $($overlay_content).toggleClass('overlay-content');
      });

      /*
       * Promo panel sticky on scroll.
       */
      function checkOffset() {
        if ($('.block-content--block-content-promo-panel h2').offset().top + $('.block-content--block-content-promo-panel h2').height() >= $('.block-content--block-content-promo-panel').offset().top - 30) {
          $('.block-content--block-content-promo-panel h2').addClass('label-not-fixed'); // restore on scroll down
          $('.block-content--block-content-promo-panel').removeClass('todays-offer-fixed'); // restore on scroll down
        }

        if ($(document).scrollTop() + window.innerHeight < $('.block-todays-offer').offset().top) {
          $('.block-content--block-content-promo-panel h2').removeClass('label-not-fixed');
          $('.block-content--block-content-promo-panel').addClass('todays-offer-fixed');
        }
      }

      $(window, context).scroll(function () {
        checkOffset();
      });
    }
  };

})(jQuery, Drupal);
