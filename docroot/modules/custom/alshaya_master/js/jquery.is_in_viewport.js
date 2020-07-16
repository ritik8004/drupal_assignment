(function ($) {

  /**
   * Check if element is fully/partially visible in viewport or not.
   *
   * @param offset
   * @param elementPartialOffsetTop
   *   To be used when we want only some part of the element to be visisble. We
   *   specify an offset from the top of the element that should be in the
   *   screen.
   * @param {boolean} isCarouselItem
   *   If item is a carousel item, pass true else false.
   *
   * @returns {boolean}
   */
  $.fn.isElementInViewPort = function (offset, elementPartialOffsetTop, isCarouselItem) {
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

      if (isCarouselItem === true
        && elementTop >= viewportTop
        && elementBottom <= viewportBottom
      ) {
        return isCarouselItemActive($(this));
      }

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
   * The function checks if a carousel item is in view or not.
   *
   *   This is tightly coupled with slick at the moment. If later the carousel
   * library changes, only code needs to be changed here to determine if
   * the carousel item is in view or not.
   *
   * @param {object} element
   *   The object inside individual row of the carousel markup.
   */
  var isCarouselItemActive = function (element) {
    var elementParent = element.parent();
    var active = false;
    // Check if slick slider is used in carousel like in homepage and PDP.
    if (elementParent.hasClass('slick-slide')) {
      active = (elementParent.hasClass('slick-active')) ? true : false;
    }
    // If slick slider is not used, check if the element is visible in screen or
    // not.
    else if (elementLeft > viewportLeft && elementRight < viewportLeft) {
      active = true;
    }

    return active;
  }
}(jQuery));
