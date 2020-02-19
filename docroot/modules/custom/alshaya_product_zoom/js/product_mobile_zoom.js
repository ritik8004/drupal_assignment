/**
 * @file
 * Product Zoom Gallery.
 */

/* global isRTL */

(function ($) {
  'use strict';
  Drupal.behaviors.alshaya_product_mobile_zoom = {
    attach: function (context, settings) {
      function applyRtl(ocObject, options) {
        if (isRTL() && $(window).width() < 1025) {
          ocObject.attr('dir', 'rtl');
          ocObject.slick(
            $.extend({}, options, {rtl: true})
          );
          if (context !== document) {
            ocObject.slick('resize');
          }
        }
        else {
          ocObject.slick(options);
          if (context !== document) {
            ocObject.slick('resize');
          }
        }
      }

      // Mobile grey block hiding over the image after 3secs.
      $('.mobilegallery .subtext').delay(3000).fadeOut();

      // Modal view for mobile when clicking on PDP image on mobile.
      $('#product-full-screen-gallery-container', context).once('js-event').each(function () {
        var element = $(this);
        var dialogsettings = {
          autoOpen: true,
          // Change dimensions of modal window as per theme needs.
          width: 1024,
          height: 768,
          dialogClass: 'dialog-product-image-gallery-container',
          open: function () {
            var currentmobSlide = parseInt($('#product-image-gallery-mobile .slick-current').attr('data-slick-index'));
            var slickModalOptions = {
              slidesToShow: 1,
              vertical: false,
              dots: true,
              arrows: false,
              centerMode: false,
              infinite: false,
              focusOnSelect: true,
              initialSlide: currentmobSlide,
              touchThreshold: 5
            };

            var gallery = $('#product-full-screen-gallery-container.ui-dialog-content #product-full-screen-gallery');
            if (!gallery.hasClass('slick-initialized')) {
              // Slick Slider initialisation.
              applyRtl(gallery, slickModalOptions);
              // Sync dots on startup.
              Drupal.behaviors.pdpInstagranDots.syncDots(gallery, currentmobSlide, false);
              // Check if we are opening modal again in which case we need to
              // setup i-dots again.
              if (!gallery.find('ul.slick-dots').hasClass('i-dots')) {
                // Do initial setup again for slick dots.
                Drupal.behaviors.pdpInstagranDots.initialSetup(gallery);
                // Attach the change event explicitly.
                Drupal.behaviors.pdpInstagranDots.attachBeforeChange(gallery);
                // Sync dots.
                Drupal.behaviors.pdpInstagranDots.syncDots(gallery, currentmobSlide, true);
              }
            }

            // Dont show product labels on video.
            gallery.on('afterChange', function (event, slick) {
              // Hide Labels on video slides.
              Drupal.hideProductLabelOnVideo(gallery, 'imagegallery__thumbnails__video', true);
            });

            $('.dialog-product-image-gallery-container button.ui-dialog-titlebar-close').on('mousedown', function () {
              var productGallery = $('#product-full-screen-gallery', $(this).closest('.dialog-product-image-gallery-container'));
              // Closing modal window before slick library gets removed.
              $(this).click();
              productGallery.slick('unslick');
              $('body').removeClass('pdp-modal-overlay');
            });
          }
        };

        // Open Gallery modal when we click on the zoom image.
        var mobileDialog = Drupal.dialog(element, dialogsettings);

        $('#product-image-gallery-mobile li', context).once('mobile-gallery').on('click', function () {
          if (!$(this).hasClass('mobilegallery__thumbnails__video')) {
            $('body').addClass('pdp-modal-overlay');
            mobileDialog.show();
            mobileDialog.showModal();
            Drupal.attachBehaviors(context);
          }
        });
      });
    }
  };
})(jQuery);
