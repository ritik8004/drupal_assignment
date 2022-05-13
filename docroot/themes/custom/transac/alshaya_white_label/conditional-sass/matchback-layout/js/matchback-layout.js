/**
 * @file
 * Product matchback layout.
 */

/* global isRTL */

(function ($, Drupal) {

  // Matchback slick options
  var optionMatchback = {
    slidesToShow: 1,
    slidesToScroll: 1,
    focusOnSelect: false,
    touchThreshold: 1000,
    dots: true,
    infinite: false
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
    applyMatchbackRtl(ocObject, optionMatchback);
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
      $('.sku-base-form', context).once('matchback-variant').on('variant-selected', function () {
        var variantMatchbackSlider = $(this).closest('.cross-sell-product-component-right').siblings('.cross-sell-product-component-left').find('.machbackSlider');
        var zoom_wrap = $(this).closest('.cross-sell-product-component-right').siblings('.cross-sell-product-component-left').find('.imagezoom-wrap')
        zoom_wrap.each(function () {
          $(this).bind( "click", function () {
            matchbackZoomModal($(this));
          });
        });
        if (variantMatchbackSlider.length > 0) {
          matchbackSlider(variantMatchbackSlider);
        }
      });

      // On matchback product change using attributes like fragnance for grouped products, attach slick on matchback gallery and bind click event to open zoom image in modal.
      $('article.entity--type-node').once('matchback-variant-group').on('group-item-selected', function () {
        var variantMatchbackSlider = $(this).find('.cross-sell-product-component-left .machbackSlider');
        var zoom_wrap = $(this).find('.cross-sell-product-component-left .imagezoom-wrap');
        zoom_wrap.each(function () {
          $(this).bind( "click", function () {
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
