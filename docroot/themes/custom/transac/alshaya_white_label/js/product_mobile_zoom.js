/**
 * @file
 * Product Zoom Gallery.
 */

/* global isRTL */
/* global Hammer */

function hammerIt(elm) {
  'use strict';

  var hammertime = new Hammer(elm, {});
  hammertime.get('pinch').set({
    enable: true
  });
  var posX = 0;
  var posY = 0;
  var scale = 1;
  var last_scale = 1;
  var last_posX = 0;
  var last_posY = 0;
  var max_pos_x = 0;
  var max_pos_y = 0;
  var transform = '';
  var el = elm;

  hammertime.on('doubletap pan pinch panend pinchend', function (ev) {
    if (ev.type === 'doubletap') {
      transform =
          'translate3d(0, 0, 0) ' +
          'scale3d(2, 2, 1) ';
      scale = 2;
      last_scale = 2;
      try {
        if (window.getComputedStyle(el, null).getPropertyValue('-webkit-transform').toString() !== 'matrix(1, 0, 0, 1, 0, 0)') {
          transform =
              'translate3d(0, 0, 0) ' +
              'scale3d(1, 1, 1) ';
          scale = 1;
          last_scale = 1;
        }
      }
      catch (err) {
        throw (err);
      }
      el.style.webkitTransform = transform;
      transform = '';
    }

    // pan
    if (scale !== 1) {
      posX = last_posX + ev.deltaX;
      posY = last_posY + ev.deltaY;
      max_pos_x = Math.ceil((scale - 1) * el.clientWidth / 2);
      max_pos_y = Math.ceil((scale - 1) * el.clientHeight / 2);
      if (posX > max_pos_x) {
        posX = max_pos_x;
      }
      if (posX < -max_pos_x) {
        posX = -max_pos_x;
      }
      if (posY > max_pos_y) {
        posY = max_pos_y;
      }
      if (posY < -max_pos_y) {
        posY = -max_pos_y;
      }
    }


    // pinch
    if (ev.type === 'pinch') {
      scale = Math.max(.999, Math.min(last_scale * (ev.scale), 4));
    }
    if (ev.type === 'pinchend') {
      last_scale = scale;
    }

    // panend
    if (ev.type === 'panend') {
      last_posX = posX < max_pos_x ? posX : max_pos_x;
      last_posY = posY < max_pos_y ? posY : max_pos_y;
    }

    if (scale !== 1) {
      transform =
          'translate3d(' + posX + 'px,' + posY + 'px, 0) ' +
          'scale3d(' + scale + ', ' + scale + ', 1)';
    }

    if (transform) {
      el.style.webkitTransform = transform;
    }
  });
}

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

          $('#product-image-gallery-mob').on('swipe', function (event, slick) {
            var image = $(this).find('.mob-imagegallery__thumbnails__image[data-slick-index="' + slick.currentSlide + '"] img');
            image.parent().siblings().each(function () {
              $(this).find('img').css('transform', 'scale(1)');
            });
            hammerIt(document.querySelector('.mob-imagegallery__thumbnails__image[data-slick-index="' + slick.currentSlide + '"]'));
          });

          $('.dialog-product-image-gallery-container-mobile button.ui-dialog-titlebar-close').on('mousedown', function () {
            var productGallery = $('#product-image-gallery-mob', $(this).closest('.dialog-product-image-gallery-container-mobile'));
            productGallery.slick('unslick');
            $('body').removeClass('pdp-modal-overlay');
            var image = $('#product-image-gallery-mob').find('.mob-imagegallery__thumbnails__image img');
            image.parent().siblings().each(function () {
              $('#product-image-gallery-mob').find('img').css('transform', 'scale(1)');
            });
          });

        }
      };
      // Open Gallery modal when we click on the zoom image.
      var mobileDialog = Drupal.dialog(element, dialogsettings);
      $('#product-image-gallery-mobile .slick-slide').off().on('click', function () {
        $('body').addClass('pdp-modal-overlay');
        mobileDialog.show();
        mobileDialog.showModal();
      });
    }
  };
})(jQuery);
