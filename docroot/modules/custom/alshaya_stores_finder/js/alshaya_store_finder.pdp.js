/**
 * @file
 * Store Finder - PDP.
 */

(function ($, Drupal) {
  'use strict';

  // Coordinates of the user's location.
  var asf_coords = null;

  // Last checked SKU (or variant SKU).
  var last_sku = null;

  // Last coords.
  var last_coords = null;

  // Geolocation permission.
  var geo_perm = false;

  // Check records already exists.
  var records = false;

  var autocomplete;

  var all_store_autocomplete;

  Drupal.pdp = Drupal.pdp || {};
  Drupal.geolocation = Drupal.geolocation || {};

  Drupal.behaviors.store_finder_pdp = {
    attach: function (context, settings) {

      $('#pdp-stores-container').once('initiate-stores').each(function () {
        try {
          if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(Drupal.geolocation.currentLocationSuccessCallback, Drupal.geolocation.currentLocationErrorCallback);
          }
        }
        catch (e) {
        }
      });

      $('.click-collect-top-stores').once('bind-events').on('click', '.other-stores-link', function () {
        $('.click-collect-all-stores').slideToggle();
      });

      $('.click-collect-all-stores').once('bind-events').on('click', '.close-inline-modal, .change-store-link, .search-stores-button', function (e) {
        if (e.target.className == 'change-store-link') {
          $(this).siblings('.search-store').show();
        }
        else if (e.target.className == 'search-stores-button' && !records) {
          e.preventDefault();

          var asf_coords = {
            latitude: $('input[name="latitude"]').val(),
            longitude: $('input[name="longitude"]').val()
          };

          // Drupal.pdp.getFormattedAddress(asf_coords.latitude, asf_coords.longitude, $('.click-collect-all-stores').find('.google-store-location'));
          Drupal.pdp.stores_display(asf_coords);

          return false;
        }
        else {
          $('.click-collect-all-stores').slideToggle();
        }
      });

      $('.click-collect-form').once('bind-events').on('click', '.change-location-link, .search-stores-button', function (e) {
        if (e.target.className == 'change-location-link') {
          $(this).siblings('.search-store').show();
        }
        else if (e.target.className == 'search-stores-button' && !records) {
          e.preventDefault();
          var asf_coords = {
            latitude: $('input[name="latitude"]').val(),
            longitude: $('input[name="longitude"]').val()
          };
          Drupal.pdp.stores_display(asf_coords);
          return false;
        }
      });

      if (!geo_perm) {
        Drupal.pdp.dispalySearchStoreForm();
      }

      // Call here once to ensure we do it after changes in attribute selection.
      Drupal.pdp.stores_display();
    }
  };

  Drupal.behaviors.alshaya_store_finder_autocomplete = {
    attach: function (context, settings) {

      if (typeof Drupal.geolocation.loadGoogle === 'function') {
        // First load the library from google.
        Drupal.geolocation.loadGoogle(function () {
          var field = $('.click-collect-form').find('input[name="location"]')[0];
          // var field = $('#all-stores-search-store').find('input[name="location"]')[0];
          autocomplete = Drupal.geolocation.initAutocomplete(field);

          // When the user selects an address from the dropdown, populate the address
          // fields in the form.
          autocomplete.addListener('place_changed', function() {
            // Get the place details from the autocomplete object.
            var place = autocomplete.getPlace();
            Drupal.geolocation.getStores(place);
          });
        });
      }
    }
  };

  // Error callback.
  Drupal.geolocation.currentLocationErrorCallback = function (error) {
    geo_perm = false;
    // Do nothing, we already have the search form displayed by default.
  };

  // Success callback.
  Drupal.geolocation.currentLocationSuccessCallback = function (position) {
    asf_coords = {
      latitude: position.coords.latitude,
      longitude: position.coords.longitude
    };
    geo_perm = true;
    Drupal.pdp.stores_display();
  };

  // Initialize autocomplete for given field.
  Drupal.geolocation.initAutocomplete = function(field) {
    // Create the autocomplete object, restricting the search to geographical
    // location types.
    return new google.maps.places.Autocomplete(
      (field),
      {types: ['geocode']}
    );
  };

  // Fill stores for the given place.
  Drupal.geolocation.getStores = function(place) {
    $('input[name="latitude"]').val(place.geometry.location.lat());
    $('input[name="longitude"]').val(place.geometry.location.lng());

    if (records) {
      var asf_coords = {
        latitude: place.geometry.location.lat(),
        longitude: place.geometry.location.lng()
      };

      // Drupal.pdp.getFormattedAddress(asf_coords.latitude, asf_coords.longitude, $('.click-collect-all-stores').find('.google-store-location'));
      Drupal.pdp.stores_display(asf_coords);
    }
  };

  // Make autocomplete field in search form in the all stores.
  Drupal.pdp.all_store_autocomplete = function() {
    var field = $('#all-stores-search-store').find('input[name="location"]')[0];
    all_store_autocomplete = Drupal.geolocation.initAutocomplete(field);
    all_store_autocomplete.addListener('place_changed', function() {
      // Get the place details from the autocomplete object.
      var place = all_store_autocomplete.getPlace();
      Drupal.geolocation.getStores(place);
    });
  };

  // Dispaly search store form.
  Drupal.pdp.dispalySearchStoreForm = function() {
    var sku_clean = $('#pdp-stores-container').attr('sku-clean');
    if ($('.selected-variant-sku-' + sku_clean).length) {
      $('.click-collect-empty-selection').hide();
      $('.click-collect-form').show();
      $('.click-collect-form').find('.available-store-text').hide();
      $('.click-collect-form').find('.store-finder-form-wrapper .change-location-link').hide();
      $('.click-collect-form').find('.store-finder-form-wrapper .search-store').show();
    }
  };

  // Get formatted address from geocode.
  Drupal.pdp.getFormattedAddress = function(latitude, longitude, $target) {
    if (typeof Drupal.geolocation.geocoder.googleGeocodingAPI.geocoder === 'undefined') {
      Drupal.geolocation.geocoder.googleGeocodingAPI.geocoder = new google.maps.Geocoder();
    }
    var geocoder = Drupal.geolocation.geocoder.googleGeocodingAPI.geocoder;
    var latlng = {lat: parseFloat(latitude), lng: parseFloat(longitude)};
    geocoder.geocode({location: latlng}, function (results, status) {
      if (status === 'OK') {
        $target.html(results[2].formatted_address);
      }
    });
  };

  // Make Ajax call to get stores and render html.
  Drupal.pdp.stores_display = function (coords) {
    if (coords) {
      asf_coords = coords;
    }

    // Get the SKU.
    var sku = $('#pdp-stores-container').attr('sku');
    var sku_clean = $('#pdp-stores-container').attr('sku-clean');

    // Get the type.
    var type = $('#pdp-stores-container').attr('type');

    if (type === 'configurable') {
      if ($('.selected-variant-sku-' + sku_clean).length) {
        $('.click-collect-empty-selection').hide();
        sku = $('.selected-variant-sku-' + sku_clean).val();
      }
      else {
        $('.click-collect-empty-selection').show();
        $('.click-collect-form').hide();
        return;
      }
    }

    /*if (last_coords === null) {
      asf_coords.latitude = 29.3222135;
      asf_coords.longitude = 48.04741160000003;
    }*/

    if (asf_coords !== null) {
      Drupal.pdp.getFormattedAddress(asf_coords.latitude, asf_coords.longitude, $('.click-collect-form').find('.google-store-location'));

      var check_location = true;
      if (last_coords !== null) {
        check_location = (last_coords.latitude != asf_coords.latitude || last_coords.longitude != asf_coords.longitude);
      }

      if ((last_sku === null || last_sku != sku) || check_location) {
        last_sku = sku;
        last_coords = asf_coords;

        $.ajax({
          url: Drupal.url('stores/product/' + last_sku + '/' + asf_coords.latitude + '/' + asf_coords.longitude),
          beforeSend: function(xmlhttprequest) {
            var progressElement = '<div class="ajax-progress ajax-progress-throbber"><div class="throbber">&nbsp;</div></div>';
            $('.click-collect-top-stores').html(progressElement);
            $('.click-collect-all-stores .stores-list-all').html(progressElement);
          },
          // url: Drupal.url('stores/product/' + last_sku + '/29.3222135/48.04741160000003'),
          success: function (response) {
            Drupal.pdp.fill_stores(response, asf_coords);
          }
        });
      }
    }
  };

  // Fill the stores with result.
  Drupal.pdp.fill_stores = function(response, asf_coords) {
    if (response.top_three) {
      records = true;
      $('.click-collect-top-stores').html(response.top_three);
      $('.click-collect-form').find('.search-store').hide();
      $('.click-collect-form').find('.available-store-text').show();
      if (response.all_stores) {
        $('.click-collect-all-stores').html(response.all_stores);
        $('.click-collect-all-stores').find('.store-finder-form-wrapper .search-store').find('.search-stores-button').hide();
        Drupal.pdp.getFormattedAddress(asf_coords.latitude, asf_coords.longitude, $('.click-collect-all-stores').find('.google-store-location'));
        Drupal.pdp.all_store_autocomplete();
      }
      else {
        $('.click-collect-all-stores').html('');
        $('.click-collect-all-stores').hide();
      }
    }
    else {
      $('.click-collect-top-stores').html('');
      $('.click-collect-all-stores').html('');
      $('.click-collect-form').find('.available-store-text').hide();
    }
    $('.click-collect-form').show();
    $('.click-collect-form').find('.store-finder-form-wrapper .change-location-link').show();
    $('.click-collect-form').find('.store-finder-form-wrapper .search-store').hide();
    $('.click-collect-form').find('.store-finder-form-wrapper .search-store').find('.search-stores-button').hide();

  };

})(jQuery, Drupal);
