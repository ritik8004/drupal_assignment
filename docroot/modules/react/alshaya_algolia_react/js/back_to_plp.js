/**
 * @file
 * JS for Back to PLP.
 */

(function ($) {
  'use strict';

  Drupal.processBackToPLP = function () {
    // On page load, apply filter/sort if any.
    $('html').once('back-to-list').each(function () {
      var storageKey = 'plp:' + window.location.pathname;
      var storage_value = Drupal.algolia.getAlgoliaStorageValues(storageKey);
        if (typeof storage_value !== 'undefined' && storage_value !== null) {
          var context = $('#alshaya-algolia-plp');
          // To adjust the grid view mode.
          if (typeof storage_value.grid_type !== 'undefined') {
            Drupal.algolia.adjustAlgoliaGridView(context, '.view-algolia-plp', storageKey, storage_value);
          }

          if (typeof storage_value.sku !== 'undefined') {
            Drupal.algolia.scrollToAlgoliaProduct(context, 'back-to-plp', storageKey, storage_value);
          }
        }
    });
  };

}(jQuery));
