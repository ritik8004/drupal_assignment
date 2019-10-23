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
                      $.cookie('alshaya_client_country_code', value.short_name.toLowerCase(), cookie_options);
                      // drupalSettings.geolocation.geocoder.googlePlacesAPI.restrictions.country = [value.short_name.toLowerCase()];
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


  Drupal.behaviors.storeFinderUpdateCountry = {
    attach: function (context, settings) {
      var country_code = $.cookie('alshaya_client_country_code');
      var name = GetParameterValues('country');
      var options = $('#views-exposed-form-stores-finder-page-2 select option').map(function() { return $(this).val(); }).get();
      if (typeof name === 'undefined') {
        if(jQuery.cookie('alshaya_client_country_code')) {
          if(jQuery.inArray(jQuery.cookie('alshaya_client_country_code').toLowerCase(), options) !== -1) {
            drupalSettings.geolocation.geocoder.googlePlacesAPI.restrictions.country = [jQuery.cookie('alshaya_client_country_code').toLowerCase()];
          }
          else {
            drupalSettings.geolocation.geocoder.googlePlacesAPI.restrictions.country = options.slice(1);
          }
        }
        else {
          drupalSettings.geolocation.geocoder.googlePlacesAPI.restrictions.country = options.slice(1);
        }
      }
      else {
        if (name == 'All') {
          drupalSettings.geolocation.geocoder.googlePlacesAPI.restrictions.country = options.slice(1);
        }
        else {
          drupalSettings.geolocation.geocoder.googlePlacesAPI.restrictions.country = [name];
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
      var name = GetParameterValues('country');
      if (typeof name === 'undefined') {
        jQuery("#views-exposed-form-stores-finder-page-2 select option[value=" + countryCode + "]").prop('selected',true);
        setTimeout(function () {
          jQuery('#views-exposed-form-stores-finder-page-2 [id^="edit-submit-stores-finder"]').trigger('click');
        }, 500);
      }
    }
  }
}

// Function to check url parameters.
function GetParameterValues(param) {  
  var url = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');  
  for (var i = 0; i < url.length; i++) {  
    var urlparam = url[i].split('=');  
    if (urlparam[0] == param) {  
      return urlparam[1];  
    }  
  } 
}


