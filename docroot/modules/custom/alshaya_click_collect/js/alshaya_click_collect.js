/**
 * @file
 * Browser location access.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  // Coordinates of the user's location.
  var asCoords = null;
  Drupal.click_collect = Drupal.click_collect || {};

  // Ask for location permission.
  Drupal.click_collect.getCurrentPosition = function (successCall, ErrorCall) {
    // Get the permission track the user location.
    try {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(successCall, ErrorCall, {timeout: 10000});
      }
    }
    catch (e) {
      // Empty.
    }
  };

  // Error callback.
  Drupal.click_collect.LocationError = function (error) {
    // Display dialog when location access is blocked from browser.
    let message = Drupal.t('We need permission to locate your nearest stores. You can enable location services in your settings.');
    let locationErrorDialog = Drupal.dialog('<div id="drupal-modal">' + message + '</div>', {
      modal: true,
      width: "auto",
      height: "auto",
      title: Drupal.t('Location access denied'),
      dialogClass: 'location-disabled-notice',
      resizable: false,
      closeOnEscape: true,
      close: function close(event) {
        $(event.target).remove();
      }
    });

    locationErrorDialog.showModal();
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

  // Get formatted address from geocode.
  Drupal.click_collect.getFormattedAddress = function (coords, $target, type) {
    var geocoder = Drupal.AlshayaPlacesAutocomplete.getGeocoder();
    var latlng = {lat: parseFloat(coords.lat), lng: parseFloat(coords.lng)};
    geocoder.geocode({location: latlng}, function (results, status) {
      if (status === 'OK') {
        if (type === 'val') {
            $target.val(results[2].formatted_address);
        }
        else {
            $target.html(results[2].formatted_address);
        }
      }
    });
  };

})(jQuery, Drupal, drupalSettings);
