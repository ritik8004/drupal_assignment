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
  hammertime.get('pan').set({
    enable: true
  });
  hammertime.get('doubletap').set({
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
    // Handling for double tap event for zooming image 2x.
    if (ev.type === 'doubletap') {
      var currentTransform = window.getComputedStyle(el, null).getPropertyValue('-webkit-transform').toString();
      // No zoom applied, so add zoom, this case happens when 1st attempt.
      if (currentTransform === 'none') {
        transform =
          'translate3d(0, 0, 0) ' +
          'scale3d(2, 2, 1) ';
      }
      // Transform exists so zoom active, in this case reset.
      else if (currentTransform !== 'matrix(1, 0, 0, 1, 0, 0)' && currentTransform !== 'matrix(0.999, 0, 0, 0.999, 0, 0)') {
        transform =
          'translate3d(0, 0, 0) ' +
          'scale3d(1, 1, 1) ';
      }
      // Apply zoom on double tap.
      else {
        transform =
          'translate3d(0, 0, 0) ' +
          'scale3d(2, 2, 1) ';
      }

      el.style.webkitTransform = transform;
      transform = '';
      return;
    }

    // Pan
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

    // Pinch
    if (ev.type === 'pinch') {
      scale = Math.max(.999, Math.min(last_scale * (ev.scale), 4));
    }
    if (ev.type === 'pinchend') {
      last_scale = scale;
    }

    // Panend
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

      $('#product-image-gallery-container-mobile').once('js-event').each(function () {
        var element = document.getElementById('product-image-gallery-container-mobile');
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
              touchThreshold: 1000
            };

            var gallery = $('#product-image-gallery-mob');
            applyRtl(gallery, slickModalOptions);

            $('.mob-imagegallery__wrapper .subtext').show().delay(5000).fadeOut();
            var mImages = Array.prototype.slice.call(document.querySelectorAll('.mob-imagegallery__thumbnails__image img'));
            mImages.forEach(function (ele) {
              hammerIt(ele);
            });

            $('.dialog-product-image-gallery-container-mobile button.ui-dialog-titlebar-close').on('mousedown', function () {
              var productGallery = $('#product-image-gallery-mob', $(this).closest('.dialog-product-image-gallery-container-mobile'));
              productGallery.slick('unslick');
              $('body').removeClass('pdp-modal-overlay');
              var image = $('#product-image-gallery-mob').find('.mob-imagegallery__thumbnails__image img');
              image.parent().siblings().each(function () {
                $('#product-image-gallery-mob').find('img').css('transform', 'none');
              });
            });

          }
        };

        // Open Gallery modal when we click on the zoom image.
        var mobileDialog = Drupal.dialog(element, dialogsettings);

        $('#product-image-gallery-mobile .slick-slide').off().on('click', function () {
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
