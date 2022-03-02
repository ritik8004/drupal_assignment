/**
 * @file
 * Alshaya VE non transac Store Finder.
 */

(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.storeFinderUpdateCountry = {
    attach: function (context, settings) {
      // Changing country exposed form submit button id as it was conflicting with store finder search box.
      $('#views-exposed-form-stores-finder-page-2 .form-actions input').attr('id', 'edit-submit-stores-finder-country');
      // Reset active class on store finder page on page load or ajax load.
      $('#views-exposed-form-stores-finder-page-1 a.map-view-link').removeClass('active');
      $('#views-exposed-form-stores-finder-page-1 a#edit-list-view').addClass('active');
      $('.block-store-finder-form__list-view').on('click', function () {
        $('.page-store-finder .views-view__attachment-after').show();
        $('div.view-id-stores_finder .view-store-finder--list__columns').hide();
        $(this).addClass('active');
        $('#views-exposed-form-stores-finder-page-1 a#edit-list-view').removeClass('active');
      });

      $('#edit-country').on('change', function (event, ui) {
        var progress_element = $('<div class="ajax-progress ajax-progress-fullscreen">&nbsp;</div>');
        $('body').after(progress_element);
        Drupal.nonTransacVeApplySearch();
      });
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
                  $.each(results[0].address_components, function ( index, value ) {
                    if (value.types[0] == 'country') {
                      var cookie_options = {path: '/', expires: 7200, secure: true};
                      $.cookie('alshaya_client_country_code', value.short_name.toLowerCase(), cookie_options);
                    }
                  });
                }
              }
            });
          });
        }
      }

      var name = Drupal.getQueryVariable('country');
      if (name.length === 0 || typeof name === 'undefined') {
        drupalSettings.geolocation.geocoder.googlePlacesAPI.restrictions.country = drupalSettings.storeFinder.storeCountries;
        if($('#edit-country').length && jQuery.cookie('alshaya_client_country_code') && jQuery.inArray(jQuery.cookie('alshaya_client_country_code').toLowerCase(), drupalSettings.storeFinder.storeCountries) !== -1) {
          drupalSettings.geolocation.geocoder.googlePlacesAPI.restrictions.country = [jQuery.cookie('alshaya_client_country_code').toLowerCase()];
        }
      }
      else {
        drupalSettings.geolocation.geocoder.googlePlacesAPI.restrictions.country = [name];
        if (name == 'All') {
          drupalSettings.geolocation.geocoder.googlePlacesAPI.restrictions.country = drupalSettings.storeFinder.storeCountries;
        }
      }
    }
  };

  /**
   * Helper function to set default country.
   */
  Drupal.nonTransacVeDefaultCountry = function () {
    if (drupalSettings.geolocation.geocoder.googlePlacesAPI.restrictions.country.length == 1) {
      var countryCode = drupalSettings.geolocation.geocoder.googlePlacesAPI.restrictions.country[0];
      var optionExists = (jQuery("#views-exposed-form-stores-finder-page-2 select option[value=" + countryCode + "]").length > 0);
      if (optionExists) {
        var name = Drupal.getQueryVariable('country');
        if (name.length === 0 || typeof name === 'undefined') {
          jQuery("#views-exposed-form-stores-finder-page-2 select option[value=" + countryCode + "]").prop('selected',true);
          Drupal.nonTransacVeApplySearch();
        }
      }
    }
  };

  /**
   * Helper function to trigger apply button.
   */
  Drupal.nonTransacVeApplySearch = function () {
    jQuery('#views-exposed-form-stores-finder-page-2 [id^="edit-submit-stores-finder"]').trigger('click');
  };

  // Set default country option on page load using client's detected country.
  $( window ).on( 'load', Drupal.nonTransacVeDefaultCountry );
})(jQuery, Drupal, drupalSettings);
