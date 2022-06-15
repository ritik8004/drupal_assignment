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
      return $.cookie('_ALGOLIA');
    }

    return drupalSettings.userDetails.userID.toString();
  };

  /**
   * Push insights to Algolia when product added to the cart.
   * @param queryId
   *   Unique search identifier.
   * @param objectId
   *   Sku value of the product.
   */
  Drupal.pushAlshayaAlgoliaInsightsAddToCart = function (queryId, objectId, indexName) {
    try {
      window.aa('convertedObjectIDsAfterSearch', {
        userToken: Drupal.getAlgoliaUserToken(),
        eventName: 'Add to cart',
        index: indexName,
        queryID: queryId,
        objectIDs: [objectId],
      });
    }
    catch (e) {
      console.error(e);
    }
  };

})(jQuery, Drupal, drupalSettings);
