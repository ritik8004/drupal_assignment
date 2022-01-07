(function ($) {

  Drupal.behaviors.sizeFacetCopy = {
    attach: function (context, settings) {
      $(document).once('size-update-event').on('algoliaRefinementListUpdated', function (e) {
        // Check for size filter update.
        // Filter/Attribute name = attr_size;
        // For _product_list index it will be attr_size.en.
        // split lang suffix to check condition.
        if (e.detail.attribute.split('.')[0] === 'attr_size' && e.detail.items.length > 0) {
          populateShopBySize();
        }
      });
    }
  };

  /**
   * Create the shop by bra size block from data present in filters in DOM.
   */
  function populateShopBySize() {
    $('#attr_size').each(function () {
      var $wrapper = $(this);

      $('.sfb-facets-container').html('');

      // Get all available facet items in the filter.
      $wrapper.find('.facet-item').each(function () {
        var item = $(this).find('.facet-item__value');
        var label = $(item).attr('data-drupal-facet-item-value');

        // Create an empty div.
        var $div = $('<div />');

        // Add label value from filter to div.
        $div.attr('data-facet-item-value', label);

        // Add classes from hidden anchor tag to copy.
        if ($(item).parent().hasClass('is-active')) {
          $div.attr('class', 'is-active');
        }

        var value = label.trim();
        var bandSize = parseInt(value);

        // This is for shop by letters.
        if (isNaN(bandSize)) {
          if ($('div[data-facet-item-value="' + value + '"]').length === 0) {
            $('.sfb-letter .sfb-facets-container').append($('<div attr-band-size="' + value + '" class="shop-by-size-letter"/>'));
            $div.append('<span class="shop-by-size-alpha">' + value + '</span>');
            $('div[attr-band-size="' + value + '"]').append($div);
          }
        }
        // This is for shop by band and cup size.
        else {
          if ($('div[attr-band-size="' + bandSize + '"]').length === 0) {
            $('.sfb-band-cup .sfb-facets-container').append($('<div attr-band-size="' + bandSize + '" class="shop-by-size-band"/>'));
          }
          $div.append('<span class="shop-by-size">' + bandSize + '</span>');
          // Find cup size now.
          var cupSize = value.replace(bandSize, '');
          $div.append('<span class="shop-by-size">' + cupSize + '</span>');
          $('div[attr-band-size="' + bandSize + '"]').append($div);
        }
      });

      // Transfer click to the actual element created by React Algolia.
      $('.sfb-facets-container [data-facet-item-value]').on('click', function () {
        var $value = $(this).attr('data-facet-item-value');
        $('.facet-item__value[data-drupal-facet-item-value="' + $value + '"]', $wrapper).parent('.facet-item').trigger('click');
      });
    });

    applyFilterSlider();
  }

  /**
   * Create navigation.
   */
  function applyFilterSlider() {
    if ($(window).width() > 1024) {
      // Duration of scroll animation.
      var scrollDuration = 300;

      // Paddles.
      var leftPaddle = ($('html').attr('dir') == 'rtl') ? $('.paddle_next') : $('.paddle_prev');
      var rightPaddle = ($('html').attr('dir') == 'rtl') ? $('.paddle_prev') : $('.paddle_next');

      // Get items dimensions.
      var itemsLength = $('.shop-by-size-band').length;
      var itemSize = $('.shop-by-size-band').outerWidth();

      var DifferenceOfsCupsizewrapper = [];
      var widthOfsCupsizewrapper = [];
      var Cupsizewrapperwidth = 0;
      var CupsizewrapperWidthsum = 0;

      // Get total width of all menu items.
      var getMenuSize = function () {
        return $('.sfb-band-cup').outerWidth();
      };

      // Get wrapper width.
      var getMenuWrapperSize = function () {
        return $('.sfb-facets-container').outerWidth();
      };

      $('.sfb-band-cup').find('.shop-by-size-band').each(function () {
        CupsizewrapperWidthsum += $(this).outerWidth() + 16;
        DifferenceOfsCupsizewrapper.push(CupsizewrapperWidthsum);

        // Get the distance of different cup size wrapper from starting point to check the slider required or not.
        Cupsizewrapperwidth = $(this).outerWidth() + 16;
        widthOfsCupsizewrapper.push(Cupsizewrapperwidth);
      });

      var menuWrapperSize = getMenuWrapperSize();

      var maxAllowedScrollValue = CupsizewrapperWidthsum - (menuWrapperSize + 17);

      // Compare the last element value with the max width of the wrapper.
      if (CupsizewrapperWidthsum < 1000) {
        if ($('html').attr('dir') == 'rtl') {
          $('.paddle_prev').addClass('hidden');
        }
        else {
          $('.paddle_next').addClass('hidden');
        }
      }

      var sliderIndex = 0;
      var sliderIndexidentifier = 0;

      // Calculate right index when slider moves to left for EN and right for AR.
      var getRightClickIndexidentifier = function () {
        var calcWidth = 0;
        var counter = 0;
        for (var i = sliderIndex; i < widthOfsCupsizewrapper.length; i++) {
          calcWidth += widthOfsCupsizewrapper[i];
          if (calcWidth > $('.sfb-facets-container').width() && counter == 0) {
            sliderIndexidentifier = i;
            counter++;
          }
        }
      };

      // Calculate left index when slider moves to right for EN and left for AR.
      var getLeftClickIndexidentifier = function (scrollPosition) {
        var calcWidth = 0;
        var counter = 0;
        for (var i = sliderIndex; i >= 0, counter == 0; i--) {
          if(scrollPosition >= widthOfsCupsizewrapper[i]) {
            calcWidth += widthOfsCupsizewrapper[i];
            if (calcWidth >= scrollPosition) {
              sliderIndex = i;
              counter++;
            }
          }
          else {
            sliderIndex = 0;
            counter++;
          }
        }
      };

      getRightClickIndexidentifier();

      // The wrapper is responsive.
      $(window).resize(debounce(function () {
        menuWrapperSize = getMenuWrapperSize();
      }, 500));

      // Get how much have we scrolled to the left and hide arrows accordingly.
      var hideArrow = function (scrollPosition) {
        if (scrollPosition == 0) {
          $(leftPaddle).addClass('hidden');
          $(rightPaddle).removeClass('hidden');
        }
        else if (scrollPosition >= maxAllowedScrollValue) {
          $(leftPaddle).removeClass('hidden');
          $(rightPaddle).addClass('hidden');
        }
        else {
          $(leftPaddle).removeClass('hidden');
          $(rightPaddle).removeClass('hidden');
        }
      };

      $('.sfb-facets-container').on('scroll', function () {
        hideArrow(Math.abs($('.sfb-facets-container').scrollLeft()));
      });

      // Scroll to left for EN and right for AR.
      $(rightPaddle).once().on('click', function () {
        var animatePosition = ($('html').attr('dir') == 'rtl') ? -widthOfsCupsizewrapper[sliderIndex] : widthOfsCupsizewrapper[sliderIndex];
        if (Math.abs(animatePosition) > maxAllowedScrollValue) {
          animatePosition = ($('html').attr('dir') == 'rtl') ? -maxAllowedScrollValue : maxAllowedScrollValue;
        }
        animatePosition += $('.sfb-facets-container').scrollLeft();
        $('.sfb-facets-container').animate({scrollLeft: animatePosition}, scrollDuration);
        sliderIndex++;
        getRightClickIndexidentifier();
      });

      // Scroll to right for EN and left for AR.
      $(leftPaddle).once().on('click', function () {
        var animatePosition = ($('html').attr('dir') == 'rtl') ? widthOfsCupsizewrapper[sliderIndexidentifier] : -widthOfsCupsizewrapper[sliderIndexidentifier];
        if (Math.abs(animatePosition) > maxAllowedScrollValue) {
          animatePosition = ($('html').attr('dir') == 'rtl') ? maxAllowedScrollValue : -maxAllowedScrollValue;
        }
        animatePosition += $('.sfb-facets-container').scrollLeft();
        $('.sfb-facets-container').animate({scrollLeft: animatePosition}, scrollDuration);
        getLeftClickIndexidentifier(widthOfsCupsizewrapper[sliderIndexidentifier]);
        sliderIndexidentifier--;
      });
    }

    //JS for checking the empty filters.
    function emptyBrasFilter(element) {
      if (element.is(':empty')) {
        element.parent().addClass('empty-bras-filter');
      }
      else {
        element.parent().removeClass('empty-bras-filter');
      }
    }

    emptyBrasFilter($('.sfb-letter .sfb-facets-container'));
    emptyBrasFilter($('.sfb-band-cup .sfb-facets-container'));
  }

  if ($('html').attr('dir') == 'rtl') {
    $('.paddle_next').addClass('hidden');
  }
  else {
    $('.paddle_prev').addClass('hidden');
  }

}(jQuery));
