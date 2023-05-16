/* eslint-disable */
/**
 * @file
 * PLP All Filters Panel & Facets JS file.
 */
(function ($, Drupal) {

  Drupal.behaviors.alshayaAlgoliaReactGlobal = {
    attach: function (context, settings) {
      // Close the facets on click anywherer outside.
      $(window).on('click', function(event) {
        var facet_block = $('.container-without-product .c-collapse-item');
        if ($(facet_block).find(event.target).length === 0) {
          $(facet_block).find('.c-facet__title').removeClass('active');
          $(facet_block).children('ul').slideUp();
        }
      });

      $(context)
        .once('lazy-hover')
        .on('mouseover', '.alshaya_search_gallery.lazy-hover', function () {
          this.classList.remove('lazy-hover');
        });
    }
  };

  Drupal.algolia = {};

  /**
   * Algolia storage values.
   *
   * @param key
   *   The storage key.
   * @returns {null|any}
   *   Return the value of object or null.
   */
  Drupal.algolia.getAlgoliaStorageValues = function(key) {
    return Drupal.getItemFromLocalStorage(key);
  }

  /**
   * Scroll to the appropriate product.
   *
   * @param $context
   *   The current context.
   * @param wrapper
   *   The wrapper in which we have find the elements context.
   * @param storageKey
   *   The local storage key.
   * @param storage_value
   *   The local storage value.
   */
  Drupal.algolia.scrollToAlgoliaProduct = function($context, wrapper, storageKey, storage_value) {
    Drupal.removeItemFromLocalStorage(storageKey);

    var wait_for_product = setTimeout(function () {
      var first_visible_product = $(wrapper + ' article[data-sku="' + storage_value.sku + '"]:visible:first', $context);
      clearTimeout(wait_for_product);
      if (typeof first_visible_product === 'undefined') {
        return;
      }

      if (first_visible_product.length > 0) {
        var elementVisible = $(first_visible_product).isElementInViewPort($('.branding__menu').height());
        // If element is not visible, only then scroll.
        if (elementVisible === false) {
          $('html, body').animate({
            scrollTop: ($(first_visible_product).offset().top - $('.branding__menu').height())
          }, 400);
        }
      }
    }, 700);
  }

  /**
   * Adjust the grid view when back from PDP to listing page.
   *
   * @param $context
   *   The current context.
   * @param className
   *   The className in which we have find the elements context.
   * @param storageKey
   *   The local storage key.
   * @param storage_value
   *   The local storage value.
   */
  Drupal.algolia.adjustAlgoliaGridView = function($context, className, storageKey, storage_value) {
    // Prepare grid type class as per storage value.
    var grid_class_remove = storage_value.grid_type == 'small' ? 'large' : 'small';
    var $algoliaProductList = $('.c-products-list', $context);
    $algoliaProductList.removeClass('product-' + grid_class_remove);
    $algoliaProductList.addClass('product-' + storage_value.grid_type);
    $algoliaProductList.addClass(className);
    $context.find('.' + grid_class_remove  + '-col-grid').removeClass('active');
    $context.find('.' + storage_value.grid_type + '-col-grid').addClass('active');
    // Remove the grid_type property once applied when back from list
    // so that on next page load, default behavior is used.
    delete storage_value.grid_type;
    Drupal.addItemInLocalStorage(storageKey, storage_value);
  }
 // Adding to use global.
  Drupal.algoliaGetActualPageNumber = function () {
    var resultsCount = $('.node--view-mode-search-result:visible').length;
    // Set to itemsPerPage by default.
    var perPage = parseInt(drupalSettings.algoliaSearch.itemsPerPage, 10);
    // If hitsPerPage is disabled check if value exists in localStorage.
    if (drupalSettings.algoliaSearch.hitsPerPage === false) {
      // Pick from localStorage if available.
      perPage = localStorage.getItem('defaultHitsPerPage') !== null ? parseInt(localStorage.getItem('defaultHitsPerPage'), 10) : parseInt(drupalSettings.algoliaSearch.itemsPerPage, 10);
    }
    return Math.ceil(resultsCount / perPage);
  }

  // Set global variable to flag express delivery label display on teaser.
  // This flag will be set for all listing page teaser express-delivery label
  // component. Since API is called only once this global variable will be used
  // to show / hide delivery labels on teaser on filter and search listing.
  window.sddEdStatus = window.sddEdStatus || true;

})(jQuery, Drupal);
