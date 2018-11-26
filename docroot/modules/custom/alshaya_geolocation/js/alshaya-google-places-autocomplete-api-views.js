/**
 * @file
 *   Javascript for the Google Places API geocoder.
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
          $('.geolocation-geocoder-google-places-api[type="search"]', context).once('bind-events').each(function () {
            var field = $(this).get(0);
            // Create autocomplete object for places.
            new Drupal.AlshayaPlacesAutocomplete(field, [Drupal.alshayaClickCollectPlacesApi.storePlacesDetails], componentRestrictions, field);

            // Handle input event.
            $(this).on('input', function () {
              var elementId = $(this).data('source-identifier');
              if ($("input[name='" + elementId + "-lat']").val() === '' || $("input[name='" + elementId + "-lng']").val() === '') {
                $('.geolocation-geocoder-google-places-api-state[data-source-identifier="' + $(this).data('source-identifier') + '"]').val(0);
              }
              return false;
            });

            // Handle keyup event.
            $(this).on('keyup', function (e) {
              var keyCode = e.keyCode || e.which;
              if (keyCode === 13) {
                e.preventDefault();
                var elementId = $(this).data('source-identifier');

                if ($("input[name='" + elementId + "-lat']").val() === '' || $("input[name='" + elementId + "-lng']").val() === '') {
                  return false;
                }
              }
            });
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
    $("input[name='geolocation_geocoder_google_places_api_state']").val(stateValue);

    if (!$.isEmptyObject(coords)) {
      // Show progress bar on store-finder page.
      if (!$('[data-drupal-selector="edit-list-view"]').hasClass('hidden')) {
        var progress_element = $('<div class="ajax-progress ajax-progress-fullscreen">&nbsp;</div>');
        $('body').after(progress_element);
      }
      $(field).parents('form').find('[type="submit"]').click();
    }
  };

})(jQuery, Drupal);
