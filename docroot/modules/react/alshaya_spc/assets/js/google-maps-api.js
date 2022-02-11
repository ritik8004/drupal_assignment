/* eslint-disable */

/**
 * @file
 * Javascript to load the Google map api.
 */

(function ($, Drupal) {

  /**
   * @namespace
   */
  Drupal.alshayaSpc = Drupal.alshayaSpc || {};

  Drupal.behaviors.alshayaSpcGoogleMap = {
    attach: function (context, drupalSettings) {
      if (typeof Drupal.alshayaSpc.loadGoogle === 'function') {
        // First load the library from google.
        Drupal.alshayaSpc.loadGoogle();
      }
    }
  };

  /**
   * Load Google Maps.
   */
  Drupal.alshayaSpc.loadGoogle = function () {
    // Check for Google Maps.
    if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
      if (Drupal.alshayaSpc.maps_api_loading === true) {
        return;
      }

      Drupal.alshayaSpc.maps_api_loading = true;
      // Google Maps isn't loaded so lazy load Google Maps.

      if (typeof drupalSettings.map.google_api_key !== 'undefined') {
        $.getScript('https://maps.googleapis.com/maps/api/js?key=' + drupalSettings.map.google_api_key + '&libraries=places&language=' +  drupalSettings.path.currentLanguage)
          .done(function () {
            Drupal.alshayaSpc.maps_api_loading = false;
          });
      } else {
        Drupal.logJavascriptError('checkout', 'Google map url could not be loaded.', GTM_CONSTANTS.CHECKOUT_ERRORS);
      }
    }
  };

})(jQuery, Drupal);
