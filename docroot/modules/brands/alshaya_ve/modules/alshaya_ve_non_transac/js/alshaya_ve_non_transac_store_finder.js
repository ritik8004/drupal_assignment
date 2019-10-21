/**
 * @file
 * Alshaya VE non transac Store Finder.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';
  /**
   * @namespace
   */
  Drupal.behaviors.storeFinderGlossaryMap = {
    attach: function (context, settings) {
      // Reset active class on store finder page on page load or ajax load.
      $('#views-exposed-form-stores-finder-page-1 a.map-view-link').removeClass('active');
      $('#views-exposed-form-stores-finder-page-1 a#edit-list-view').addClass('active');
      $('.block-store-finder-form__list-view').on('click', function () {
        $('.path--store-finder .attachment-after').show();
        $('div.view-id-stores_finder .view-content:first').hide();
        $(this).addClass('active');
        $('#views-exposed-form-stores-finder-page-1 a#edit-list-view').removeClass('active');
        $('.path--store-finder .attachment-after').css("width", "100%");
      });
    }
  };

  Drupal.behaviors.storeFinderCountrySelected = {
    attach: function (context, settings) {
      $('#edit-country').on('change', function (event, ui) {
        var progress_element = $('<div class="ajax-progress ajax-progress-fullscreen">&nbsp;</div>');
        $('body').after(progress_element);
        setTimeout(function () {
          $('#views-exposed-form-stores-finder-page-2 [id^="edit-submit-stores-finder"]').trigger('click');
        }, 500);
      });
    }
  };

  Drupal.behaviors.storeFinderSetLatLongCookie = {
    attach: function (context, settings) {
      var country_code = $.cookie('alshaya_client_country_code');
      if (typeof country_code === 'undefined' || !country_code) {
        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(function (position) {
            var cookie_options = {path: '/', expires: 7200, secure: true};
            var latlng = {lat: parseFloat(position.coords.latitude), lng: parseFloat(position.coords.longitude)};
            var geocoder = Drupal.AlshayaPlacesAutocomplete.getGeocoder();
            geocoder.geocode({location: latlng}, function (results, status) {
              if (status === 'OK') {
                if (results[0].address_components) {
                  $.each(results[0].address_components, function( index, value ) {
                    if (value.types[0] == 'country') {
                      var cookie_options = {path: '/', expires: 7200, secure: true};
                      $.cookie('alshaya_client_country_code', value.short_name, cookie_options);
                    }
                  });
                }
              }
            });
          });
        }
      }
    }
  };

  // Set default country option on page load using client's detected country.
  $( window ).on( 'load', defaultSelectCountryOption );
})(jQuery, Drupal, drupalSettings);



function defaultSelectCountryOption() {
  if (drupalSettings.geolocation.geocoder.googlePlacesAPI.restrictions.country.length == 1) {
    var countryCode = drupalSettings.geolocation.geocoder.googlePlacesAPI.restrictions.country[0];
    var optionExists = (jQuery("#views-exposed-form-stores-finder-page-2 select option[value=" + countryCode + "]").length > 0);
    if (optionExists) {
      jQuery("#views-exposed-form-stores-finder-page-2 select option[value=" + countryCode + "]").prop('selected',true);
    }
  }
}


