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
   * We are using this separate function for carousels since in cases like PDP
   * the carousel is not occupying the full width of the page. In that case we
   * need to calculate left and right of carousels based on its container and
   * not on the viewport.
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

      // Get element left.
      var elementLeft = $(this).offset().left - offset;
      var elementRight = elementLeft + $(this).outerWidth();

      if (elementTop >= viewportTop
        && elementBottom <= viewportBottom
      ) {
        var active = false;
        // Check if we are in homepage/PDP.
        if ($(this).closest('.view-product-slider').length > 0) {
          var recommendProductsContainer = $(this).closest('.view-product-slider');
          var carouselLeft = recommendProductsContainer.offset().left;
          var carouselRight = carouselLeft + recommendProductsContainer.outerWidth();

          if ((elementLeft >= carouselLeft)
            && (elementRight <= carouselRight)) {
            active = true;
          }
        }
        // If slick slider is not used, that means the cart page carousels are
        // being viewed. Checked if they are within their container.
        else if ($(this).closest('.spc-recommended-products').length > 0) {
          var recommendProductsContainer = $(this).closest('.spc-recommended-products');
          var carouselLeft = recommendProductsContainer.offset().left;
          var carouselRight = carouselLeft + recommendProductsContainer.outerWidth();

          // We check if at least some part of the product is visible in the
          // carousel.
          if ((elementLeft >= carouselLeft)
            && (elementRight <= carouselRight)) {
            active = true;
          }
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
