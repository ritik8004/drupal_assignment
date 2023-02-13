/**
 * @file
 * PLP All Filters Panel & Facets JS file.
 */
(function ($, Drupal, drupalSettings) {

    /**
   * Helper function get the algolia_search_clicks value.
   *
   * @param customerType
   */
    Drupal.fetchSkuAlgoliaInsightsClickData = function () {
        var algoliaClicks = Drupal.getItemFromLocalStorage('algolia_search_clicks');
        var sku= Object.keys(drupalSettings.productInfo);
        if (algoliaClicks && algoliaClicks[sku] !== undefined  && algoliaClicks[sku] !== null
          && typeof algoliaClicks[sku] !== 'string') {
        var dataInsightsQueryId = algoliaClicks[sku]['query-id'];
        $('.data-insights-query-class').attr('data-insights-query-id',dataInsightsQueryId);
      }
    };
    Drupal.fetchSkuAlgoliaInsightsClickData();
})(jQuery, Drupal, drupalSettings);
