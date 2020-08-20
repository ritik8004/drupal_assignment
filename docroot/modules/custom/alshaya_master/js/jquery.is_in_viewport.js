(function ($) {

  /**
   * Check if element is fully/partially visible in viewport or not.
   *
   * @param offset
   * @param elementPartialOffsetTop
   *   To be used when we want only some part of the element to be visisble. We
   *   specify an offset from the top of the element that should be in the
   *   screen.
   *
   * @returns {boolean}
   */
  $.fn.isElementInViewPort = function (offset, elementPartialOffsetTop) {
    try {
      // Get element top and bottom.
      var elementTop = $(this).offset().top - offset;
      var elementBottom = (elementPartialOffsetTop !== 'undefined')
                          ? elementTop + elementPartialOffsetTop
                          : elementTop + $(this).outerHeight();

      // Get window top and bottom.
      var viewportTop = $(window).scrollTop();
      var viewportBottom = viewportTop + $(window).height();

      // Get element left and right.
      var elementLeft = $(this).offset().left - offset;
      var elementRight = elementLeft + $(this).outerWidth();

      // Get window left and right.
      var viewportLeft = $(window).scrollLeft();
      var viewportRight = viewportLeft + $(window).width();

      return elementTop >= viewportTop
      && elementBottom <= viewportBottom
      && elementLeft >= viewportLeft
      && elementRight <= viewportRight;
    }
    catch (e) {
      return false;
    }
  }

  /**
   * Check if carousel element is fully/partially visible in viewport or not.
   *
   * @param offset
   * @param elementPartialOffsetTop
   *   To be used when we want only some part of the element to be visible. We
   *   specify an offset from the top of the element that should be in the
   *   screen.
   *
   * @returns {boolean}
   */
  $.fn.isCarouselElementInViewPort = function (offset, elementPartialOffsetTop) {
    try {
      // Get element top and bottom.
      var elementTop = $(this).offset().top - offset;
      var elementBottom = (elementPartialOffsetTop !== 'undefined')
                          ? elementTop + elementPartialOffsetTop
                          : elementTop + $(this).outerHeight();

      // Get window top and bottom.
      var viewportTop = $(window).scrollTop();
      var viewportBottom = viewportTop + $(window).height();

      // Get element left and right.
      var elementLeft = $(this).offset().left - offset;
      var elementRight = elementLeft + $(this).outerWidth();

      // Get window left and right.
      var viewportLeft = $(window).scrollLeft();
      var viewportRight = viewportLeft + $(window).width();

      if (elementTop >= viewportTop
          && elementBottom <= viewportBottom
      ) {
        var elementParent = $(this).parent();
        var active = false;
        // Check if slick slider is used in carousel like in homepage and PDP.
        if (elementParent.hasClass('slick-slide')) {
          // If it has slick-active class, that means it is showing in screen.
          active = (elementParent.hasClass('slick-active')) ? true : false;
        }
        // If slick slider is not used, check if the element is visible in screen or
        // not.
        else if ((elementLeft > viewportLeft)
          && (elementRight < viewportRight)) {
          active = true;
        }
        return active;
      }
    }
    catch (e) {
      return false;
    }

    return false;
  }
}(jQuery));
