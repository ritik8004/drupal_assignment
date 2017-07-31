(function ($, Drupal) {
  'use strict';

  /* global google */

  /**
   * @namespace
   */
  Drupal.click_collect = Drupal.click_collect || {};
  Drupal.geolocation = Drupal.geolocation || {};

  /**
   * Click and collect constructor.
   *
   * @constructor
   *
   * @param {HTMLElement} field
   *   The html element to which we need to attach autocomplete.
   * @param {Array} callbacks
   *   The callback functions to be called on place changed
   * @param {Object} restriction
   *   The component restrictions object.
   * @param {HTMLElement} $trigger
   *   The element on which the ajax call should trigger.
   */
  Drupal.ClickCollect = function (field, callbacks, restriction, $trigger) {
    var click_collect = this;

    var intance = click_collect.googleAutocomplete(field);

    // Set restriction for autocomplete.
    if (!$.isEmptyObject(restriction)) {
      intance.setComponentRestrictions(restriction);
    }
    else if (typeof drupalSettings.alshaya_click_collect !== 'undefined' && typeof drupalSettings.alshaya_click_collect.country !== 'undefined') {
      intance.setComponentRestrictions({country: [drupalSettings.alshaya_click_collect.country]});
    }

    intance.addListener('place_changed', function () {
      // Get the place details from the autocomplete object.
      var place = intance.getPlace();

      click_collect.coords = {};
      if (typeof place.geometry !== 'undefined') {
        click_collect.coords = {
          lat: place.geometry.location.lat(),
          lng: place.geometry.location.lng()
        };
      }

      if ($.isArray(callbacks)) {
        callbacks.forEach(function (callback) {
          callback.call(this, click_collect.coords, field, restriction, $trigger);
        });
      }
    });

    // No result found.
    var $noResultEle = $('<div class="pac-not-found"><span>' + Drupal.t('No matches found for this area') + '</span><div>');

    $(field).on('keyup', function (e) {
      var keyCode = e.keyCode || e.which;
      if (keyCode === 13) {
        return false;
      }

      if ($(this).val().length > 0) {
        $noResultEle.get(0).remove();
        setTimeout(function () {
          if ($('.pac-container').last().find('.pac-item').length === 0) {
            $('.pac-container').last().html($noResultEle);
            $('.pac-container').last().show();
          }
          else {
            $noResultEle.get(0).remove();
          }
        }, 1000);
      }
    });

  };

  // Initialize autocomplete for given field.
  Drupal.ClickCollect.prototype.googleAutocomplete = function (field) {
    // Create the autocomplete object, restricting the search to geographical
    // location types.
    return new google.maps.places.Autocomplete(
      (field),
      {types: ['geocode']}
    );
  };

  // Get formatted address from geocode.
  Drupal.click_collect.getFormattedAddress = function (coords, $target) {
    if (typeof Drupal.geolocation.geocoder.googleGeocodingAPI.geocoder === 'undefined') {
      Drupal.geolocation.geocoder.googleGeocodingAPI.geocoder = new google.maps.Geocoder();
    }
    var geocoder = Drupal.geolocation.geocoder.googleGeocodingAPI.geocoder;
    var latlng = {lat: parseFloat(coords.lat), lng: parseFloat(coords.lng)};
    geocoder.geocode({location: latlng}, function (results, status) {
      if (status === 'OK') {
        $target.html(results[2].formatted_address);
      }
    });
  };

  // Ask for location permission.
  Drupal.click_collect.getCurrentPosition = function (successCall, ErrorCall) {
    // Get the permission track the user location.
    try {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(successCall, ErrorCall);
      }
    }
    catch (e) {
      // Empty
    }
  };

})(jQuery, Drupal);
