/**
 * @file
 * PLP All Filters Panel & Facets JS file.
 */
(function ($, Drupal) {

    /**
   * Helper function get the algolia_search_clicks value.
   *
   * @param customerType
   */
    Drupal.algolia_insights_query_pdp = function () {
        var algoliaClicks = Drupal.getItemFromLocalStorage('algolia_search_clicks');
        var sku= $('#data-insights-query-id').attr('data-sku');
        if (algoliaClicks && algoliaClicks[sku] !== undefined  && algoliaClicks[sku] !== null
          && typeof algoliaClicks[sku] !== 'string') {
        var dataInsightsQueryId = algoliaClicks[sku]['query-id'];
        $('#data-insights-query-id').attr('data-insights-query-id');
        $('#data-insights-query-id').attr('data-insights-query-id',dataInsightsQueryId);
      }
      
    };
    Drupal.algolia_insights_query_pdp();
})(jQuery, Drupal);
