/**
 * @file
 * Product matchback layout.
 */

/* global isRTL */

(function ($, Drupal) {
  'use strict';

  // Matchback slick options
  var optionMatchback = {
    slidesToShow: 1,
    slidesToScroll: 1,
    focusOnSelect: false,
    touchThreshold: 1000,
    dots: true
  };

  // Matchback zoom image dialog options
  var dialogsettings = {
    autoOpen: true,
    // Change dimensions of modal window as per theme needs.
    width: 1024,
    height: 768,
    dialogClass: 'dialog-product-image-gallery-container dialog-matchback-image-gallery-container',
    open: productMatchbackDialogOpen
  };

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

  // Call applyMatchbackRtl() to initialise slick.
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

  // Call matchbackSlider() to apply slick and instagram dots.
  function matchbackSlider(ocObject) {
    ocObject.on('init', function (event, slick) {
      Drupal.behaviors.pdpInstagranDots.initialSetup($(this));
    });
    Drupal.behaviors.pdpInstagranDots.attachBeforeChange($(this));
    applyMatchbackRtl(ocObject, optionMatchback);
    applyMatchbackHorizontalLazyLoad($(this));
    if ($(window).width() > 767) {
      var currentSlide = parseInt($(this).find('.slick-current').attr('data-slick-index'));
      // Create Instagram Dots.
      if (!ocObject.find('ul.slick-dots').hasClass('i-dots')) {
        // Do initial setup again for slick dots.
        Drupal.behaviors.pdpInstagranDots.initialSetup(ocObject);
        // Attach the change event explicitly.
        Drupal.behaviors.pdpInstagranDots.attachBeforeChange(ocObject);
        // Sync dots.
        Drupal.behaviors.pdpInstagranDots.syncDots(ocObject, currentSlide, true);
      }
    }
  }

  // Call matchbackZoomModal() to open matchback zoom image in modal.
  function matchbackZoomModal(ocObject) {
    var zoom_src = ocObject.data('zoom-src');
    var imagezoom_container = ocObject.closest('.cloudzoom__thumbnails-matchback_gallery').find('.imagezoom-dialog-container');
    var element = imagezoom_container.clone();
    imagezoom_container.find('.matchback-imagegallery__full__image').attr("src",zoom_src);
    $('body').addClass('pdp-modal-overlay');
    var myDialog = Drupal.dialog(imagezoom_container, dialogsettings);
    myDialog.show();
    myDialog.showModal();
    ocObject.closest('.cloudzoom__thumbnails-matchback_gallery').append(element);
  }

  /**
   * Zoom modal dialog.
   */
  function productMatchbackDialogOpen() {
    $('.dialog-matchback-image-gallery-container button.ui-dialog-titlebar-close').on('mousedown', function () {
      $('body').removeClass('pdp-modal-overlay');
    });
  }

  Drupal.behaviors.productMatchbackLayout = {
    attach: function (context, settings) {

      var matchback = $('.horizontal-crossell.above-mobile-block .machbackSlider');

      $('#machbackSlider .imagezoom-wrap').off().on('click', function (e) {
        matchbackZoomModal($(this));
      });

      if (matchback) {
        matchback.each(function () {
          matchbackSlider($(this));
        });
      }

      // On matchback variant change attach slick on matchback gallery and bind click even to open zoom image in modal.
      $('.sku-base-form').once('matchback-variant').on('variant-selected', function () {
        var variantMatchbackSlider = $(this).closest('.cross-sell-product-component-right').siblings('.cross-sell-product-component-left').find('.machbackSlider');
        var zoom_wrap = $(this).closest('.cross-sell-product-component-right').siblings('.cross-sell-product-component-left').find('.imagezoom-wrap')
        zoom_wrap.each(function () {
          $(this).bind( "click", function() {
            matchbackZoomModal($(this));
          });
        });
        if (variantMatchbackSlider.length > 0) {
          matchbackSlider(variantMatchbackSlider);
        }
      });
    }
  };
})(jQuery, Drupal);
