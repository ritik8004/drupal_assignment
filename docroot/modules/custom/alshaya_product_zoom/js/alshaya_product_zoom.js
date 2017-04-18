(function ($) {
  Drupal.behaviors.alshaya_product_zoom = {
    attach: function (context, settings) {
      ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
      // Initialize Product Zoom using CloudZoom library.
      // Initialize lightSliders.
      ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
      items = $('.cloud-zoom:not(cloud-zoom-processed), .cloud-zoom-gallery:not(cloud-zoom-processed)', context);
      if (items.length) {
        items.addClass('cloud-zoom-processed').CloudZoom();
        items.parent().css('float', 'left');
      }
      // Slider 1 - For Desktop - Image zoom.
      $("#lightSlider").lightSlider({
        vertical: true,
        item: 5,
        verticalHeight: 500,
      });
      // Slider - 2 For Desktop - Image Gallery.
      $("#product-image-gallery").lightSlider({
        vertical: true,
        item: 5,
        verticalHeight: 500,
      });
      // Slider - 3 For Mobile - Image Gallery.
      $("#product-image-gallery-mobile").lightSlider({
        item: 1,
        onAfterSlide: function (el) {
          el.children('iframe').remove();
        },
      });
      // Show mobile slider only on mobile resolution.
      toggleProductImageGallery();
      $(window).on("resize", function (e) {
        toggleProductImageGallery();
      });

      ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
      // Modal view on image click in desktop and tablet.
      ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
      // Modal view for Slider-2 when clicking on big image - Image Gallery.
      var element = document.getElementById('product-image-gallery-container');
      var dialogsettings = {
        autoOpen: true,
        // Change dimensions of modal window as per theme needs.
        width: 1024,
        height: 768,
        dialogClass: 'dialog-product-image-gallery-container',
        open: function () {
          // ZoomIn ZoomOut in Gallery view with a draggable container.
          // Make sure it always starts @ zero position for below calcs to work.
          if ($("#full-image-wrapper").length > 0) {
            $("#full-image").css({top: 0, left: 0});

            var maskWidth  = $("#full-image-wrapper").width();
            var maskHeight = $("#full-image-wrapper").height();
            var imgPos     = $("#full-image").offset();
            var imgWidth   = $("#full-image").width();
            var imgHeight  = $("#full-image").height();
            var x1 = (imgPos.left + maskWidth) - imgWidth;
            var y1 = (imgPos.top + maskHeight) - imgHeight;
            var x2 = imgPos.left;
            var y2 = imgPos.top;

            // Make image draggable inside the window.
            $("#full-image").draggable({ containment: [x1,y1,x2,y2] });
            $("#full-image").css({cursor: 'move'});

            // Zoom in and Zoom out buttons.
            var image = $('#full-image-wrapper img');
            var imagesize = image.width();
            var orignalwidth = imagesize;
            $('.zoomin').on('click', function () {
              imagesize = imagesize + 25;
              image.width(imagesize);
              image.height('auto');
            });

            $('.zoomout').on('click', function () {
              imagesize = imagesize - 25;
              if(imagesize < orignalwidth) {
                return;
              }
              image.width(imagesize);
              image.height('auto');
            });

            // Swap the big image inside slider-2 when clicking on thumbnail.
            $('#product-image-gallery li').click(function () {
              if($(this).hasClass('youtube') || $(this).hasClass('vimeo')) {
                var href = $(this).attr('data-iframe');
                $('#full-image-wrapper img').hide();
                $('#full-image-wrapper iframe').remove();
                appendVideoIframe($('#full-image-wrapper'), href, 480, 480);
              }
              else{
                var bigImage = $(this).children('a').attr('href');
                // Put the big image in our main container.
                $('#full-image-wrapper img').attr('src', bigImage);
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
      $('.cloud-zoom-container #cloud-zoom-wrap').on('click', function () {
        myDialog.show();
        myDialog.showModal();
      });

      ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
      // Handling videos inside sliders.
      // Swapping the conatiners or Inserting video iframes inside containers on click of video thumbnails.
      ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

      // Fetch Vimeo thumbnail via a GET call. Vimeo doesnot give thumbnails via URL like YT.
      $('#lightSlider li.video-product-zoom.vimeo, #product-image-gallery li.vimeo, #product-image-gallery-mobile li.vimeo').each(function () {
        var vimeoVideoUrl = $(this).attr('data-iframe');
        var match = /vimeo.*\/(\d+)/i.exec(vimeoVideoUrl);
        var self = $(this);
        if (match) {
          var vimeoVideoID = match[1];
          $.getJSON('https://www.vimeo.com/api/v2/video/' + vimeoVideoID + '.json?callback=?', {format: "json"}, function (data) {
            featuredImg = data[0].thumbnail_large;
            self.find('img').attr('src', featuredImg);
          });
        }
      });

      // Support Youtube & Vimeo videos in slider.
      // For Mobile slider we only insert, no need to remove it.
      $('#product-image-gallery-mobile li').on('click', function () {
        if($(this).hasClass('youtube') || $(this).hasClass('vimeo')) {
          var href = $(this).attr('data-iframe');
          $(this).children('img').hide();
          $(this).children('iframe').remove();
          appendVideoIframe($(this), href, 320, 320);
        }
      });
      // For Desktop slider, we add a iframe on click on the image.
      $('#lightSlider li').on('click', function () {
        if ($(this).hasClass('video-product-zoom')) {
          var wrap = $('#cloud-zoom-wrap');
          // Get width & height of wrap.
          var width = wrap.width();
          var height = wrap.height();
          var URL = $(this).attr('data-iframe');
          $('#yt-vi-container iframe').remove();
          appendVideoIframe($('#yt-vi-container'), URL, width, height);
          $('#cloud-zoom-wrap').hide();
        }
      });
      // For Desktop slider, we remove the iframe when we want to zoom another image.
      $('#lightSlider li a.cloud-zoom-gallery').on('click', function () {
        var playerIframe = $('#yt-vi-container iframe');
        // Check if there is a youtube video playing, if yes stop it and destroy the iframe.
        if (playerIframe.length > 0) {
          playerIframe.remove();
          $('#cloud-zoom-wrap').show();
        }
      });

      ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
      // Helper functions.
      ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

      /**
       * Toggles the product gallery based on screen width.
       */
      function toggleProductImageGallery() {
        if ($(window).width() < 381) {
          $('.image-gallery-mobile').show();
          $('.cloud-zoom-container').hide();
        }
        else {
          $('.image-gallery-mobile').hide();
          $('.cloud-zoom-container').show();
        }
      }

      /**
       * Appends iframe tag in the element that is passed.
       *
       * @param element
       *   The HTML element inside which we want iframe.
       * @param href
       *   The URL of video.
       * @param width
       *   The width of iframe/video.
       * @param height
       *   The height of the iframe/video.
       */
      function appendVideoIframe(element, href, width, height) {
        element.append('<iframe id="player" width="' + width + '" height="' + height + '" src="' + href
          + '" frameborder="0" allowfullscreen></iframe>');
      }
    }
  };
})(jQuery);
