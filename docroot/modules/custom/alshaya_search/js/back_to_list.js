(function ($) {
  'use strict';
  Drupal.behaviors.backToList = {
    attach: function (context, settings) {
      // On product click, store the product position.
      $('.views-infinite-scroll-content-wrapper .c-products__item').on('click', function () {
        // Set position value.
        localStorage.setItem('list_scroll_position', $(this).position().top);
      });

      // Here we just clearing the storage.
      $(window).on('scroll', function() {
        if (localStorage.getItem('list_scroll_position')) {
          var y_scroll_pos = window.pageYOffset;
          var product_position = localStorage.getItem('list_scroll_position');
          // If windows scroll position is greater that product position, it means
          // we have reached/scrolled to the product and thus clear the storage.
          if(y_scroll_pos >= product_position) {
            localStorage.removeItem('list_scroll_position');
          }
        }
      });

      // If scroll position is set in storage.
      if (localStorage.getItem('list_scroll_position')) {
        // Get the position.
        var gScroltop = localStorage.getItem('list_scroll_position');
        // Doing this on ajax complete as the load more button might not
        // available due to lazy loading.
        $(document).ajaxComplete(function (event, xhr, settings) {
          if (triggerLoadMoreClick(gScroltop)) {
            $('.js-pager__items a').trigger("click");
          }

          // Scroll to appropriate product.
          if (localStorage.getItem('list_scroll_position')) {
            scrollToProduct(gScroltop);
          }
        });

        // Scroll to appropriate position if no ajax call.
        if (localStorage.getItem('list_scroll_position')) {
          // Scroll to appropriate product.
          scrollToProduct(gScroltop);
        }
      }

      /**
       * Scroll to the appropriate product position.
       *
       * @param product_pos
       */
       function scrollToProduct(product_pos) {
        // Get the height of brand menu.
        var stickyHeaderHeight = ($('.branding__menu').length > 0) ? $('.branding__menu').height() + 40 : 40;
        var product_height = $('.views-infinite-scroll-content-wrapper .c-products__item').height();

        // Once load more button is not less than the product scroll position,
        // we scroll then to appropriate position.
        $(window).scrollTop(product_pos + parseInt(stickyHeaderHeight) + parseInt(product_height));
      }

      /**
       * Whether load more needs to be clicked or not.
       *
       * @param product_pos
       * @returns {boolean}
       */
      function triggerLoadMoreClick(product_pos) {
        // If load more button exists.
        if ($('.js-pager__items').length > 0) {
          var load_more_button_position = $('.js-pager__items').position();
          // If load more button position is less what than the product we clicked,
          // means we need to click the load more button.
          if (load_more_button_position.top < product_pos) {
            return true;
          }

          return false;
        }
      }
    }
  }

}(jQuery));
