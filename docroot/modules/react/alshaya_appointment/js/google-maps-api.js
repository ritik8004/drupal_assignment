/* eslint-disable */

/**
 * @file
 * Javascript to load the Google map api.
 */

(function ($, Drupal) {
  'use strict';

  /**
   * @namespace
   */
  Drupal.alshayaAppointment = Drupal.alshayaAppointment || {};

  Drupal.behaviors.alshayaAppointmentGoogleMap = {
    attach: function (context, drupalSettings) {
      if (typeof Drupal.alshayaAppointment.loadGoogle === 'function') {
        // First load the library from google.
        Drupal.alshayaAppointment.loadGoogle();
      }
    }
  };

  /**
   * Load Google Maps.
   */
  Drupal.alshayaAppointment.loadGoogle = function () {
    // Check for Google Maps.
    if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
      if (Drupal.alshayaAppointment.maps_api_loading === true) {
        return;
      }

      Drupal.alshayaAppointment.maps_api_loading = true;
      // Google Maps isn't loaded so lazy load Google Maps.

      if (typeof drupalSettings.alshaya_appointment.google_map_api_key !== 'undefined') {
        $.getScript('https://maps.googleapis.com/maps/api/js?key=' + drupalSettings.alshaya_appointment.google_map_api_key + '&libraries=places,geometry&language=' +  drupalSettings.path.currentLanguage)
          .done(function () {
            Drupal.alshayaAppointment.maps_api_loading = false;
          });
      } else {
        Drupal.logJavascriptError('checkout', 'Google map url could not be loaded.');
      }
    }
  };

})(jQuery, Drupal);
