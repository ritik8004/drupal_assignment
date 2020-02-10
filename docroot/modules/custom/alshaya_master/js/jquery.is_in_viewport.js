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

      return elementTop >= viewportTop && elementBottom <= viewportBottom;
    }
    catch (e) {
      return false;
    }
  }

  /**
   * Check if element is fully visible in viewport horizontally or not.
   *
   * @param offset
   *
   * @returns {boolean}
   */
  $.fn.isElementInViewPortHorizontally = function (offset) {
    try {
      // Get element top and bottom.
      var elementLeft = $(this).offset().left - offset;
      var elementRight = elementLeft + $(this).outerWidth();

      // Get window top and bottom.
      var viewportLeft = $(window).scrollLeft();
      var viewportRight = viewportLeft + $(window).width();

      return elementLeft >= viewportLeft && elementRight <= viewportRight;
    }
    catch (e) {
      return false;
    }
  }

}(jQuery));
