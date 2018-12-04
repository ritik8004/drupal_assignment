/**
 * @file
 * Product Zoom Gallery.
 */

/* global isRTL */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.alshaya_product_zoom = {
    attach: function (context, settings) {
      // Remove unwanted attributes in slider for users.
      $('.gallery-wrapper #cloud-zoom img').removeAttr('title');
      $('.gallery-wrapper #cloud-zoom img').removeAttr('alt');

      // Initialize Product Zoom using CloudZoom library.
      // Initialize lightSliders.
      var items = $('.acq-content-product .cloud-zoom:not(cloud-zoom-processed), .acq-content-product .cloudzoom__thumbnails__image:not(cloud-zoom-processed)');
      if (items.length) {
        items.addClass('cloud-zoom-processed').once('bind-events').CloudZoom();
      }

      // Process main pdp gallery only once.
      var zoomContainer = $('.acq-content-product #product-zoom-container');
      if (zoomContainer.length > 0 && !zoomContainer.hasClass('product-zoom-processed')) {
        zoomContainer.addClass('product-zoom-processed');

        var lightSlider = $('.acq-content-product #lightSlider');
        Drupal.productZoomApplyRtl(lightSlider, slickOptions, context);
        // Adding class if there is no slider.
        addPagerClass();

        // If there is only one thumbnail and that is video.
        if ($('li', lightSlider).length == 1 && $('li', lightSlider).hasClass('cloudzoom__thumbnails__video')) {
          var video_url = $('li', lightSlider).attr('data-iframe');
          appendVideoIframe($('.acq-content-product .cloudzoom__video_main'), video_url);
          // Hiding the main image container to correct position of video iframe.
          $('.acq-content-product #cloud-zoom-wrap').hide();
        }

        var mobilegallery = $('#product-image-gallery-mobile', context);
        Drupal.productZoomApplyRtl(mobilegallery, slickMobileOptions, context);

        // Modal view on image click in desktop and tablet.
        // Modal view for PDP Slider, when clicking on main image.
        var element = $(zoomContainer.find('#product-image-gallery-container'));

        // Open Gallery modal when we click on the zoom image.
        var myDialog = Drupal.dialog(element, dialogsettings);
        $('.acq-content-product .cloudzoom #cloud-zoom-wrap').off().on('click', function () {
          $('body').addClass('pdp-modal-overlay');
          myDialog.show();
          myDialog.showModal();
        });

        // Videos inside main PDP slider.
        // For Desktop slider, we add a iframe on click on the image.
        $('li', lightSlider).once('bind-js').on('click', function (e) {
          if ($(this).hasClass('cloudzoom__thumbnails__video')) {
            var URL = $(this).attr('data-iframe');
            $('.acq-content-product .cloudzoom__video_main iframe').remove();
            appendVideoIframe($('.acq-content-product .cloudzoom__video_main'), URL);
            $('.acq-content-product #cloud-zoom-wrap').hide();
            $(this).siblings('.slick-slide').removeClass('slick-current');
            $(this).addClass('slick-current');
          }
        });

        // For Desktop slider, we remove the video iframe if user clicks on image thumbnail..
        $('li a.cloudzoom__thumbnails__image', lightSlider).once('bind-js-img').on('click', function () {
          var playerIframe = $('.acq-content-product .cloudzoom__video_main iframe');
          // Check if there is a youtube video playing, if yes stop it and destroy the iframe.
          if (playerIframe.length > 0) {
            playerIframe.remove();
            $('.acq-content-product #cloud-zoom-wrap').show();
          }
        });

        $('li a', lightSlider).once('bind-js').on('click', function (e) {
          e.preventDefault();
          var index = $(this).parent().attr('data-slick-index');
          if (lightSlider.slick('slickCurrentSlide') !== index) {
            lightSlider.slick('slickGoTo', index);
          }
          $(this).parent().siblings('.slick-slide').removeClass('slick-current');
          $(this).parent().addClass('slick-current');
        });
      }

      // Add mobile slick options for cart page free gifts.
      var freeGiftsZoomContainer = $('.acq-content-product-modal #product-zoom-container');
      if ($(window).width() < 768 && freeGiftsZoomContainer.length > 0 && !freeGiftsZoomContainer.hasClass('free-gifts-product-zoom-processed')) {
        freeGiftsZoomContainer.addClass('free-gifts-product-zoom-processed');
        var mobilegallery = $('#product-image-gallery-mobile', context);
        Drupal.productZoomApplyRtl(mobilegallery, slickMobileOptions, context);
      }

      var modalLightSlider = $('.acq-content-product-modal #lightSlider');
      if (modalLightSlider.length > 0 && !modalLightSlider.hasClass('product-zoom-processed')) {
        modalLightSlider.addClass('product-zoom-processed');
        Drupal.productZoomApplyRtl(modalLightSlider, slickCSUSOptions, context);

        $('li a', modalLightSlider).once('bind-js-modal').off('click').on('click', function (e) {
          e.preventDefault();
          e.stopPropagation();

          $(this).parent().siblings('.slick-slide').removeClass('slick-current');
          $(this).parent().addClass('slick-current');
          var bigImage = $(this).attr('href');
          // Put the big image in our main container.
          $('.acq-content-product-modal #cloud-zoom-wrap img').attr('src', bigImage);
          $('.acq-content-product-modal #cloud-zoom-wrap img').css('transform', 'scale(1)');
          $('.acq-content-product-modal .cloudzoom__video_main iframe').remove();
          $('.acq-content-product-modal #cloud-zoom-wrap').show();
        });

        $('li', modalLightSlider).once('bind-js').on('click', function () {
          if ($(this).hasClass('cloudzoom__thumbnails__video')) {
            var URL = $(this).attr('data-iframe');
            $('.acq-content-product-modal .cloudzoom__video_main iframe').remove();
            appendVideoIframe($('.acq-content-product-modal .cloudzoom__video_main'), URL);
            $('.acq-content-product-modal #cloud-zoom-wrap').hide();
            $(this).siblings('.slick-slide').removeClass('slick-current');
            $(this).addClass('slick-current');
          }
        });
      }

      // Stop video playback if slide is changed.
      pauseVideos($('#product-image-gallery-mobile'), 'mobilegallery__thumbnails__video');
      pauseVideos($('#product-image-gallery-mob'), 'mob-imagegallery__thumbnails__video');

      // Preventing click on image.
      $('#cloud-zoom-wrap a').once('bind-js').on('click', function (event) {
        event.stopPropagation();
        event.preventDefault();
      });

      // Show mobile slider only on mobile resolution.
      toggleProductImageGallery();
    }
  };

  $(window).once('toggleProductImageGallery').on('resize', function (e) {
    toggleProductImageGallery();
  });

  $(document).once('bind-slick-nav').on('click', '.slick-prev, .slick-next', function () {
    var slider = $(this).closest('.slick-slider');

    setTimeout(function () {
      var currentSlide = slider.find('li.slick-current');
      // If the new slide is video thubnail,
      // we trigger click on slide to render video.
      if (currentSlide.hasClass('cloudzoom__thumbnails__video')) {
        currentSlide.trigger('click');
      }
      else {
        slider.find('li.slick-current a').trigger('click');
      }
    }, 1);
  });

  /**
   * Use the beforeChange event of slick to pause videos when scrolling from video slides.
   *
   * @param {object} slickSelector
   *   Slick slider selcetor.
   * @param {object} videoSlideSelector
   *   Slide slider slide selector for video slides.
   */
  function pauseVideos(slickSelector, videoSlideSelector) {
    slickSelector.on('beforeChange', function (event, slick) {
      var currentSlide;
      var slideType;
      var player;
      var command;

      // Find the current slide element and decide which player API we need to use.
      currentSlide = $(slick.$slider).find('.slick-current');
      if (currentSlide.hasClass(videoSlideSelector)) {
        // Determine which type of slide this is.
        slideType = currentSlide.hasClass('vimeo') === true ? 'vimeo' : 'youtube';
        // Get the iframe inside this slide.
        player = currentSlide.find('iframe').get(0);
        if (slideType === 'vimeo') {
          command = {
            method: 'pause',
            value: 'true'
          };
        }
        else {
          command = {
            event: 'command',
            func: 'pauseVideo'
          };
        }
        // Check if the player exists.
        if (player !== 'undefined') {
          // Post our command to the iframe.
          player.contentWindow.postMessage(JSON.stringify(command), '*');
        }
      }
    });
  }

  /**
   * Toggles the product gallery based on screen width [between tab and mobile].
   */
  function toggleProductImageGallery() {
    if ($(window).width() < 768) {
      $('.mobilegallery').show();
      $('.cloudzoom').hide();
    }
    else {
      $('.mobilegallery').hide();
      $('.cloudzoom').show();
    }
  }

  /**
   * Appends iframe tag in the element that is passed.
   *
   * @param {object} element
   *   The HTML element inside which we want iframe.
   * @param {string} href
   *   The URL of video.
   */
  function appendVideoIframe(element, href) {
    element.append('<iframe id="player" src="' + href
      + '" frameborder="0" style="position:absolute;top:0;left:0;width:100%;height:100%;" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>');
  }

  /**
   * Get the vertical parameter for slick slider on the basis of the drupalsetting image_slider_position_pdp.
   *
   * Get the slidesToShow parameter for slick slider on the basis of the
   * drupalsetting pdp_slider_items.
   *
   * @return {boolean} vertical
   *   The vertical paramerter for slick slider.
   *
   * @return {integer} slidesToShow
   *   The slidesToShow paramerter for slick slider.
   *
   * @param {string} slick_slider_setting
   *   The setting of slick slider.
   */
  function getPDPSliderParameter(slick_slider_setting) {
    if (slick_slider_setting === 'vertical') {
      var pdp_slider_position = drupalSettings.alshaya_white_label.image_slider_position_pdp;
      return !(pdp_slider_position === 'slider-position-bottom');
    }

    else if (slick_slider_setting === 'slidesToShow') {
      var pdp_slider_items = drupalSettings.pdp_slider_items;
      return pdp_slider_items;
    }

    else if (slick_slider_setting === 'slidesToShowCSUS') {
      var pdp_slider_items_cs_us = drupalSettings.pdp_slider_items_cs_us;
      return pdp_slider_items_cs_us;
    }
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

  /**
   * Zoom modal dialog.
   */
  function _product_zoom_dialog_open() {
    var currentSlide;
    var lightSlider = $('.acq-content-product #lightSlider');
    if (lightSlider.hasClass('pager-yes') && getPDPSliderParameter('vertical')) {
      currentSlide = lightSlider.slick('slickCurrentSlide');
    }
    else {
      currentSlide = $('.slick-current', lightSlider).attr('data-slick-index');
    }

    var gallery = $('#product-image-gallery');
    slickModalOptions.currentSlide = currentSlide;
    Drupal.productZoomApplyRtl(gallery, slickModalOptions, document);

    if (gallery.hasClass('pager-no')) {
      $('li[data-slick-index="' + currentSlide + '"]', gallery).addClass('slick-current', function () {
        $(this).siblings().removeClass('slick-current');
      });
    }
    else {
      gallery.slick('slickGoTo', currentSlide);
    }

    var defaultMainImage = $('#product-image-gallery-container li[data-slick-index="' + currentSlide + '"]');
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
   * Add Pager class for tablets.
   */
  function addPagerClass() {
    if ($(window).width() < 1025) {
      $('#lightSlider').once('pager-class').each(function () {
        $(this).removeClass('pager-yes');
        $(this).removeClass('pager-no');

        if ($(this).find('.slick-track > li').length < 4) {
          $(this).addClass('pager-no');
        }
        else {
          $(this).addClass('pager-yes');
        }
      });
    }
  }

  // Slider - 3 For Mobile - Image Gallery.
  var slickMobileOptions = {
    slidesToShow: 1,
    vertical: false,
    dots: true,
    centerMode: false,
    infinite: false,
    focusOnSelect: true,
    touchThreshold: 1000,
    initialSlide: 0
  };

  var slickOptions = {
    slidesToShow: getPDPSliderParameter('slidesToShow'),
    vertical: getPDPSliderParameter('vertical'),
    arrows: true,
    focusOnSelect: false,
    centerMode: getPDPSliderParameter('vertical'),
    infinite: false,
    touchThreshold: 1000,
    responsive: [
      {
        breakpoint: 1025,
        settings: {
          slidesToShow: 3,
          touchThreshold: 1000,
          vertical: false,
          centerMode: false
        }
      }
    ]
  };

  var slickCSUSOptions = {
    slidesToShow: getPDPSliderParameter('slidesToShowCSUS'),
    vertical: getPDPSliderParameter('vertical'),
    arrows: true,
    focusOnSelect: false,
    centerMode: getPDPSliderParameter('vertical'),
    infinite: false,
    touchThreshold: 1000,
    responsive: [
      {
        breakpoint: 1025,
        settings: {
          slidesToShow: 3,
          touchThreshold: 1000,
          vertical: false,
          centerMode: false
        }
      }
    ]
  };

  var slickModalOptions = {
    slidesToShow: getPDPSliderParameter('slidesToShow'),
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

  var dialogsettings = {
    autoOpen: true,
    // Change dimensions of modal window as per theme needs.
    width: 1024,
    height: 768,
    dialogClass: 'dialog-product-image-gallery-container',
    open: _product_zoom_dialog_open
  };

})(jQuery, Drupal, drupalSettings);
