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

      // Process main pdp gallery only once.
      var zoomContainer = $('.acq-content-product .content__main #product-zoom-container');
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
        mobilegallery.on('afterChange', function (event, slick) {
          // Hide Labels on video slides.
          Drupal.hideProductLabelOnVideo($(this), 'mobilegallery__thumbnails__video', true);
        });

        Drupal.productZoomApplyRtl(mobilegallery, slickMobileOptions, context);
        if (!mobilegallery.find('ul.slick-dots').hasClass('i-dots')) {
          // Do initial setup again for slick dots.
          Drupal.behaviors.pdpInstagranDots.initialSetup(mobilegallery);
          Drupal.attachBehaviors(context);
        }
        // Modal view on image click in desktop and tablet.
        // Modal view for PDP Slider, when clicking on main image.
        var element = $(zoomContainer.find('#product-full-screen-gallery-container'));

        // Open Gallery modal when we click on the zoom image.
        var myDialog = Drupal.dialog(element, dialogsettings);
        $('.acq-content-product .cloudzoom #cloud-zoom-wrap img').off().on('click', function () {
          $('body').addClass('pdp-modal-overlay');
          myDialog.show();
          myDialog.showModal();
        });

        // $(document).once() because we need the same functionality for free gifts pdp modal too and we are
        // using HtmlCommand to render the free gifts pdp (Check viewProduct() in FreeGiftController.php).
        $(document).once('dialog-opened').on('click','.dialog-product-image-gallery-container #product-full-screen-gallery li.slick-slide', function (e) {
          var productGallery = $('#product-full-screen-gallery', $(this).closest('.dialog-product-image-gallery-container'));
          // Closing modal window before slick library gets removed.
          $(this).closest('.dialog-product-image-gallery-container').find($('button.ui-dialog-titlebar-close')).trigger('mousedown');
          productGallery.slick('unslick');
          $('body').removeClass('pdp-modal-overlay');
          e.preventDefault();
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
          else {
            // Handle click on image thumbnails.
            var imageUrl = $(this).find('a.cloudzoom__thumbnails__image').attr('href');
            var zoomImageUrl = $(this).find('a.cloudzoom__thumbnails__image').attr('data-zoom-url');
            if (imageUrl !== null || imageUrl !== 'undefined') {
              $('#product-zoom-container #cloud-zoom-wrap .img-wrap img').attr('src', imageUrl)
              .parent().find('.product-image-zoom-placeholder-content').css({'background-image': 'url(' + zoomImageUrl + ')'});
            }
          }
          // Hide Product labels on video slides.
          Drupal.hideProductLabelOnVideo(lightSlider, 'cloudzoom__thumbnails__video', false);
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
          // Show Product labels on image slides.
          Drupal.hideProductLabelOnVideo(lightSlider, 'cloudzoom__thumbnails__video', false);
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

          // Show product labels.
          Drupal.hideProductLabelOnVideo(modalLightSlider, 'cloudzoom__thumbnails__video', false);
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
          // Hide product labels.
          Drupal.hideProductLabelOnVideo(modalLightSlider, 'cloudzoom__thumbnails__video', false);
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

      // Zoom effect on image hover for desktop.
      if ($(window).width() > 1025) {
        $('#product-zoom-container .img-wrap')
        .on('mouseover', function (){
          $(this).addClass('product-image-zoomed');
          $(this).find('.product-image-zoom-placeholder-content').css({'transform': 'scale('+ $(this).attr('data-scale') +')'});
        })
        .on('mouseout', function (){
          $(this).removeClass('product-image-zoomed');
          $(this).find('.product-image-zoom-placeholder-content').css({'transform': 'scale(1)'});
        })
        .on('mousemove', function (e){
          $(this).find('.product-image-zoom-placeholder-content').css({'transform-origin': ((e.pageX - $(this).offset().left) / $(this).width()) * 100 + '% ' + ((e.pageY - $(this).offset().top) / $(this).height()) * 100 +'%'});
        })
        .each(function (){
          $(this)
          .once('product-image-zoom-placeholder-appended')
          // Add a magazine image zoom placeholder.
          .append('<div class="product-image-zoom-placeholder"><div class="product-image-zoom-placeholder-content"></div></div>')
          // Set up a background image for each magazine image zoom placeholder based on data-src attribute.
          .children('.product-image-zoom-placeholder')
          // Binding click event to image zoom placeholder sibling.
          .on('click', function (){
            $(this).parent().find('img').trigger('click');
          })
          .children('.product-image-zoom-placeholder-content')
          .css({'background-image': 'url('+ $(this).find('img').attr('data-zoom-url') +')'});
          $(this).find('img').on('load', function () {
            var imgWidth = $(this).width();
            var containerWidth = $(this).parent().width();
            var leftPosition = (containerWidth - imgWidth)/2;
            $(this).parent().find('.product-image-zoom-placeholder').css({'width': imgWidth + 'px', 'left': leftPosition + 'px'})
          })
        })
      }
    }
  };

  $(window).once('toggleProductImageGallery').on('resize', function (e) {
    toggleProductImageGallery();
  });

  $(document).once('bind-slick-nav').on('click', '#product-zoom-container .slick-prev, #product-zoom-container .slick-next', function () {
    var slider = $(this).closest('.slick-slider');
    setTimeout(function () {
      var currentSlide = slider.find('li.slick-current');
      // If the new slide is video thubnail,
      // we trigger click on slide to render video.
      if (currentSlide.hasClass('cloudzoom__thumbnails__video') || currentSlide.hasClass('imagegallery__thumbnails__video')) {
        currentSlide.trigger('click');
      }
      else {
        slider.find('li.slick-current a').trigger('click');
      }
    }, 1);
  });

  /**
   * Use the beforeChange event of slick to pause videos when scrolling from
   * video slides.
   *
   * @param {object} slickSelector
   *   Slick slider selcetor.
   * @param {object} videoSlideSelector
   *   Slide slider slide selector for video slides.
   */
  function pauseVideos(slickSelector, videoSlideSelector) {
    slickSelector.once().on('beforeChange', function (event, slick) {
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
   * Get the vertical parameter for slick slider on the basis of the
   * drupalsetting image_slider_position_pdp.
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

    var gallery = $('#product-full-screen-gallery');
    slickModalOptions.currentSlide = currentSlide;
    Drupal.productZoomApplyRtl(gallery, slickModalOptions, document);
    // Create Instagram Dots.
    if (!gallery.find('ul.slick-dots').hasClass('i-dots')) {
      // Do initial setup again for slick dots.
      Drupal.behaviors.pdpInstagranDots.initialSetup(gallery);
      // Attach the change event explicitly.
      Drupal.behaviors.pdpInstagranDots.attachBeforeChange(gallery);
    }

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
      var productGallery = $('#product-full-screen-gallery', $(this).closest('.dialog-product-image-gallery-container'));
      // Closing modal window before slick library gets removed.
      $(this).click();
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

      $('li', gallery).each(function () {
        $(this).once('bind-js').on('click', function (e) {
          e.preventDefault();

          var index = $(this).attr('data-slick-index');
          if (gallery.slick('slickCurrentSlide') !== index) {
            gallery.slick('slickGoTo', index);
          }
          $(this).siblings('.slick-slide').removeClass('slick-current');
          $(this).addClass('slick-current');

          var li = $(this);
          img_scale = 1;
          $('.zoomin').removeClass('disabled');
          $('.zoomout').removeClass('disabled');

          // Make image draggable inside the window.
          $('#full-image').css({
            left: 0,
            top: 0
          });

          // Video Handling for PDP Modal.
          if (li.hasClass('youtube') || li.hasClass('vimeo')) {
            var href = $(this).attr('data-iframe');
            $('#full-image-wrapper').hide();
            $('.cloudzoom__video_modal').show();
            $('.cloudzoom__video_modal iframe').remove();
            appendVideoIframe($('.cloudzoom__video_modal'), href);
            // Hide zoom buttons when watching video.
            $(this).parents('.imagegallery__wrapper').siblings('.button__wrapper').hide();
          }
          else {
            var bigImage = $(this).find('a').attr('href');
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

  /**
   * Hide product labels on video slides in a slick slider.
   *
   * @param {*} gallery
   *   The slick slider. Preferrably with content.
   * @param {*} videoSlideClass
   *   The class on the slide to indetify a video slide.
   * @param {*} mobileGalleryFlag
   *   Boolean to indicate if slider is a mobile gallery
   */
  Drupal.hideProductLabelOnVideo = function (gallery, videoSlideClass, mobileGalleryFlag) {
    if (mobileGalleryFlag === true) {
      if (gallery.find('.slick-current').hasClass(videoSlideClass)) {
        gallery.siblings('.product-labels').hide();
      }
      else {
        gallery.siblings('.product-labels').show();
      }
    }
    else {
      if (gallery.find('.slick-current').hasClass(videoSlideClass)) {
        gallery.parents('.cloudzoom__thumbnails').siblings('.cloudzoom__herocontainer').find('.product-labels').hide();
      }
      else {
        gallery.parents('.cloudzoom__thumbnails').siblings('.cloudzoom__herocontainer').find('.product-labels').show();
      }
    }
  };

  // Slider - 3 For Mobile - Image Gallery.
  var slickMobileOptions = {
    slidesToShow: 1,
    vertical: false,
    dots: true,
    arrows: true,
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
    slidesToShow: 1,
    vertical: false,
    arrows: true,
    dots: true,
    infinite: false,
    centerMode: false,
    focusOnSelect: false,
    touchThreshold: 1000,
    responsive: [
      {
        breakpoint: 1025,
        settings: {
          slidesToShow: 1,
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
