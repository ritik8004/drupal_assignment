/**
 * @file
 * Promo panel js.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.promoPanel = {
    attach: function (context, settings) {
      var $offer_toggler = $('.block-promo-panel-wrapper > .promo-panel-label');
      var $offer_content = $('.block-promo-panel-wrapper > div');
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
        if ($('.block-promo-panel-wrapper > .promo-panel-label').offset().top + $('.block-promo-panel-wrapper > .promo-panel-label').height() >= $('.block-promo-panel-wrapper').offset().top - 30) {
          $('.block-promo-panel-wrapper > .promo-panel-label').addClass('label-not-fixed'); // restore on scroll down
          $('.block-promo-panel-wrapper').removeClass('promo-panel-fixed'); // restore on scroll down
        }

        if ($(document).scrollTop() + window.innerHeight < $('.block-promo-panel-wrapper').offset().top) {
          $('.block-promo-panel-wrapper > .promo-panel-label').removeClass('label-not-fixed');
          $('.block-promo-panel-wrapper').addClass('promo-panel-fixed');
        }
      }

      $(window, context).scroll(function () {
        checkOffset();
      });
    }
  };

})(jQuery, Drupal);
