/**
 * @file
 * Browser location access.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  // Coordinates of the user's location.
  var asCoords = null;
  Drupal.click_collect = Drupal.click_collect || {};

  // Error callback.
  Drupal.click_collect.LocationError = function (error) {
    Drupal.ajax({
      url: Drupal.url('location-access-blocked-warning'),
      element: $('#store-finder-wrapper').get(0),
      base: false,
      progress: {type: 'throbber'},
      submit: {js: true}
    }).execute();
    // Display search store form if conditions matched.
    Drupal.click_collect.LocationAccessError(drupalSettings);
  };

  // Success callback.
  Drupal.click_collect.LocationSuccess = function (position) {
    asCoords = {
      lat: position.coords.latitude,
      lng: position.coords.longitude
    };
    Drupal.click_collect.LocationAccessSuccess(asCoords);
  };

})(jQuery, Drupal, drupalSettings);
