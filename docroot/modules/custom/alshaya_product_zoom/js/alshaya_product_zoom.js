(function ($) {
  Drupal.behaviors.alshaya_product_zoom = {
    attach: function (context, settings) {
      // Product Zoom using CloudZoom library.
      items = $('.cloud-zoom:not(cloud-zoom-processed), .cloud-zoom-gallery:not(cloud-zoom-processed)', context);
      if (items.length) {
        items.addClass('cloud-zoom-processed').CloudZoom();
        items.parent().css('float', 'left');
      }

      // Initialize lightslider.
      $("#lightSlider").lightSlider({
          vertical:true,
          // Number of items to show at one time.
          item: 5,
          // The vertical container height, adjust this as per theme requirements.
          verticalHeight:500,
      });

      // Fetch Vimeo thumbnail.
      $('#lightSlider li.video-product-zoom.vimeo').each(function(){
          var vimeoVideoUrl = $(this).attr('data-iframe');
          var match = /vimeo.*\/(\d+)/i.exec(vimeoVideoUrl);
          if (match) {
            var vimeoVideoID = match[1];
            $.getJSON('https://www.vimeo.com/api/v2/video/' + vimeoVideoID + '.json?callback=?', { format: "json" }, function (data) {
              featuredImg = data[0].thumbnail_large;
              $('#lightSlider li.video-product-zoom.vimeo img').attr('src', featuredImg);
            });
          }
      });

      // Support Youtube & Vimeo videos in slider.
      $('#lightSlider li').on('click', function () {
        if($(this).hasClass('video-product-zoom')) {
          var wrap = $('#wrap');
           // Get width & height of wrap.
          var width = wrap.width();
          var height = wrap.height();
          var URL =  $(this).attr('data-iframe');
          $('#yt-vi-container iframe').remove();
          $('#yt-vi-container').html('<iframe id="player" width="' + width + '" height="' + height + '" src="'
                  + URL + '" frameborder="0" allowfullscreen></iframe>');
          $('#wrap').hide();
        }
      });
      $('#lightSlider li a.cloud-zoom-gallery').on('click', function () {
        var playerIframe = $('#yt-vi-container iframe');
        // Check if there is a youtube video playing, if yes stop it and destroy the iframe.
        if(playerIframe.length > 0) {
          playerIframe.remove();
          $('#wrap').show();
        }
      });
    }
  };
})(jQuery);
