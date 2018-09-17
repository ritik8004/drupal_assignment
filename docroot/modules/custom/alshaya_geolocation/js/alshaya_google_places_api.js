(function ($, Drupal) {
  'use strict';

  /* global google */

  /**
   * @namespace
   */
  Drupal.click_collect = Drupal.click_collect || {};
  Drupal.geolocation = Drupal.geolocation || {};

  // Global var for no result found html.
  var noResultHtml = '<div class="pac-not-found"><span>' + Drupal.t('No area found') + '</span><div>';

  // Global var for storing location autocomplete instance.
  var location_autocomplete_instance_clone = null;
  var location_autocomplete_no_result_checked = null;

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
  Drupal.AlshayaPlacesAutocomplete = function (field, callbacks, restriction, $trigger) {
    var places_autocomplete = this;

    try {
      if (location_autocomplete_instance_clone !== null) {
        location_autocomplete_instance_clone.unbindAll();
        google.maps.event.clearInstanceListeners(field);
        $(".pac-container").remove();
        location_autocomplete_instance = null;
      }
    }
    catch (e) {
    }

    var location_autocomplete_instance = places_autocomplete.googleAutocomplete(field);
    location_autocomplete_instance_clone = Object.create(location_autocomplete_instance);

    // Set restriction for autocomplete.
    if (!$.isEmptyObject(restriction)) {
      location_autocomplete_instance.setComponentRestrictions(restriction);
    }

    location_autocomplete_instance.addListener('place_changed', function () {
      // Get the place details from the autocomplete object.
      var place = location_autocomplete_instance.getPlace();

      // Remove no results message.
      $('.pac-container').find('.pac-not-found').remove();

      try {
        // Try to clear any no result check timeouts if exist.
        clearTimeout(location_autocomplete_no_result_checked);
      }
      catch (e) {
      }
      places_autocomplete.coords = Drupal.AlshayaPlacesAutocomplete.getLatLong(place);

      if ($.isArray(callbacks)) {
        callbacks.forEach(function (callback) {
          callback.call(this, places_autocomplete.coords, field, restriction, $trigger);
        });
      }
    });

    $(field).once('autocomplete-init').on('keyup', function (e) {
      var keyCode = e.keyCode || e.which;
      if (keyCode === 13) {
        var geocoder = Drupal.AlshayaPlacesAutocomplete.getGeocoder();
        geocoder.geocode({
          'componentRestrictions': {
            'locality': $(this).val(),
            'country': restriction.country
          }
        }, function (results, status) {
          if (status == 'OK') {
            var coords = Drupal.AlshayaPlacesAutocomplete.getLatLong(results[0]);
            if ($.isEmptyObject(coords) || results[0].address_components.length <= 1) {
              return false;
            }
            else if ($.isArray(callbacks)) {
              callbacks.forEach(function (callback) {
                callback.call(null, coords, field, restriction, $trigger);
              });
            }
          }
        });
      }
      else if ($(this).val().length > 0) {
        // Remove the no results found html, we will add again in timeout if no results.
        $('.pac-container').find('.pac-not-found').remove();

        try {
          clearTimeout(location_autocomplete_no_result_checked);
        }
        catch (e) {
        }

        var place = location_autocomplete_instance.getPlace();

        if (typeof place === 'undefined' || typeof place.geometry === 'undefined') {
          var total = $('.pac-container').length;

          if (total > 1) {
            $('.pac-container').each(function (i) {
              if ($(this).css("width") == '0px') {
                $(this).remove();
              }
              else if (i+1 >= total) {
                $(this).find('.pac-item').remove();
              }
            });
          }

          location_autocomplete_no_result_checked = setTimeout(function () {
            if ($('.pac-container').find('.pac-item').length <= 0) {
              $('.pac-container').html(noResultHtml);
              $('.pac-container').show();

              // Now we check every 100ms.
              location_autocomplete_no_result_checked = setTimeout(Drupal.click_collect.locationAutocompleteCheckNoResultsCase, 100);
            }
          }, 1000);
        }
      }
      else {
        $('.pac-container').hide();
      }
    });

  };

  // Initialize autocomplete for given field.
  Drupal.AlshayaPlacesAutocomplete.prototype.googleAutocomplete = function (field) {
    // Create the autocomplete object, restricting the search to geographical
    // location types.
    return new google.maps.places.Autocomplete(
      (field),
      {types: ['geocode']}
    );
  };

  // Get Geocoder object.
  Drupal.AlshayaPlacesAutocomplete.getGeocoder = function () {
    if (typeof Drupal.geolocation.geocoder.googleGeocodingAPI.geocoder === 'undefined') {
      Drupal.geolocation.geocoder.googleGeocodingAPI.geocoder = new google.maps.Geocoder();
    }
    return Drupal.geolocation.geocoder.googleGeocodingAPI.geocoder;
  };

  // Get latitude and longitude from given object.
  Drupal.AlshayaPlacesAutocomplete.getLatLong = function (place) {
    var coords = {};
    if (typeof place !== 'undefined' && typeof place.geometry !== 'undefined') {
      coords = {
        lat: place.geometry.location.lat(),
        lng: place.geometry.location.lng()
      };
    }
    return coords;
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

  // Ask for location permission.
  Drupal.click_collect.getCurrentPosition = function (successCall, ErrorCall) {
    // Get the permission track the user location.
    try {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(successCall, ErrorCall, {timeout: 10000});
      }
    }
    catch (e) {
      // Empty
    }
  };

  // Function to check and remove no results message or keep checking
  // for results to arrive.
  Drupal.click_collect.locationAutocompleteCheckNoResultsCase = function () {
    $('.pac-container').find('.pac-not-found').remove();

    if ($('.pac-container').find('.pac-item').length <= 0) {
      $('.pac-container').html(noResultHtml);
      $('.pac-container').show();

      // We still check every 100ms as we are not sure when we will get the result.
      location_autocomplete_no_result_checked = setTimeout(Drupal.click_collect.locationAutocompleteCheckNoResultsCase, 100);
    }
  };

  $.fn.clickCollectScrollTop = function () {
    window.scrollTo(0, 0);
  };

})(jQuery, Drupal);
