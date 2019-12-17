/**
 * @file
 * JS for Back to Search.
 */

(function ($) {
  'use strict';

  /**
   * Get the storage values.
   *
   * @returns {null}
   */
  function getAlgoliaStorageValues() {
    var value = localStorage.getItem(window.location.hash);
    if (typeof value !== 'undefined' && value !== null) {
      return JSON.parse(value);
    }

    return null;
  }

  /**
   * Scroll to the appropriate product.
   */
  function scrollToAlgoliaProduct() {
    var storage_value = getAlgoliaStorageValues();
    var first_visible_product = $('#alshaya-algolia-search .view-search article[data-sku="' + storage_value.sku + '"]:visible:first');

    localStorage.removeItem(window.location.hash);
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
  function adjustAlgoliaGridView() {
    // Get storage values.
    var storage_value = getAlgoliaStorageValues();
    // Prepare grid type class as per storage value.
    var grid_class_remove = storage_value.grid_type == 'small' ? 'large' : 'small';
    var $algoliaSearchProductList = $('#alshaya-algolia-search .c-products-list');
    $algoliaSearchProductList.removeClass('product-' + grid_class_remove);
    $algoliaSearchProductList.addClass('product-' + storage_value.grid_type);
    $algoliaSearchProductList.addClass('back-to-search');
    $('#alshaya-algolia-search').find('.' + grid_class_remove  + '-col-grid').removeClass('active');
    $('#alshaya-algolia-search').find('.' + storage_value.grid_type + '-col-grid').addClass('active');
    // Remove the grid_type property once applied when back from list
    // so that on next page load, default behavior is used.
    delete storage_value.grid_type;
    localStorage.setItem(window.location.hash, JSON.stringify(storage_value));
  }

  Drupal.processBackToSearch = function (storage_value) {
    // On page load, apply filter/sort if any.
    $('html').once('back-to-list').each(function () {
      if (typeof storage_value !== 'undefined' && storage_value !== null) {
        // To adjust the grid view mode.
        if (typeof storage_value.grid_type !== 'undefined') {
          adjustAlgoliaGridView();
        }

        if (typeof storage_value.sku !== 'undefined') {
          scrollToAlgoliaProduct();
        }
      }
    });
  };

}(jQuery));
