/**
 * @file
 * JS for Back to PLP.
 */

(function ($) {
  'use strict';

  var replaceState;

  $.fn.updateWindowLocation = function (data) {
    data = Drupal.removeURLParameter(data, 'facet_filter_url');
    replaceState = data;
  };

  $(window).on('beforeunload pagehide', function () {
    if (typeof replaceState !== 'undefined') {
      history.replaceState({'back_to_list': true}, document.title, replaceState);
    }
  });

  function returnRefinedURL(key, url) {
    return url.replace(new RegExp(key + "=\\w+"), "").replace("?&", "?").replace("&&", "&");
  }

  // For RTL, we have some code to mess with page scroll.
  // @see docroot/themes/custom/transac/alshaya_white_label/js/custom.js file.
  $(window).on('pageshow', function () {
    if (window.location.search.indexOf('show_on_load') > -1) {
      replaceState = window.location.href;
      var url = returnRefinedURL('show_on_load', window.location.href);
      url = url.replace(/&$/g, "");
      history.replaceState({}, document.title, url);
    }

    setTimeout(Drupal.processBackToList, 10);
  });

  /**
   * Get the storage values.
   *
   * @returns {null}
   */
  function getStorageValues() {
    var value = localStorage.getItem(window.location.pathname);
    if (typeof value !== 'undefined' && value !== null) {
      return JSON.parse(value);
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

    var elementVisible = $(first_visible_product).isElementInViewPort($('.branding__menu').height());

    // If element is not visible, only then scroll.
    if (elementVisible === false) {
      $('html, body').animate({
        scrollTop: ($(first_visible_product).offset().top - $('.branding__menu').height())
      }, 400);
    }
  }

  /**
   * Adjust the grid view when back from PDP to listing page.
   */
  function adjustGridView() {
    // Get storage values.
    var storage_value = getStorageValues();
    // Prepare grid type class as per storage value.
    var grid_class_remove = storage_value.grid_type == 'small' ? 'large' : 'small';
    $('.c-products-list').removeClass('product-' + grid_class_remove);
    $('.c-products-list').addClass('product-' + storage_value.grid_type);
    $('.c-products-list').addClass('back-to-list');
    $('.' + grid_class_remove  + '-col-grid').removeClass('active');
    $('.' + storage_value.grid_type + '-col-grid').addClass('active');
    // Remove the grid_type property once applied when back from list
    // so that on next page load, default behavior is used.
    delete storage_value.grid_type;
    localStorage.setItem(window.location.pathname, JSON.stringify(storage_value));
  }

  Drupal.processBackToList = function () {
    // On page load, apply filter/sort if any.
    $('html').once('back-to-list').each(function () {
      var storage_value = getStorageValues();
      if (typeof storage_value !== 'undefined' && storage_value !== null) {
        // To adjust the grid view mode.
        if (typeof storage_value.grid_type !== 'undefined') {
          adjustGridView();
        }

        if (typeof storage_value.nid !== 'undefined') {
          // Set timeout because of conflict.
          setTimeout(function () {
            scrollToProduct();
          }, 1);
        }
      }
    });
  };

  Drupal.behaviors.backToSearch = {
    attach: function (context, settings) {
      // On product click, store the product position.
      $('.views-infinite-scroll-content-wrapper .c-products__item').once('back-to-plp').on('click', function () {
        // Prepare object to store details.
        var storage_details = {
          nid: $(this).find('article:first').attr('data-nid'),
          grid_type: $('.c-products-list').hasClass('product-large') ? 'large' : 'small',
        };

        // As local storage only supports string key/value pair.
        localStorage.setItem(window.location.pathname, JSON.stringify(storage_details));
      });
    }
  };

}(jQuery));
