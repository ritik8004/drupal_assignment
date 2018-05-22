(function ($) {
  'use strict';
  Drupal.behaviors.backToList = {
    attach: function (context, settings) {
      // On product click, store the product position.
      $('.views-infinite-scroll-content-wrapper .c-products__item').on('click', function () {
        // Set key/value where key is url and value is position of product.
        localStorage.setItem(window.location.href, $(this).position().top);
      });

      // Here we just clearing the storage.
      $(window).on('scroll', function() {
        if (localStorage.getItem(window.location.href)) {
          // Current scroll position.
          var y_scroll_pos = window.pageYOffset;
          // Position where we need to scroll.
          var product_position = localStorage.getItem(window.location.href);
          // If windows scroll position is greater that product position, it means
          // we have reached/scrolled to the product and thus clear the storage.
          if(y_scroll_pos >= parseInt(product_position)) {
            localStorage.removeItem(window.location.href);
          }
        }
      });

      // If scroll position is set in storage.
      if (localStorage.getItem(window.location.href)) {
        // Get the position.
        var gScroltop = localStorage.getItem(window.location.href);
        // Doing this on ajax complete as the load more button might not
        // available due to lazy loading.
        $(document).ajaxComplete(function (event, xhr, settings) {
          // Scroll to appropriate position.
          checkAndScrollTo(gScroltop);
        });

        // Doing this as back button loads page so fast causing conflict.
        setTimeout(function() {
          // Scroll to appropriate position.
          checkAndScrollTo(gScroltop);
        }, 1000);
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
          if (load_more_button_position.top <= product_pos) {
            return true;
          }
        }

        return false;
      }

      /**
       * Check and click and scroll to position.
       *
       * @param product_pos
       */
      function checkAndScrollTo(product_pos) {
        if (triggerLoadMoreClick(product_pos)) {
          $('.js-pager__items a').trigger("click");
        }
        // Scroll to appropriate position if no ajax call.
        if (localStorage.getItem(window.location.href)) {
          // Scroll to appropriate product.
          scrollToProduct(product_pos);
        }
      }
    }
  }

}(jQuery));
