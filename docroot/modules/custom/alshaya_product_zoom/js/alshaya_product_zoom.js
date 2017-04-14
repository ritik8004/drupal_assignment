(function ($) {
  Drupal.behaviors.alshaya_product_zoom = {
    attach: function (context, settings) {
      // Inlialize Product Zoom using CloudZoom library.
      items = $('.cloud-zoom:not(cloud-zoom-processed), .cloud-zoom-gallery:not(cloud-zoom-processed)', context);
      if (items.length) {
        items.addClass('cloud-zoom-processed').CloudZoom();
        items.parent().css('float', 'left');
      }

      // Initialize lightSliders.
      // Slider 1 - For Desktop - Image zoom.
      $("#lightSlider").lightSlider({
        vertical: true,
        // Number of items to show at one time.
        item: 5,
        // The vertical container height, adjust this as per theme requirements.
        verticalHeight: 500,
      });

      // Slider - 2 For Desktop - Image Gallery.
      $("#product-image-gallery").lightSlider({
        vertical: true,
        item: 5,
        verticalHeight: 500,
      });

      // Modal view for Slider-2 when clicking on big image - Image Gallery.
      var element = document.getElementById('product-image-gallery-container');
      var dialogsettings = {
        autoOpen: true,
        width: 1024,
        height: 768,
        dialogClass: 'dialog-product-image-gallery-container'
      };
      var myDialog = Drupal.dialog(element, dialogsettings);

      // Open Gallery modal when we click on the zoom image.
      $('.cloud-zoom-container .mousetrap').click(function () {
        myDialog.show();
        myDialog.showModal();
      });

      ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

      // ZoomIn ZoomOut in Gallery view.
      // Make sure it always starts @ zero position for below calcs to work.
      if ($("#full-image-wrapper").length > 0) {
        $("#full-image").css({top: 0, left: 0});

        var maskWidth  = $("#full-image-wrapper").width();
        var maskHeight = $("#full-image-wrapper").height();
        var imgPos     = $("#full-image").offset();
        var imgWidth   = $("#full-image").width();
        console.log(imgWidth);
        var imgHeight  = $("#full-image").height();
        console.log(imgHeight);
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
            $('#full-image-wrapper').append('<iframe id="player" width="480" height="480" src="'
              + href + '" frameborder="0" allowfullscreen></iframe>');
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

      ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

      // Fetch Vimeo thumbnail.
      $('#lightSlider li.video-product-zoom.vimeo, #product-image-gallery li.vimeo').each(function () {
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
      $('#lightSlider li').on('click', function () {
        if ($(this).hasClass('video-product-zoom')) {
          var wrap = $('#wrap');
          // Get width & height of wrap.
          var width = wrap.width();
          var height = wrap.height();
          var URL = $(this).attr('data-iframe');
          $('#yt-vi-container iframe').remove();
          $('#yt-vi-container').html('<iframe id="player" width="' + width + '" height="' + height + '" src="'
            + URL + '" frameborder="0" allowfullscreen></iframe>');
          $('#wrap').hide();
        }
      });
      $('#lightSlider li a.cloud-zoom-gallery').on('click', function () {
        var playerIframe = $('#yt-vi-container iframe');
        // Check if there is a youtube video playing, if yes stop it and destroy the iframe.
        if (playerIframe.length > 0) {
          playerIframe.remove();
          $('#wrap').show();
        }
      });
    }
  };
})(jQuery);
