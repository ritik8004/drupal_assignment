/**
 * @file
 * Free gift js file.
 */

/* global isRTL */

(function ($, Drupal) {
  'use strict';

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

  var optionFreeGifts = {
    arrows: true,
    useTransform: false,
    slidesToShow: 3,
    slidesToScroll: 1,
    focusOnSelect: false,
    touchThreshold: 1000,
    infinite: false
  };

  Drupal.behaviors.freeGiftsSlider = {
    attach: function (context, settings) {
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

      // On dialog close remove the free gift overlay related classes.
      $('.free-gifts-modal-overlay #drupal-modal').once().on('dialogclose', function () {
        if ($('body').hasClass('free-gift-promo-list-overlay')) {
          $('body').removeClass('free-gift-promo-list-overlay');
        }
        $('body').removeClass('free-gifts-modal-overlay');
      });

      $(document).ajaxComplete(function (event, xhr, settings) {
        if ($('.free-gifts-modal-overlay').length > 0) {
          if (settings.url.indexOf('back') !== -1) {
            $('.ui-dialog-title').hide();
          }
          else if (settings.url.indexOf('replace') !== -1) {
            $('.ui-dialog-title').show();
          }
        }
      });
    }
  };

})(jQuery, Drupal);
