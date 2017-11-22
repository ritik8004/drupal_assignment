/**
 * @file
 * Product Zoom Gallery.
 */

/* global isRTL */

(function($) {

  // Based on https://gist.github.com/asgeo1/1652946

  /**
   * Bind an event handler to a "double tap" JavaScript event.
   * @param {function} handler
   * @param {number} [delay=300]
   */
  $.fn.doubletap = $.fn.doubletap || function(handler, delay) {
    delay = delay == null ? 300 : delay;
    this.bind('touchend', function(event) {
      var now = new Date().getTime();
      // The first time this will make delta a negative number.
      var lastTouch = $(this).data('lastTouch') || now + 1;
      var delta = now - lastTouch;
      if (delta < delay && 0 < delta) {
        // After we detect a doubletap, start over.
        $(this).data('lastTouch', null);
        if (handler !== null && typeof handler === 'function') {
          handler(event);
        }
      } else {
        $(this).data('lastTouch', now);
      }
    });
  };
})(jQuery);

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

      var element = document.getElementById('product-image-gallery-container-mobile');
      var dialogsettings = {
        autoOpen: true,
        // Change dimensions of modal window as per theme needs.
        width: 1024,
        height: 768,
        dialogClass: 'dialog-product-image-gallery-container-mobile',
        open: function () {
          var img_scale = 1;

          var slickModalOptions = {
            slidesToShow: 1,
            vertical: false,
            dots: true,
            arrows: false,
            centerMode: false,
            infinite: false,
            focusOnSelect: true,
            initialSlide: 0
          };

          var gallery = $('#product-image-gallery-mob');
          applyRtl(gallery, slickModalOptions);

          $('.mob-imagegallery__wrapper .subtext').show().delay(5000).fadeOut();
          $('#product-image-gallery-mob .mob-imagegallery__thumbnails__image img').doubletap(function(e) {
            $(this).parent().siblings().find('img.expand').each(function () {
              $(this).removeClass('expand').css({'transform': 'scale(1)', 'transition': 'transform 300ms ease-out'});
            });
            if ($(e.target).hasClass('expand')) {
              $(e.target).removeClass('expand').css({'transform': 'scale(1)', 'transition': 'transform 300ms ease-out'});
            } else
            {
              $(e.target).addClass('expand').css({'transform': 'scale(3)', 'transition': 'transform 300ms ease-out'});
            }
          });

          $('.zoomin').removeClass('disabled');
          $('.zoomout').removeClass('disabled');

          $('.zoomin').on('click', function () {
            var image = $('#product-image-gallery-mob .mob-imagegallery__thumbnails__image.slick-current img');
            if (img_scale < 1.75) {
              img_scale = img_scale + 0.25;

              image.css({'transform': 'scale(' + img_scale + ')', 'transition': 'transform 300ms ease-out'});
              $('.zoomout').removeClass('disabled');
            }
            else {
              $(this).addClass('disabled');
            }

          });
          $('.zoomout').on('click', function () {
            var image = $('#product-image-gallery-mob .mob-imagegallery__thumbnails__image.slick-current img');
            if (img_scale <= 1) {
              $(this).addClass('disabled');
              return;
            } else {
              img_scale = img_scale - 0.25;
              $('.zoomin').removeClass('disabled');
              image.css({'transform': 'scale(' + img_scale + ')', 'transition': 'transform 300ms ease-out'});
            }
          });

          $('#product-image-gallery-mob').on('swipe', function (event, slick) {
            var image = $(this).find('.mob-imagegallery__thumbnails__image[data-slick-index="'+ slick.currentSlide +'"] img');
            $('.zoomin').removeClass('disabled');
            $('.zoomout').removeClass('disabled');
            image.parent().siblings().each(function () {
              $(this).find('img').css('transform', 'scale(1)');
            });
          });

          $('.dialog-product-image-gallery-container-mobile button.ui-dialog-titlebar-close').on('mousedown', function () {
            var productGallery = $('#product-image-gallery-mob', $(this).closest('.dialog-product-image-gallery-container-mobile'));
            productGallery.slick('unslick');
            $('body').removeClass('pdp-modal-overlay');
          });

        }
      };
      // Open Gallery modal when we click on the zoom image.
      var mobileDialog = Drupal.dialog(element, dialogsettings);
      $('#product-image-gallery-mobile .lslide').off().on('click', function () {
        $('body').addClass('pdp-modal-overlay');
        mobileDialog.show();
        mobileDialog.showModal();
      });
    }
  };
})(jQuery);
