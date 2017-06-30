(function ($, Drupal) {
  'use strict';

  /* global google */

  var geoPerm;
  var lastCoords;
  var coords;
  var progressElement = '<div class="ajax-progress ajax-progress-throbber"><div class="throbber">&nbsp;</div></div>';
  var storeList;

  Drupal.clickAndCollect = Drupal.clickAndCollect || {};
  Drupal.geolocation = Drupal.geolocation || {};

  Drupal.behaviors.clickAndCollect = {
    attach: function (context, settings) {
      if (typeof Drupal.geolocation.loadGoogle === 'function') {
        // First load the library from google.
        Drupal.geolocation.loadGoogle(function () {
          var field = $('#edit-guest-delivery-collect').find('input[name="store_location"]')[0];

          var autocomplete = new google.maps.places.Autocomplete(
            (field),
            {types: ['geocode']}
          );

          // When the user selects an address from the dropdown, populate the address
          // fields in the form.
          autocomplete.addListener('place_changed', function () {
            // Get the place details from the autocomplete object.
            var place = autocomplete.getPlace();

            var coords = {
              lat: place.geometry.location.lat(),
              lng: place.geometry.location.lng()
            };
            Drupal.clickAndCollect.storesList(coords);
          });
        });
      }

      $('.tab').once('initiate-stores').on('click', function () {
        if ($(this).hasClass('tab-click-collect') && $('#click-and-collect-list-view').html().length <= 0) {
          // Display the loader.
          $('#click-and-collect-list-view').html(progressElement);

          // Get the permission track the user location.
          try {
            if (navigator.geolocation) {
              navigator.geolocation.getCurrentPosition(Drupal.clickAndCollect.locationSuccess, Drupal.clickAndCollect.locationError);
            }
          }
          catch (e) {
            // console.log(navigator.geolocation);
          }
        }
      });

      // Select this store click
      $('#edit-guest-delivery-collect', context).once('bind-events').on('click', 'a.stores-list-view, a.stores-map-view', function (e) {
        if (e.target.className === 'stores-list-view') {
          e.preventDefault();
          $('#click-and-collect-list-view').show();
          $('#click-and-collect-map-view').hide();
          return false;
        }
        else if (e.target.className === 'stores-map-view') {
          e.preventDefault();
          $('#click-and-collect-list-view').hide();
          $('#click-and-collect-map-view').show();
          return false;
        }
      });

      // Select this store click
      $('#click-and-collect-list-view', context).once('bind-events').on('click', 'a[data-store-code]', function (e) {
        if (e.target.className === 'select-store') {
          var selectedStoreObj = _.findWhere(storeList, {code: $(this).data('store-code')});
          Drupal.clickAndCollect.selectedStoreEvents($(this), selectedStoreObj);
        }
      });

      $('#selected-store-wrapper', context).once('bind-events').on('click', 'a.change-store', function (e) {
        if (e.target.className === 'change-store') {
          $('#selected-store-wrapper').hide();
          $('#store-finder-wrapper').show();
        }
      });

      if (geoPerm) {
        Drupal.clickAndCollect.storesList(coords);
      }
    }
  };

  // Error callback.
  Drupal.clickAndCollect.locationError = function (error) {
    // Do nothing, we already have the search form displayed by default.
    geoPerm = false;
    $('#click-and-collect-list-view').html('');
  };

  // Success callback.
  Drupal.clickAndCollect.locationSuccess = function (position) {

    /* coords = {
      lat: position.coords.latitude,
      lng: position.coords.longitude
    }; */
    geoPerm = true;

    coords = {
      lat: 29.3204817,
      lng: 48.04878039999994
    };
    Drupal.clickAndCollect.storesList(coords);
  };

  // Make Ajax call to get stores list and render html.
  Drupal.clickAndCollect.storesList = function (coords) {
    if (coords !== null) {

      var cartId = drupalSettings.alshaya_acm_checkout.cart_id;
      var checkLocation = true;

      if (typeof lastCoords !== 'undefined' && lastCoords !== null) {
        checkLocation = (lastCoords.lat !== coords.lat || lastCoords.lng !== coords.lng);
      }

      if (checkLocation) {
        lastCoords = coords;

        $.ajax({
          url: Drupal.url('click-and-collect/stores/cart/' + cartId + '/' + coords.lat + '/' + coords.lng),
          beforeSend: function (xmlhttprequest) {
            $('#click-and-collect-list-view').html(progressElement);
          },
          success: function (response) {
            storeList = response.raw;
            $('#click-and-collect-list-view').html(response.output);
            Drupal.clickAndCollect.storeMapView(storeList);
          }
        });
      }
    }
  };

  // Render html for Selected store.
  Drupal.clickAndCollect.selectedStoreEvents = function (selectedButton, selectedStoreObj) {

    $.ajax({
      url: Drupal.url('click-and-collect/selected-store'),
      type: 'post',
      data: selectedStoreObj,
      dataType: 'json',
      beforeSend: function (xmlhttprequest) {
        // selectedButton.ladda('start');
      },
      success: function (response) {
        $('#selected-store-wrapper > #selected-store-content').html(response.output);
        $('#selected-store-wrapper').show();
        $('#store-finder-wrapper').hide();
      }
    });
  };

  // Display map view.
  Drupal.clickAndCollect.storeMapView = function (storeList) {

    var geolocationMap = {};
    var mapWrapper = $('#click-and-collect-map-view');
    geolocationMap.settings = {};
    geolocationMap.settings.google_map_settings = drupalSettings.geolocation.google_map_settings;
    geolocationMap.container = mapWrapper.children('.geolocation-common-map-container');
    geolocationMap.container.show();
    geolocationMap.lat = coords.lat;
    geolocationMap.lng = coords.lng;
    geolocationMap.googleMap = Drupal.geolocation.addMap(geolocationMap);
    // Invoke function to add marker for each store.
    _.invoke(storeList, Drupal.clickAndCollect.addMarker, {geolocationMap: geolocationMap});
  };

  // Add marker to map.
  Drupal.clickAndCollect.addMarker = function (param) {
    var store = this;
    var position = new google.maps.LatLng(parseFloat(store.lat), parseFloat(store.lng));
    var markerConfig = {
      position: position,
      map: param.geolocationMap.googleMap,
      title: store.name,
      infoWindowContent: store.address,
      infoWindowSolitary: true,
      icon: param.geolocationMap.settings.google_map_settings.marker_icon_path
    };

    // var marker = ;
    Drupal.geolocation.setMapMarker(param.geolocationMap, markerConfig, false);

    /*
    marker.addListener('click', function () {
      var target = $('[data-location-id="' + location.data('location-id') + '"]:visible').first();

      // Alternatively select by class.
      if (target.length === 0) {
        target = $('.geolocation-location-id-' + location.data('location-id') + ':visible').first();
      }

      if (target.length === 1) {
        $('html, body').animate({
          scrollTop: target.offset().top
        }, 'slow');
      }
    });
    */
  };

})(jQuery, Drupal);
