/**
 * @file
 * Magazine Gallery
 */

/* global isRTL */

(function ($) {
  'use strict';
  Drupal.behaviors.magazine_gallery = {
    attach: function (context, settings) {

      var desktop_sidebar_width = $('.content__sidebar').width();
      $('#cloud-zoom-big').css('width', desktop_sidebar_width + 'px');

      var desktopElement = $('#product-image-gallery-container');
      var mobileElement = $('#product-image-gallery-container-mobile');

      // Open Gallery mobile modal when we click on the image.
      var desktopDialog = Drupal.dialog(desktopElement, desktopdialogsettings);
      var mobileDialog = Drupal.dialog(mobileElement, mobiledialogsettings);

      if ($(window).width() < 768) {
        $('#product-zoom-container .pdp-image a').on('click', function (e) {
          e.preventDefault()
        });

        $('.pdp-image').off().on('click', function () {
          $('body').addClass('pdp-modal-overlay');
          $(this).siblings('.clicked').removeClass('clicked');
          $(this).addClass('clicked');
          mobileDialog.show();
          mobileDialog.showModal();
        });
      }
      else {
        var items = $('.magazine__gallery--container .cloud-zoom:not(cloud-zoom-processed)');
        if (items.length) {
          items.addClass('cloud-zoom-processed').CloudZoom();
        }

        $('.pdp-image').off().on('click', function (e) {
          $('body').addClass('pdp-modal-overlay');
          $(this).siblings('.clicked').removeClass('clicked');
          $(this).addClass('clicked');
          desktopDialog.show();
          desktopDialog.showModal();
        });
      }
    }
  };

  /**
   * Zoom modal dialog.
   */
  function _magazine_dialog_open() {
    var gallery = $('#product-image-gallery');
    var currentSlide = $('.pdp-image.clicked').attr('data-image-index');
    slickModalOptions.initialSlide = currentSlide;
    Drupal.productZoomApplyRtl(gallery, slickModalOptions, document);

    if (gallery.hasClass('pager-no')) {
      $('li[data-slick-index="' + currentSlide + '"]', gallery).addClass('slick-current', function () {
        $(this).siblings().removeClass('slick-current');
      });
    }
    else {
      gallery.slick('slickGoTo', currentSlide);
    }

    var defaultMainImage = $('#product-image-gallery-container li[data-slick-index="'+ currentSlide +'"]');
    var bigImgUrl = defaultMainImage.children('a').attr('href');
    $('#full-image-wrapper img').attr('src', bigImgUrl);
    $('#full-image-wrapper img').css('transform', 'scale(1)');
    $('#full-image-wrapper iframe').remove();
    $('#full-image-wrapper img').show();

    $('.dialog-product-image-gallery-container button.ui-dialog-titlebar-close').on('mousedown', function () {
      var productGallery = $('#product-image-gallery', $(this).closest('.dialog-product-image-gallery-container'));
      productGallery.slick('unslick');
      $('body').removeClass('pdp-modal-overlay');
    });

    // ZoomIn ZoomOut in Gallery view with a draggable container.
    if ($('#full-image-wrapper').length > 0) {
      var maskWidth = $('#full-image-wrapper').width();
      var maskHeight = $('#full-image-wrapper').height();
      var imgPos = $('#full-image').offset();
      var imgWidth = $('#full-image').width();
      var imgHeight = $('#full-image').height();
      var x1 = (imgPos.left + maskWidth) - imgWidth;
      var y1 = (imgPos.top + maskHeight) - imgHeight;
      var x2 = imgPos.left;
      var y2 = imgPos.top;

      $('#full-image').css({
        left: 0,
        top: 0
      });

      // Make image draggable inside the window.
      var click = {x: 0, y: 0};
      $('#full-image').draggable({
        containment: [x1, y1, x2, y2],
        start: function (event) {
          click.x = event.clientX;
          click.y = event.clientY;
        },
        drag: function (event, ui) {
          // This is the parameter for scale().
          var matrix = image.css('transform').match(/-?[\d\.]+/g);
          var zoom = parseFloat(matrix[3]);
          var original = ui.originalPosition;
          // jQuery will simply use the same object we alter here.
          ui.position = {
            left: ((event.clientX - click.x + original.left) / zoom),
            top: (event.clientY - click.y + original.top) / zoom
          };
        }
      });

      // Zoom in and Zoom out buttons.
      var image = $('#full-image-wrapper img');
      var img_scale = 1;
      $('.zoomin').removeClass('disabled');
      $('.zoomout').removeClass('disabled');

      $('.zoomin').once('bind-js').on('click', function () {
        if (img_scale < 1.75) {
          img_scale = img_scale + 0.25;

          image.css('transform', 'scale(' + img_scale + ')');
          $('.zoomout').removeClass('disabled');
        }
        else {
          $(this).addClass('disabled');
        }

      });
      $('.zoomout').once('bind-js').on('click', function () {
        if (img_scale <= 1) {
          $(this).addClass('disabled');
          return;
        }
        else {
          img_scale = img_scale - 0.25;
          $('.zoomin').removeClass('disabled');
          image.css('transform', 'scale(' + img_scale + ')');
        }
      });

      $('li a', gallery).each(function () {
        $(this).once('bind-js').on('click', function (e) {
          e.preventDefault();

          var index = $(this).parent().attr('data-slick-index');
          if (gallery.slick('slickCurrentSlide') !== index) {
            gallery.slick('slickGoTo', index);
          }
          $(this).parent().siblings('.slick-slide').removeClass('slick-current');
          $(this).parent().addClass('slick-current');

          var li = $(this).closest('li');
          img_scale = 1;
          $('.zoomin').removeClass('disabled');
          $('.zoomout').removeClass('disabled');

          // Make image draggable inside the window.
          $('#full-image').css({
            left: 0,
            top: 0
          });

          // Video Handling for PDP Modal.
          if ($(li).hasClass('youtube') || $(li).hasClass('vimeo')) {
            var href = $(this).attr('data-iframe');
            $('#full-image-wrapper').hide();
            $('.cloudzoom__video_modal').show();
            $('.cloudzoom__video_modal iframe').remove();
            appendVideoIframe($('.cloudzoom__video_modal'), href);
            // Hide zoom buttons when watching video.
            $(this).parents('.imagegallery__wrapper').siblings('.button__wrapper').hide();
          }
          else {
            var bigImage = $(this).attr('href');
            // Put the big image in our main container.
            $('#full-image-wrapper img').attr('src', bigImage);
            $('#full-image-wrapper img').css('transform', 'scale(1)');
            $('.cloudzoom__video_modal iframe').remove();
            $('.cloudzoom__video_modal').hide();
            $(this).parents('.imagegallery__wrapper').siblings('.button__wrapper').show();
            $('#full-image-wrapper').show();
          }
        });
      });
    }
  }

  /**
   * Zoom modal dialog.
   */
  function _magazine_mobile_dialog_open() {
    var gallery = $('#product-image-gallery-mob');
    var currentSlide = $('.pdp-image.clicked').attr('data-image-index');
    slickMobileModalOptions.initialSlide = parseInt(currentSlide);
    Drupal.productZoomApplyRtl(gallery, slickMobileModalOptions, document);

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

  Drupal.productZoomApplyRtl = function (ocObject, options, context) {
    if (ocObject.length < 1) {
      return;
    }

    if (isRTL() && $(window).width() < 1025) {
      ocObject.attr('dir', 'rtl');
      ocObject.slick(
        $.extend({}, options, {rtl: true})
      );
    }
    else {
      // When Arabic and slider position is bottom, we need RTL support.
      if (isRTL() && options.vertical === false) {
        ocObject.attr('dir', 'rtl');
        ocObject.slick(
          $.extend({}, options, {rtl: true})
        );
      }
      else {
        ocObject.slick(options);
      }
    }

    if (context !== document) {
      ocObject.slick('resize');
    }
  };

  var slickMobileModalOptions = {
    slidesToShow: 1,
    vertical: false,
    dots: true,
    arrows: false,
    centerMode: false,
    infinite: false,
    focusOnSelect: true,
    touchThreshold: 5
  };

  var slickModalOptions = {
    slidesToShow: 3,
    vertical: true,
    arrows: true,
    infinite: false,
    centerMode: true,
    focusOnSelect: false,
    touchThreshold: 1000,
    responsive: [
      {
        breakpoint: 1025,
        settings: {
          slidesToShow: 5,
          vertical: false,
          touchThreshold: 1000,
          centerMode: false
        }
      }
    ]
  };

  var desktopdialogsettings = {
    autoOpen: true,
    width: 1024,
    height: 768,
    dialogClass: 'dialog-product-image-gallery-container',
    open: _magazine_dialog_open
  };

  var mobiledialogsettings = {
    autoOpen: true,
    width: 1024,
    height: 768,
    dialogClass: 'dialog-product-image-gallery-container-mobile',
    open: _magazine_mobile_dialog_open
  };
})(jQuery);
