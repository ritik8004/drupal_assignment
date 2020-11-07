(function ($) {
  'use strict';

  Drupal.behaviors.sizeFacetCopy = {
    attach: function (context, settings) {
      // For now we want to do it for PLP only.
      $('.region__content [data-block-plugin-id="facet_block:plp_size"]:first').once('size-copy').each(function () {
        var $wrapper = $(this);

        $('.sfb-facets-container').html('');

        // Get all available facets.
        $wrapper.find('.facet-item').each(function () {
          var item = $(this).find('a');
          var $div = $('<div />');

          // Add value from hidden anchor to copy.
          $div.attr('data-facet-item-value', $(item).attr('data-drupal-facet-item-value'));

          // Add classes from hidden anchor tag to copy.
          $div.attr('class', $(item).attr('class'));

          var $value = $('.facet-item__value', $(item)).clone();
          $value.find('span').remove();
          var value = $value.html().trim();
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

        $('.sfb-facets-container [data-facet-item-value]').on('click', function () {
          var $value = $(this).attr('data-facet-item-value');
          $('.facet-item a[data-drupal-facet-item-value="' + $value + '"]', $wrapper).closest('.facet-item').trigger('click');
        });
      });

      applyFilterSlider();
    }
  };

  function applyFilterSlider() {
    if ($(window).width() > 1024) {
      // Duration of scroll animation.
      var scrollDuration = 300;

      // Paddles.
      var leftPaddle = $('.paddle_prev');
      var rightPaddle = $('.paddle_next');

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

      // Get how much have we scrolled to the left.
      var getMenuPosition = function () {
        return $('.sfb-facets-container').scrollLeft();
      };

      $('.sfb-band-cup').find('.shop-by-size-band').each(function () {
        // Get the distance of different cup size wrapper from starting point.
        if ($('html').attr('dir') == 'rtl') {
          CupsizewrapperWidthsum = $(this).outerWidth() + 16;
        }
        else {
          CupsizewrapperWidthsum += $(this).outerWidth() + 16;
        }
        DifferenceOfsCupsizewrapper.push(CupsizewrapperWidthsum);

        // Get the distance of different cup size wrapper from starting point to check the slider required or not.
        Cupsizewrapperwidth += $(this).outerWidth() + 16;
        widthOfsCupsizewrapper.push(Cupsizewrapperwidth);
      });

      // Compare the last element value with the max width of the wrapper.
      if (widthOfsCupsizewrapper[widthOfsCupsizewrapper.length - 1] < 1000) {
        if ($('html').attr('dir') == 'rtl') {
          $('.paddle_prev').addClass('hidden');
        }
        else {
          $('.paddle_next').addClass('hidden');
        }
      }

      var sliderIndex;
      var counter = 0;
      var sliderIndexidentifier;
      if ($('html').attr('dir') == 'rtl') {
        DifferenceOfsCupsizewrapper.reverse();
        for (var i = 1; i < DifferenceOfsCupsizewrapper.length; i++) {
          DifferenceOfsCupsizewrapper[i] = DifferenceOfsCupsizewrapper[i] + DifferenceOfsCupsizewrapper[i - 1];
          if (DifferenceOfsCupsizewrapper[i] > $('.sfb-facets-container').width() && counter == 0) {
            sliderIndex = DifferenceOfsCupsizewrapper.length - i - 1;
            sliderIndexidentifier = DifferenceOfsCupsizewrapper.length - i;
            counter++;
          }
        }
      }
      else {
        sliderIndex = 0;
        for (var i = 1; i < DifferenceOfsCupsizewrapper.length; i++) {
          if (DifferenceOfsCupsizewrapper[i] > $('.sfb-facets-container').width() && counter == 0) {
            sliderIndexidentifier = DifferenceOfsCupsizewrapper.length - i;
            counter++;
          }
        }
      }

      var menuWrapperSize = getMenuWrapperSize();

      // The wrapper is responsive.
      $(window).resize(debounce(function () {
        menuWrapperSize = getMenuWrapperSize();
      }, 500));

      var menuSize = getMenuSize();
      // Get how much of menu is invisible.
      var menuInvisibleSize = menuSize - menuWrapperSize;

      // Finally, what happens when we are actually scrolling the menu.
      $('.sfb-facets-container').on('scroll', function () {

        // Get how much of menu is invisible.
        menuInvisibleSize = menuSize - menuWrapperSize;
        // Get how much have we scrolled so far.
        var menuPosition = getMenuPosition();

        // Show & hide the paddles depending on scroll position.
        if (menuPosition <= 0) {
          $(leftPaddle).addClass('hidden');
          $(rightPaddle).removeClass('hidden');
        }
        else if (menuPosition >= (DifferenceOfsCupsizewrapper[itemsLength - 1] - (menuWrapperSize + 17))) {
          $(leftPaddle).removeClass('hidden');
          $(rightPaddle).addClass('hidden');
        }
        else {
          $(leftPaddle).removeClass('hidden');
          $(rightPaddle).removeClass('hidden');
        }
      });

      if ($('html').attr('dir') == 'rtl') {
        $(leftPaddle).once().on('click', function () {
          sliderIndex--;
          if (sliderIndex <= 0) {
            $('.sfb-facets-container').animate({scrollLeft: 0}, scrollDuration);
            sliderIndex = 0;
          }
          else {
            $('.sfb-facets-container').animate({scrollLeft: DifferenceOfsCupsizewrapper[sliderIndex]}, scrollDuration);
          }
        });

        // Scroll to right.
        $(rightPaddle).once().on('click', function () {
          // After reaching the end of slide if we scroll using mouse then index should be same as last one.
          if (sliderIndex >= sliderIndexidentifier - 1) {
            sliderIndex = sliderIndexidentifier - 1;
          }
          else {
            sliderIndex++;
          }
          $('.sfb-facets-container').animate({scrollLeft: DifferenceOfsCupsizewrapper[sliderIndex]}, scrollDuration);
        });
      }
      else {
        // Scroll to left.
        $(rightPaddle).once().on('click', function () {
          if (sliderIndex > sliderIndexidentifier - 1) {
            sliderIndex = sliderIndexidentifier - 1;
          }
          else {
            sliderIndex++;
          }
          $('.sfb-facets-container').animate({scrollLeft: DifferenceOfsCupsizewrapper[sliderIndex - 1]}, scrollDuration);
        });

        // Scroll to right.
        $(leftPaddle).once().on('click', function () {
          sliderIndex--;
          if (sliderIndex <= 0) {
            sliderIndex = 0;
            $('.sfb-facets-container').animate({scrollLeft: 0}, scrollDuration);
          }
          else {
            // scroll by a single size wrapper.
            $('.sfb-facets-container').animate({scrollLeft: (DifferenceOfsCupsizewrapper[sliderIndex - 1])}, scrollDuration);
          }
        });
      }
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
