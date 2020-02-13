(function ($) {

  /**
   * Check if element is fully visible in viewport or not.
   *
   * @param offset
   *
   * @returns {boolean}
   */
  $.fn.isElementInViewPort = function (offset) {
    try {
      // Get element top and bottom.
      var elementTop = $(this).offset().top - offset;
      var elementBottom = elementTop + $(this).outerHeight();

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

}(jQuery));
