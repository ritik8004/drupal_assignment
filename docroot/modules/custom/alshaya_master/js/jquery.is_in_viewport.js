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

}(jQuery));
