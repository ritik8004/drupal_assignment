(function ($) {
  'use strict';

  $.fn.updateWindowLocation = function (data) {
    history.replaceState({'back_to_list': true}, document.title, data);
  };

  /**
   * Get the storage values.
   *
   * @returns {null}
   */
  function getStorageValues() {
    if (localStorage.getItem(window.location.href)) {
      return JSON.parse(localStorage.getItem(window.location.href));
    }

    return null;
  }

  /**
   * Scroll to the appropriate product.
   */
  function scrollToProduct() {
    var storage_value = getStorageValues();
    var first_visible_product = $('.views-infinite-scroll-content-wrapper article[data-nid="' + storage_value.nid + '"]:visible:first');

    if (typeof first_visible_product === 'undefined') {
      return;
    }

    var elementVisible = isElementInViewPort(first_visible_product);
    // If element is not visible, only then scroll.
    if (elementVisible === false) {
      $('html, body').animate({
        scrollTop: ($(first_visible_product).offset().top - $('.branding__menu').height())
      }, 400);
    }

    // Once scroll to product, clear the storage.
    localStorage.removeItem(window.location.href);
  }

  /**
   * Check if element is fully visible in viewport or not.
   *
   * @param element
   * @returns {boolean}
   */
  function isElementInViewPort(element) {
    // Get element top and bottom.
    var elementTop = $(element).offset().top - $('.branding__menu').height();
    var elementBottom = elementTop + $(element).outerHeight();

    // Get window top and bottom.
    var viewportTop = $(window).scrollTop();
    var viewportBottom = viewportTop + $(window).height();

    return elementTop >= viewportTop && elementBottom <= viewportBottom;
  }

  Drupal.behaviors.backToList = {
    attach: function (context, settings) {
      // On product click, store the product position.
      $('.views-infinite-scroll-content-wrapper .c-products__item').once('back-to-plp').on('click', function () {
        // Prepare object to store details.
        var storage_details = {
          nid: $(this).find('article:first').attr('data-nid')
        };

        // As local storage only supports string key/value pair.
        localStorage.setItem(window.location.href, JSON.stringify(storage_details));
      });

      // On page load, apply filter/sort if any.
      $('html').once('back-to-list').each(function() {
        var storage_value = getStorageValues();
        if (typeof storage_value !== 'undefined' && storage_value !== null) {
          if (typeof storage_value.nid !== 'undefined') {
            // Set timeout because of conflict.
            setTimeout(function(){
              scrollToProduct();
            }, 1);
          }
        }
      });
    }
  }

}(jQuery));
