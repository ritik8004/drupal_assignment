/**
 * @file
 * JS for Back to Search.
 */

(function ($) {
  'use strict';

  Drupal.processBackToSearch = function (storage_value) {
    // On page load, apply filter/sort if any.
    $('html').once('back-to-list').each(function () {
      var storageKey = window.location.hash;
      if (typeof storage_value !== 'undefined' && storage_value !== null) {
        var $context = $('#alshaya-algolia-search');
        // To adjust the grid view mode.
        if (typeof storage_value.grid_type !== 'undefined') {
          Drupal.algolia.adjustAlgoliaGridView($context, 'back-to-search', storageKey, storage_value);
        }

        if (typeof storage_value.sku !== 'undefined') {
          Drupal.algolia.scrollToAlgoliaProduct($context, '.view-search', storageKey, storage_value);
        }
      }
    });
  };

}(jQuery));
