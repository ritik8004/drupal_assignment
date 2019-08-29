/**
 * @file
 * Custom js file.
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

      // var optionsHeroImageBanner = {
      //   arrows: true,
      //   useTransform: false,
      //   slidesToShow: 1,
      //   slidesToScroll: 1,
      //   focusOnSelect: false,
      //   touchThreshold: 1000,
      //   infinite: false,
      //   responsive: [
      //     {
      //       breakpoint: 1025,
      //       settings: {
      //         arrows: false,
      //         dots: true
      //       }
      //     }
      //   ]
      // };

      function applyRtl(ocObject, options) {
        if (isRTL()) {
          ocObject.attr('dir', 'rtl');
          ocObject.slick(
            $.extend({}, options, {rtl: true})
          );
        }
        else {
          ocObject.slick(options);
        }
      }

      var shopByStory = $('.item-list ul');
      // var magazineHeroBanner = $('.field--name-field-magazine-hero-image.field__items');

      // For tablets and mobile we don't want to apply slickSlider.
      // if ($(window).width() > 1023) {
      //   shopByStory.each(function () {
      //     applyRtl($(this), optionsShopByStory);
      //   });
      // }

      setTimeout(function () {
        shopByStory.each(function () {
          applyRtl($(this), optionFreeGifts);
        });
      }, 5);

      /**
       * Helper function to remove classes from body when dailog is closed.
       *
       */
      // function modalFreeGiftCloseBtnEvent() {
      //   $('.ui-dialog-titlebar-close').once().on('click', function () {
      //     // Remove the last class added for overlay.
      //     if ($('body').hasClass('free-gifts-modal-overlay')) {
      //       $('body').removecla
      //     }
      //   });
      // }

      // $('.free-gift-title a, .free-gift-image a, .path--cart #table-cart-items table tr td.name a').on('click', function () {
      //   $('body').addClass('free-gifts-modal-overlay');
      // });

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

      $('.free-gift-view a').once().on('click', function () {
        $('.ui-dialog-title').hide();
      });
      //
      $('.free-gifts-modal-overlay #drupal-modal article > a').once().on('click', function () {
        $('.ui-dialog-title').show();

        // $(context).ajaxComplete(function () {
        //   $('.ui-dialog-title').show();
        // });
      });
    }
  };

})(jQuery, Drupal);
