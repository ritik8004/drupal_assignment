/**
 * @file
 * JS for Back to Search.
 */

(function ($) {

  Drupal.processBackToSearch = function () {
    // On page load, apply filter/sort if any.
    $('#alshaya-algolia-autocomplete').once('back-to-search').each(function () {
      var storageKey = `search:${window.location.hash}`;
      var storage_value = Drupal.algolia.getAlgoliaStorageValues(storageKey);
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
