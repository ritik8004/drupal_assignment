/**
 * @file
 * Promo panel js.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.promoPanel = {
    attach: function (context, settings) {
      var $offer_toggler = $('.block-content--block-content-promo-panel > .promo-panel-label');
      var $offer_content = $('.block-content--block-content-promo-panel > div');
      var $overlay_content = $('.empty-overlay');
      var $body = $('body');

      $($offer_toggler).on('click', function () {
        $(this).parent().toggleClass('active-promo-panel');
        $($offer_content).slideToggle('500');
        $($body).toggleClass('active-promo-panel-content');
        $($overlay_content).toggleClass('overlay-content');
      });

      /*
       * Promo panel sticky on scroll.
       */
      function checkOffset() {
        if ($('.block-content--block-content-promo-panel > .promo-panel-label').offset().top + $('.block-content--block-content-promo-panel > .promo-panel-label').height() >= $('.block-content--block-content-promo-panel').offset().top - 30) {
          $('.block-content--block-content-promo-panel > .promo-panel-label').addClass('label-not-fixed'); // restore on scroll down
          $('.block-content--block-content-promo-panel').removeClass('promo-panel-fixed'); // restore on scroll down
        }

        if ($(document).scrollTop() + window.innerHeight < $('.block-content--block-content-promo-panel').offset().top) {
          $('.block-content--block-content-promo-panel > .promo-panel-label').removeClass('label-not-fixed');
          $('.block-content--block-content-promo-panel').addClass('promo-panel-fixed');
        }
      }

      $(window, context).scroll(function () {
        checkOffset();
      });
    }
  };

})(jQuery, Drupal);
