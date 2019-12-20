/**
 * @file
 * This file contains code for integration with Algolia Insights for analytics.
 */

(function ($, Drupal, drupalSettings) {

  Drupal.getAlgoliaUserToken = function () {
    if (drupalSettings.userDetails === undefined || drupalSettings.userDetails.userID === undefined || !(drupalSettings.userDetails.userID)) {
      return $.cookie('_ALGOLIA');
    }

    return drupalSettings.userDetails.userID;
  }

})(jQuery, Drupal, drupalSettings);
