/**
 * @file
 * Product Zoom Gallery.
 */

/* global isRTL */

(function ($) {
  'use strict';
  Drupal.behaviors.alshaya_product_mobile_zoom = {
    attach: function (context, settings) {
      // //////////////////////////////////////////////////////////////////////////////////////////////////////////////
      // Modal view on image click in mobile.
      // //////////////////////////////////////////////////////////////////////////////////////////////////////////////
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
          var currentSlide;
          if ($('#lightSlider').hasClass('pager-yes')) {
            currentSlide = $('#lightSlider').slick('slickCurrentSlide');
          }
          else {
            currentSlide = $('#lightSlider .slick-current').attr('data-slick-index');
          }

          var slickModalOptions = {
            slidesToShow: 1,
            vertical: false,
            dots: true,
            arrows: false,
            centerMode: false,
            infinite: false,
            focusOnSelect: false,
            initialSlide: currentSlide
          };

          var gallery = $('#product-image-gallery-mob');
          applyRtl(gallery, slickModalOptions);

          if ($('#product-image-gallery-mobile').hasClass('pager-no')) {
            $('#product-image-gallery-mob li[data-slick-index="' + currentSlide + '"]').addClass('slick-current', function () {
              $(this).siblings().removeClass('slick-current')
            });
          }

          var zoomimage = document.querySelector('#product-image-gallery-mob .mob-imagegallery__thumbnails__image.slick-current img');
          var hammer = new Hammer.Manager(zoomimage);
          // hammer.get('pinch').set({ enable: true });
          // Create a recognizer
          var DoubleTap = new Hammer.Tap({
            event: 'doubletap',
            taps: 2
          });

          // Add the recognizer to the manager
          hammer.add(DoubleTap);

          // Subscribe to desired event
          hammer.on('doubletap', function(e) {
            e.target.classList.toggle('expand');
          });

          // Zoom in and Zoom out buttons.
          var image = $('#product-image-gallery-mob .mob-imagegallery__thumbnails__image.slick-current img');
          var img_scale = 1;
          image.css('transform', 'scale(1)');
          $('.zoomin').removeClass('disabled');
          $('.zoomout').removeClass('disabled');

          $('.zoomin').on('click', function () {
            if(img_scale < 1.75) {
              img_scale = img_scale + 0.25;

              image.css('transform', 'scale(' + img_scale + ')');
              $('.zoomout').removeClass('disabled');
            }
            else {
              $(this).addClass('disabled');
            }

          });
          $('.zoomout').on('click', function () {
            if (img_scale <= 1) {
              $(this).addClass('disabled');
              return;
            } else {
              img_scale = img_scale - 0.25;
              $('.zoomin').removeClass('disabled');
              image.css('transform', 'scale(' + img_scale + ')');
            }
          });

          $('#product-image-gallery-mob').on('swipe', function (event, slick) {
            var image = $(this).find('.mob-imagegallery__thumbnails__image[data-slick-index="'+ slick.currentSlide +'"] img');
            var zoomimage = document.querySelector('#product-image-gallery-mob .mob-imagegallery__thumbnails__image[data-slick-index="'+ slick.currentSlide +'"] img');
            var hammer = new Hammer.Manager(zoomimage);
            // Create a recognizer
            var DoubleTap = new Hammer.Tap({
              event: 'doubletap',
              taps: 2
            });

            // Add the recognizer to the manager
            hammer.add(DoubleTap);

            // Subscribe to desired event
            hammer.on('doubletap', function(e) {
              e.target.classList.toggle('expand');
            });

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
