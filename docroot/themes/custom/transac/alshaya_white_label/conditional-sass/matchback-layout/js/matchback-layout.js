/**
 * @file
 * Product matchback layout.
 */

/* global isRTL */

(function ($, Drupal) {
  'use strict';

  /**
   * Call blazyRevalidate() on afterChange of slick sliders.
   *
   * @param {object} carousel
   * The carousel element.
   */
  function applyMatchbackHorizontalLazyLoad(carousel) {
    // Lazy Load on carousels.
    carousel.on('afterChange', function () {
      Drupal.blazyRevalidate();
    });
  }

  Drupal.behaviors.productMatchbackLayout = {
    attach: function (context, settings) {

      var dialogsettings = {
        autoOpen: true,
        // Change dimensions of modal window as per theme needs.
        width: 1024,
        height: 768,
        dialogClass: 'dialog-product-image-gallery-container dialog-matchback-image-gallery-container',
        open: _product_matchback_dialog_open
      };

      var optionMatchback = {
        slidesToShow: 1,
        slidesToScroll: 1,
        focusOnSelect: false,
        touchThreshold: 1000,
        dots: true
      };

      var matchback = $('.horizontal-crossell.above-mobile-block .machbackSlider');

      function applyMatchbackRtl(ocObject, options) {

        // For mobile we don't want to apply OwlCarousel.
        if ($(window).width() < 768) {
          return;
        }

        // Get number of items.
        var itemsCount = ocObject.find('.views-row').length;

        // Check dynamically if looping is required and at which breakpoint.
        for (var i in options.responsive) {
          if (options.responsive[i]) {
            options.responsive[i].loop = options.responsive[i].items < itemsCount;
          }
        }

        if (isRTL()) {
          ocObject.attr('dir', 'rtl');
          ocObject.once('product-matchback-carousel').slick($.extend({}, options, {
            rtl: true
          }));
        }
        else {
          ocObject.once('product-matchback-carousel').slick(options);
        }
      }

      $('#machbackSlider .imagezoom-wrap').off().on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var element = $(this).find('.imagezoom-dialog-container').clone();
        $('body').addClass('pdp-modal-overlay');
        var myDialog = Drupal.dialog($(this).find('.imagezoom-dialog-container'), dialogsettings);
        myDialog.show();
        myDialog.showModal();
        $(this).append(element);
      });

      /**
       * Zoom modal dialog.
       */
      function _product_matchback_dialog_open() {
        $('.dialog-matchback-image-gallery-container button.ui-dialog-titlebar-close').on('mousedown', function () {
          $('body').removeClass('pdp-modal-overlay');
        });
      }

      if (matchback) {
        matchback.each(function () {
          $(this).on('init', function (event, slick) {
            Drupal.behaviors.pdpInstagranDots.initialSetup($(this));
          });
          Drupal.behaviors.pdpInstagranDots.attachBeforeChange($(this));
          applyMatchbackRtl($(this), optionMatchback);
          applyMatchbackHorizontalLazyLoad($(this));
          if ($(window).width() > 767) {
            var currentSlide = parseInt($(this).find('.slick-current').attr('data-slick-index'));
            // Create Instagram Dots.
            if (!$(this).find('ul.slick-dots').hasClass('i-dots')) {
              // Do initial setup again for slick dots.
              Drupal.behaviors.pdpInstagranDots.initialSetup($(this));
              // Attach the change event explicitly.
              Drupal.behaviors.pdpInstagranDots.attachBeforeChange($(this));
              // Sync dots.
              Drupal.behaviors.pdpInstagranDots.syncDots($(this), currentSlide, true);
            }
          }
        });
      }
    }
  };
})(jQuery, Drupal);
