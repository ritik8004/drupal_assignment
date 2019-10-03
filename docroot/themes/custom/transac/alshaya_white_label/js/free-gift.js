/**
 * @file
 * Free gift js file.
 */

/* global isRTL */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.freeGiftsSlider = {
    attach: function (context, settings) {
      var optionFreeGifts = {
        arrows: true,
        useTransform: false,
        slidesToShow: 3,
        slidesToScroll: 1,
        focusOnSelect: false,
        touchThreshold: 1000,
        infinite: false
      };

      function applyRtl(ocObject, options) {
        if (isRTL()) {
          ocObject.attr('dir', 'rtl');
          ocObject.once().slick(
            $.extend({}, options, {rtl: true})
          );
        }
        else {
          ocObject.once().slick(options);
        }
      }

      var shopByStory = $('#drupal-modal .item-list ul');

      if ($(window).width() > 767) {
        setTimeout(function () {
          shopByStory.each(function () {
            applyRtl($(this), optionFreeGifts);
          });
        }, 5);
      }

      if ($('.free-gifts-modal-overlay').length > 0) {
        if ($('.free-gift-view').length > 0) {
          $('#drupal-modal').addClass('free-gift-listing-modal');
          $('#drupal-modal').removeClass('free-gift-detail-modal');
        }
        else {
          $('#drupal-modal').addClass('free-gift-detail-modal');
          $('#drupal-modal').removeClass('free-gift-listing-modal');
        }
      }

      $('.free-gift-promo-list a').once().on('click', function () {
        $('body').addClass('free-gift-promo-list-overlay');
      });

      $('.free-gift-title a, .free-gift-message a, .free-gift-image a, .gift-message a, .path--cart #table-cart-items table tr td.name a').on('click', function () {
        $('body').addClass('free-gifts-modal-overlay');
      });

      setTimeout(function () {
        $('.free-gifts-modal-overlay .ui-dialog-titlebar-close').once().on('click', function () {
          if ($('body').hasClass('free-gift-promo-list-overlay')) {
            $('body').removeClass('free-gift-promo-list-overlay');
          }
          $('body').removeClass('free-gifts-modal-overlay');
        });
      }, 3);

      $('.free-gift-view a').once().on('click', function () {
        // hide title on details page modal.
        $(document).ajaxComplete(function () {
          $('.ui-dialog-title').hide();
        });
      });

      $('.free-gifts-modal-overlay #drupal-modal article > a').once().on('click', function () {
        // show title when back to promo list page.
        $(document).ajaxComplete(function () {
          $('.ui-dialog-title').show();
        });
      });
    }
  };

})(jQuery, Drupal);
