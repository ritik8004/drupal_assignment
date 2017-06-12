(function ($) {
  'use strict';
  Drupal.behaviors.alshaya_product_zoom = {
    attach: function (context, settings) {
      // Remove unwanted attributes in slider for users.
      $('.gallery-wrapper #cloud-zoom img').removeAttr('title');
      $('.gallery-wrapper #cloud-zoom img').removeAttr('alt');

      // //////////////////////////////////////////////////////////////////////////////////////////////////////////////
      // Initialize Product Zoom using CloudZoom library.
      // Initialize lightSliders.
      // //////////////////////////////////////////////////////////////////////////////////////////////////////////////


      var items = $('.acq-content-product .cloud-zoom:not(cloud-zoom-processed), .acq-content-product .cloudzoom__thumbnails__image:not(cloud-zoom-processed)');
      if (items.length) {
        items.addClass('cloud-zoom-processed', context).CloudZoom();
      }

      // Slider 1 - For Desktop - Image zoom.
      if ($(window).width() < 1025) {
        $('#lightSlider', context).lightSlider({
          vertical: false,
          item: 3,
          verticalHeight: 100
        });
        // Slider 1 - For Desktop - Image zoom.
        $('#drupal-modal #lightSlider', context).lightSlider({
          vertical: false,
          item: 3,
          loop: true,
          verticalHeight: 100
        });
      }
      else {
        $('#lightSlider', context).lightSlider({
          vertical: true,
          item: 5,
          loop: true,
          verticalHeight: 440
        });
        // Slider 1 - For Desktop - Image zoom.
        $('#drupal-modal #lightSlider', context).lightSlider({
          vertical: true,
          item: 5,
          loop: true,
          verticalHeight: 440
        });
      }

      // Slider - 3 For Mobile - Image Gallery.
      $('#product-image-gallery-mobile', context).lightSlider({
        item: 1,
        onAfterSlide: function (el) {
          el.children('iframe').remove();
        }
      });
      // Show mobile slider only on mobile resolution.
      toggleProductImageGallery();
      $(window).on('resize', function (e) {
        toggleProductImageGallery();
      });

      // //////////////////////////////////////////////////////////////////////////////////////////////////////////////
      // Modal view on image click in desktop and tablet.
      // //////////////////////////////////////////////////////////////////////////////////////////////////////////////
      // Modal view for Slider-2 when clicking on big image - Image Gallery.
      var element = document.getElementById('product-image-gallery-container');
      var dialogsettings = {
        autoOpen: true,
        // Change dimensions of modal window as per theme needs.
        width: 1024,
        height: 768,
        dialogClass: 'dialog-product-image-gallery-container',
        open: function () {

          if ($(window).width() < 1025) {
            $('#product-image-gallery', context).lightSlider({
              vertical: false,
              item: 5,
              loop: true,
              verticalHeight: 100
            }).refresh();
          } else {
            $('#product-image-gallery', context).lightSlider({
              vertical: true,
              item: 5,
              loop: true,
              verticalHeight: 440
            });
          }

          // ZoomIn ZoomOut in Gallery view with a draggable container.
          if ($('#full-image-wrapper').length > 0) {
            $('#full-image').css({top: 0, left: -200});

            var maskWidth = $('#full-image-wrapper').width();
            var maskHeight = $('#full-image-wrapper').height();
            var imgPos = $('#full-image').offset();
            var imgWidth = $('#full-image').width();
            var imgHeight = $('#full-image').height();
            var x1 = (imgPos.left + maskWidth) - imgWidth;
            var y1 = (imgPos.top + maskHeight) - imgHeight;
            var x2 = imgPos.left;
            var y2 = imgPos.top;

            // Make image draggable inside the window.
            var click = {x: 0, y: 0};
            $('#full-image').draggable({
              containment: [x1, y1, x2, y2],
              start: function(event) {
                click.x = event.clientX;
                click.y = event.clientY;
              },
              drag: function(event, ui) {
                // This is the parameter for scale().
                var matrix = image.css('transform').match(/-?[\d\.]+/g);
                var zoom = parseFloat(matrix[3]);
                var original = ui.originalPosition;
                // jQuery will simply use the same object we alter here.
                ui.position = {
                  left: ((event.clientX - click.x + original.left) / zoom),
                  top:  (event.clientY - click.y + original.top ) / zoom
                };
              }
            });
            // Zoom in and Zoom out buttons.
            var image = $('#full-image-wrapper img');
            var img_scale = 1;
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

            // Swap the big image inside slider-2 when clicking on thumbnail.
            $('#product-image-gallery li', context).on('click', function () {
              if ($(this).hasClass('youtube') || $(this).hasClass('vimeo')) {
                var href = $(this).attr('data-iframe');
                $('#full-image-wrapper img').hide();
                $('#full-image-wrapper iframe').remove();
                appendVideoIframe($('#full-image-wrapper'), href, 480, 480);
              }
              else {
                var bigImage = $(this).children('a').attr('href');
                // Put the big image in our main container.
                $('#full-image-wrapper img').attr('src', bigImage);
                $('#full-image-wrapper img').css('transform', 'scale(1)');
                $('#full-image-wrapper iframe').remove();
                $('#full-image-wrapper img').show();
              }
              // Stop the browser from loading the image in a new tab.
              return false;
            });
          }
        }
      };
      // Open Gallery modal when we click on the zoom image.
      var myDialog = Drupal.dialog(element, dialogsettings);
      $('.acq-content-product .cloudzoom #cloud-zoom-wrap').on('click', function () {
        myDialog.show();
        myDialog.showModal();
      });

      // //////////////////////////////////////////////////////////////////////////////////////////////////////////////
      // Handling videos inside sliders.
      // Swapping the conatiners or Inserting video iframes inside containers on click of video thumbnails.
      // //////////////////////////////////////////////////////////////////////////////////////////////////////////////

      // Fetch Vimeo thumbnail via a GET call. Vimeo doesnot give thumbnails via URL like YT.
      // @TODO: Can we do this in PHP?
      $('#lightSlider li.cloudzoom__thumbnails__video.vimeo, #product-image-gallery li.vimeo, #product-image-gallery-mobile li.vimeo').each(function () {
        var vimeoVideoUrl = $(this).attr('data-iframe');
        var match = /vimeo.*\/(\d+)/i.exec(vimeoVideoUrl);
        var self = $(this);
        if (match) {
          var vimeoVideoID = match[1];
          $.getJSON('https://www.vimeo.com/api/v2/video/' + vimeoVideoID + '.json?callback=?', {format: 'json'}, function (data) {
            var featuredImg = data[0].thumbnail_large;
            self.find('img').attr('src', featuredImg);
          });
        }
      });

      // Support Youtube & Vimeo videos in slider.
      // For Mobile slider we only insert, no need to remove it.
      $('#product-image-gallery-mobile li', context).on('click', function () {
        if ($(this).hasClass('youtube') || $(this).hasClass('vimeo')) {
          var href = $(this).attr('data-iframe');
          $(this).children('img').hide();
          $(this).children('iframe').remove();
          appendVideoIframe($(this), href, 320, 320);
        }
      });
      // For Desktop slider, we add a iframe on click on the image.
      $('#lightSlider li', context).on('click', function () {
        if ($(this).hasClass('cloudzoom__thumbnails__video')) {
          var wrap = $('#cloud-zoom-wrap');
          // Get width & height of wrap.
          var width = wrap.width();
          var height = wrap.height();
          var URL = $(this).attr('data-iframe');
          $('#yt-vi-container iframe').remove();
          appendVideoIframe($('#yt-vi-container'), URL, width, height);
          $('#cloud-zoom-wrap').hide();
          $(this).siblings('.lslide').removeClass('active');
          $(this).addClass('active');
        }
      });

      $('#lightSlider li a', context).on('click', function (e) {
        e.preventDefault();
        if ($(this).hasClass('cloudzoom__thumbnails__image')) {
          $(this).parent().siblings('.lslide').removeClass('active');
          $(this).parent().addClass('active');
        }
      });

      $('.acq-content-product-modal #lightSlider li', context).on('click', function () {
        if ($(this).hasClass('cloudzoom__thumbnails__video')) {
          var wrap = $('.acq-content-product-modal #cloud-zoom-wrap');
          // Get width & height of wrap.
          var width = wrap.width();
          var height = wrap.height();
          var URL = $(this).attr('data-iframe');
          $('.acq-content-product-modal #yt-vi-container iframe').remove();
          appendVideoIframe($('.acq-content-product-modal #yt-vi-container'), URL, width, height);
          $('#cloud-zoom-wrap').hide();
        }
        else {
          var bigImage = $(this).children('a').attr('href');
          // Put the big image in our main container.
          $('.acq-content-product-modal #cloud-zoom-wrap img').attr('src', bigImage);
          $('.acq-content-product-modal #cloud-zoom-wrap img').css('transform', 'scale(1)');
          $('.acq-content-product-modal #cloud-zoom-wrap iframe').remove();
          $('.acq-content-product-modal #cloud-zoom-wrap img').show();
        }
        // Stop the browser from loading the image in a new tab.
        return false;
      });

      $('#product-image-gallery li img', context).on('click', function () {
        if ($(this).parent().hasClass('imagegallery__thumbnails__image')) {
          $(this).parent().parent().siblings('.lslide').removeClass('active');
          $(this).parent().parent().addClass('active');
        }
      });
      // For Desktop slider, we remove the iframe when we want to zoom another image.
      $('#lightSlider li a.cloudzoom__thumbnails__image', context).on('click', function () {
        var playerIframe = $('#yt-vi-container iframe');
        // Check if there is a youtube video playing, if yes stop it and destroy the iframe.
        if (playerIframe.length > 0) {
          playerIframe.remove();
          $('#cloud-zoom-wrap').show();
        }
      });

      // //////////////////////////////////////////////////////////////////////////////////////////////////////////////
      // Helper functions.
      // //////////////////////////////////////////////////////////////////////////////////////////////////////////////

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
       * @param {number} width
       *   The width of iframe/video.
       * @param {number} height
       *   The height of the iframe/video.
       */
      function appendVideoIframe(element, href, width, height) {
        element.append('<iframe id="player" width="' + width + '" height="' + height + '" src="' + href
          + '" frameborder="0" allowfullscreen></iframe>');
      }
    }
  };
})(jQuery);
