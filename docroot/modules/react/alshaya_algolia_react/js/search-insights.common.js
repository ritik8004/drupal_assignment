/**
 * @file
 * This file contains code for integration with Algolia Insights for analytics.
 */

(function ($, Drupal, drupalSettings) {

  /**
   * Get Algolia userToken for Search Insights.
   *
   * @returns {string}
   */
  Drupal.getAlgoliaUserToken = function () {
    if (drupalSettings.userDetails === undefined || drupalSettings.userDetails.userID === undefined || !(drupalSettings.userDetails.userID)) {
      return $.cookie('_ALGOLIA').toString();
    }

    return drupalSettings.userDetails.userID.toString();
  };

  /**
   * Push insights to Algolia when product added to the cart.
   * @param queryId
   *   Unique search identifier.
   * @param product
   *   jQuery object which contains all gtm attributes.
   */
  Drupal.pushAlshayaAlgoliaInsightsAddToCart = function (queryId, product) {
    // Convert the product to a jQuery object, if not already.
    if (!(product instanceof jQuery) && typeof product !== 'undefined') {
      product = $(product);
    }

    try {
      window.aa('convertedObjectIDsAfterSearch', {
        userToken: Drupal.getAlgoliaUserToken(),
        eventName: 'Add to cart',
        index: "...",
        queryID: queryId,
        objectIDs: [product.attr('data-insights-object-id')]
      });
    }
    catch (e) {
      console.error(e);
    }
  };

})(jQuery, Drupal, drupalSettings);
