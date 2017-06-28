/**
 * @file
 * Store Finder - PDP.
 */

(function ($, Drupal) {
  'use strict';

  /* global google */

  // Coordinates of the user's location.
  var asfCoords = null;

  // Last checked SKU (or variant SKU).
  var lastSku = null;

  // Last coords.
  var lastCoords = null;

  // Geolocation permission.
  var geoPerm = false;

  // Check records already exists.
  var records = false;

  var autocomplete;

  Drupal.pdp = Drupal.pdp || {};
  Drupal.pdp.allStores = Drupal.pdp.allStores || {};
  Drupal.geolocation = Drupal.geolocation || {};

  Drupal.behaviors.storeFinderPdp = {
    attach: function (context, settings) {

      $('#pdp-stores-container', context).once('initiate-stores').each(function () {
        // Get the permission track the user location.
        try {
          if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(Drupal.geolocation.currentLocationSuccessCallback, Drupal.geolocation.currentLocationErrorCallback);
          }
        }
        catch (e) {
          // Empty
        }
      });

      $('.click-collect-top-stores', context).once('bind-events').on('click', '.other-stores-link', function () {
        $('.click-collect-all-stores').toggle('slow', function () {
          // Scroll
          $('html,body').animate({
            scrollTop: $('.click-collect-all-stores').offset().top
          }, 'slow');
        });

      });

      $('.click-collect-all-stores', context).once('bind-events').on('click', '.close-inline-modal, .change-store-link, .search-stores-button, .cancel-change-location', function (e) {
        if (e.target.className === 'change-store-link') {
          // $(this).parent().siblings('.store-finder-form-wrapper').find('.search-store').show();
          $(this).siblings('.change-location').show();
          $(this).hide();
        }
        else if (e.target.className === 'cancel-change-location') {
          e.preventDefault();
          $(this).parent().hide();
          $('.click-collect-all-stores').find('.available-store-text .change-location-link').show();
          return false;
        }
        else if (e.target.className === 'search-stores-button' && !records) {
          e.preventDefault();
          var coords = {
            latitude: $('input[name="latitude"]').val(),
            longitude: $('input[name="longitude"]').val()
          };

          Drupal.pdp.storesDisplay(coords);
          return false;
        }
        else {
          $('.click-collect-all-stores').toggle('slow');
        }
      });

      $('.click-collect-form', context).once('bind-events').on('click', '.change-location-link, .search-stores-button, .cancel-change-location', function (e) {
        if (e.target.className === 'change-location-link') {
          $(this).siblings('.change-location').show();
          $(this).hide();
        }
        else if (e.target.className === 'cancel-change-location') {
          e.preventDefault();
          $(this).parent().hide();
          $('.click-collect-form').find('.available-store-text .change-location-link').show();
          return false;
        }
        else if (e.target.className === 'search-stores-button' && !records) {
          e.preventDefault();
          var asfCoords = {
            latitude: $('input[name="latitude"]').val(),
            longitude: $('input[name="longitude"]').val()
          };
          Drupal.pdp.storesDisplay(asfCoords);
          return false;
        }
      });

      // Call here once to ensure we do it after changes in attribute selection.
      if (settings.alshaya_acm.storeFinder === true) {
        Drupal.pdp.storesDisplay();
      }

      // If geolocation permission is denied then display the search form.
      if (!geoPerm) {
        Drupal.pdp.dispalySearchStoreForm();
      }

    }
  };

  Drupal.behaviors.alshayaStoreFinderAutocomplete = {
    attach: function (context, settings) {

      if (typeof Drupal.geolocation.loadGoogle === 'function') {
        // First load the library from google.
        Drupal.geolocation.loadGoogle(function () {
          var field = $('.click-collect-form').find('input[name="location"]')[0];
          autocomplete = Drupal.geolocation.initAutocomplete(field);

          // When the user selects an address from the dropdown, populate the address
          // fields in the form.
          autocomplete.addListener('place_changed', function () {
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
    geoPerm = false;
    // Do nothing, we already have the search form displayed by default.
  };

  // Success callback.
  Drupal.geolocation.currentLocationSuccessCallback = function (position) {
    asfCoords = {
      latitude: position.coords.latitude,
      longitude: position.coords.longitude
    };
    geoPerm = true;
    Drupal.pdp.storesDisplay();
  };

  // Initialize autocomplete for given field.
  Drupal.geolocation.initAutocomplete = function (field) {
    // Create the autocomplete object, restricting the search to geographical
    // location types.
    return new google.maps.places.Autocomplete(
      (field),
      {types: ['geocode']}
    );
  };

  // Fill stores for the given place.
  Drupal.geolocation.getStores = function (place) {
    $('input[name="latitude"]').val(place.geometry.location.lat());
    $('input[name="longitude"]').val(place.geometry.location.lng());

    if (records) {
      var coords = {
        latitude: place.geometry.location.lat(),
        longitude: place.geometry.location.lng()
      };

      Drupal.pdp.storesDisplay(coords);
    }
  };

  Drupal.pdp.changeLocationAutocomplete = function () {
    var field = $('.click-collect-form').find('input[name="store-location"]')[0];
    var allStoresAutocomplete = Drupal.geolocation.initAutocomplete(field);
    allStoresAutocomplete.addListener('place_changed', function () {
      // Get the place details from the autocomplete object.
      var place = allStoresAutocomplete.getPlace();
      Drupal.geolocation.getStores(place);
    });
  };

  // Dispaly search store form.
  Drupal.pdp.dispalySearchStoreForm = function () {
    var skuClean = $('#pdp-stores-container').attr('sku-clean');
    if ($('.selected-variant-sku-' + skuClean).length) {
      $('.click-collect-empty-selection').hide();
      $('.click-collect-form').show();
      $('.click-collect-form').find('.available-store-text').hide();
      $('.click-collect-form').find('.store-finder-form-wrapper .search-store').show();
    }
  };

  // Get formatted address from geocode.
  Drupal.pdp.getFormattedAddress = function (latitude, longitude, $target) {
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
  Drupal.pdp.storesDisplay = function (coords) {
    if (coords) {
      asfCoords = coords;
    }

    // Get the SKU.
    var sku = $('#pdp-stores-container').attr('sku');
    var skuClean = $('#pdp-stores-container').attr('sku-clean');

    // Get the type.
    var type = $('#pdp-stores-container').attr('type');

    if (type === 'configurable') {
      if ($('.selected-variant-sku-' + skuClean).length) {
        $('.click-collect-empty-selection').hide();
        sku = $('.selected-variant-sku-' + skuClean).val();
      }
      else {
        $('.click-collect-empty-selection').show();
        $('.click-collect-form').hide();
        return;
      }
    }

    if (asfCoords !== null) {
      Drupal.pdp.getFormattedAddress(asfCoords.latitude, asfCoords.longitude, $('.click-collect-form').find('.google-store-location'));

      var checkLocation = true;
      if (lastCoords !== null) {
        checkLocation = (lastCoords.latitude !== asfCoords.latitude || lastCoords.longitude !== asfCoords.longitude);
      }

      if ((lastSku === null || lastSku !== sku) || checkLocation) {
        lastSku = sku;
        lastCoords = asfCoords;

        $.ajax({
          url: Drupal.url('stores/product/' + lastSku + '/' + asfCoords.latitude + '/' + asfCoords.longitude),
          beforeSend: function (xmlhttprequest) {
            var progressElement = '<div class="ajax-progress ajax-progress-throbber"><div class="throbber">&nbsp;</div></div>';
            $('.click-collect-top-stores').html(progressElement);
            $('.click-collect-all-stores .stores-list-all').html(progressElement);
          },
          success: function (response) {
            Drupal.pdp.fillStores(response, asfCoords);
          }
        });
      }
    }
  };

  // Fill the stores with result.
  Drupal.pdp.fillStores = function (response, asfCoords) {
    if (response.top_three) {
      records = true;
      $('.click-collect-top-stores').html(response.top_three);
      $('.click-collect-form').find('.store-finder-form-wrapper .search-store').hide();
      $('.click-collect-form').find('.change-location').hide();
      $('.click-collect-form').find('.available-store-text').show();
      $('.click-collect-form').find('.available-store-text .change-location-link').show();
      Drupal.pdp.changeLocationAutocomplete();
      if (response.all_stores) {
        $('.click-collect-all-stores').html(response.all_stores);
        Drupal.pdp.getFormattedAddress(asfCoords.latitude, asfCoords.longitude, $('.click-collect-all-stores').find('.google-store-location'));
        Drupal.pdp.allStores.Autocomplete();
        Drupal.pdp.allStores.changeLocationAutocomplete();
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
      $('.click-collect-form').find('.store-finder-form-wrapper .search-store').show();
    }
    $('.click-collect-form').show();
  };

  // Make autocomplete field in search form in the all stores.
  Drupal.pdp.allStores.Autocomplete = function () {
    var field = $('#all-stores-search-store').find('input[name="location"]')[0];
    var allStoresAutocomplete = Drupal.geolocation.initAutocomplete(field);
    allStoresAutocomplete.addListener('place_changed', function () {
      // Get the place details from the autocomplete object.
      var place = allStoresAutocomplete.getPlace();
      Drupal.geolocation.getStores(place);
    });
  };

  Drupal.pdp.allStores.changeLocationAutocomplete = function () {
    var field = $('.click-collect-all-stores').find('input[name="store-location"]')[0];
    var allStoresAutocomplete = Drupal.geolocation.initAutocomplete(field);
    allStoresAutocomplete.addListener('place_changed', function () {
      // Get the place details from the autocomplete object.
      var place = allStoresAutocomplete.getPlace();
      Drupal.geolocation.getStores(place);
    });
  };


})(jQuery, Drupal);
