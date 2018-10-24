/**
 * @file
 * Product Zoom Gallery.
 */

/* global isRTL */

(function ($) {
  'use strict';
  Drupal.behaviors.alshaya_product_mobile_zoom = {
    attach: function (context, settings) {

      // Modal view for mobile when clicking on PDP image on mobile.
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

      $('#product-image-gallery-container-mobile').once('js-event').each(function () {
        var element = $(this);
        var dialogsettings = {
          autoOpen: true,
          // Change dimensions of modal window as per theme needs.
          width: 1024,
          height: 768,
          dialogClass: 'dialog-product-image-gallery-container-mobile',
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

            var gallery = $('#product-image-gallery-mob');
            applyRtl(gallery, slickModalOptions);

            $('.mob-imagegallery__wrapper .subtext').show().delay(5000).fadeOut();

            gallery.on('swipe', function (event, slick) {
              var image = '.mob-imagegallery__thumbnails__image[data-slick-index="' + slick.currentSlide + '"] img';
              if (!($(image).attr('data-scale') === 1 || $(image).attr('data-translate-x') === 0 || $(image).attr('data-translate-y') === 0)) {
                $(image).attr('data-scale', 1);
                $(image).attr('data-translate-x', 0);
                $(image).attr('data-translate-y', 0);
                $(image).css('transform', 'translate3d(0px, 0px, 0px) scale3d(1, 1, 1)');
                $(image).parent().removeClass('active');
              }
            });

            $('.dialog-product-image-gallery-container-mobile button.ui-dialog-titlebar-close').on('mousedown', function () {
              var productGallery = $('#product-image-gallery-mob', $(this).closest('.dialog-product-image-gallery-container-mobile'));
              productGallery.slick('unslick');
              $('body').removeClass('pdp-modal-overlay');
              $('#product-image-gallery-mob').find('img').css('transform', 'none');
            });

          }
        };

        // Open Gallery modal when we click on the zoom image.
        var mobileDialog = Drupal.dialog(element, dialogsettings);

        $('#product-image-gallery-mobile li').off().on('click', function () {
          if (!$(this).hasClass('mobilegallery__thumbnails__video')) {
            $('body').addClass('pdp-modal-overlay');
            mobileDialog.show();
            mobileDialog.showModal();
          }
        });
      });
    }
  };
})(jQuery);
