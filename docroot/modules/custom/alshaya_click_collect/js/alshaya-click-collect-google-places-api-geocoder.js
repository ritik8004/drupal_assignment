/**
 * @file
 *   Javascript for the Google Places API geocoder.
 */

/**
 * @property {Object} drupalSettings.geolocation.geocoder.googlePlacesAPI.restrictions
 */

(function ($, Drupal) {
  'use strict';

  /**
   * @namespace
   */
  Drupal.geolocation = Drupal.geolocation || {};
  Drupal.click_collect = Drupal.click_collect || {};
  Drupal.alshayaClickCollectPlacesApi = Drupal.alshayaClickCollectPlacesApi || {};

  Drupal.behaviors.alshayaClickCollectPlacesApi = {
    attach: function (context, settings) {
      if (typeof Drupal.geolocation.loadGoogle === 'function') {

        var componentRestrictions = {};
        if (typeof drupalSettings.geolocation.geocoder.googlePlacesAPI.restrictions !== 'undefined') {
          componentRestrictions = drupalSettings.geolocation.geocoder.googlePlacesAPI.restrictions;
        }

        // First load the library from google.
        Drupal.geolocation.loadGoogle(function () {
          $('input.geolocation-geocoder-google-places-api', context).each(function () {
            var field = $(this).get(0);
            // Create autocomplete object for places.
            new Drupal.ClickCollect(field, [Drupal.alshayaClickCollectPlacesApi.storePlacesDetails], componentRestrictions, field);
          }) // Handle input and keyup events.
            .on('input', function () {
              var elementId = $(this).data('source-identifier');
              if ($("input[name='" + elementId + "-lat']").val() === '' || $("input[name='" + elementId + "-lng']").val() === '') {
                $('.geolocation-geocoder-google-places-api-state[data-source-identifier="' + $(this).data('source-identifier') + '"]').val(0);
              }
              return false;
            })
            .on('keypress', function (e) {
              var keyCode = e.keyCode || e.which;
              if (keyCode === 13) {
                e.preventDefault();
                var elementId = $(this).data('source-identifier');

                if ($("input[name='" + elementId + "-lat']").val() !== '' || $("input[name='" + elementId + "-lng']").val() !== '') {
                  $(this).parents('form').find('input[type="submit"]').attr('disabled', '');
                  $(this).parents('form').find('input[type="submit"]').click();
                }
                else {
                  return false;
                }
              }
            });
        });
      }
    }
  };

  // Set the places details to lat and lng.
  Drupal.alshayaClickCollectPlacesApi.storePlacesDetails = function (coords, field) {
    var elementId = $(field).data('source-identifier');
    var lat = (typeof coords.lat !== 'undefined') ? coords.lat : '';
    var lng = (typeof coords.lng !== 'undefined') ? coords.lng : '';
    var stateValue = 0;
    if (!$.isEmptyObject(coords)) {
      stateValue = 1;
    }

    $("input[name='" + elementId + "-lat']").val(lat);
    $("input[name='" + elementId + "-lng']").val(lng);

    $('.geolocation-geocoder-google-places-api-state[data-source-identifier="' + elementId + '"]').val(stateValue);

    if (!$.isEmptyObject(coords)) {
      $(field).parents('form').find('input[type="submit"]').click();
    }
  };

})(jQuery, Drupal);
