(function ($, Drupal) {
  'use strict';

  /* global google */

  // Browser geo permision.
  var geoPerm;
  // Last selected coordinates.
  var lastCoords;
  // Selected coordinates.
  var coords;
  // Default progress element copied from /core/misc/ajax.js.
  var progressElement = '<div class="ajax-progress ajax-progress-throbber"><div class="throbber">&nbsp;</div></div>';
  // Store list
  var storeList;
  // Index to show in marker as label.
  var index;

  // Geolocation map object.
  var geolocationMap = {};
  geolocationMap.settings = {};
  geolocationMap.settings.google_map_settings = drupalSettings.geolocation.google_map_settings;

  Drupal.clickAndCollect = Drupal.clickAndCollect || {};
  Drupal.geolocation = Drupal.geolocation || {};

  Drupal.behaviors.clickAndCollect = {
    attach: function (context, settings) {
      if (typeof Drupal.geolocation.loadGoogle === 'function') {
        // First load the library from google.
        Drupal.geolocation.loadGoogle(function () {
          var field = $('#edit-guest-delivery-collect').find('input[name="store_location"]')[0];
          // Create autocomplete object for places.
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
            // Get all available stores for the selected coordinates.
            Drupal.clickAndCollect.storeListAll(coords);
          });
        });
      }

      // Ask for location permission when click and collect tab selected.
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
            // Empty.
          }
        }
      });

      // Toggle between store list view and map view.
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

      // Select this store and view on map.
      $('#click-and-collect-list-view', context).once('bind-events').on('click', 'a[data-store-code]', function (e) {
        e.preventDefault();
        // Find the store object with the given store-code from the store list.
        var storeObj = _.findWhere(storeList, {code: $(this).data('store-code')});
        // Choose the selected store to proceed with checkout.
        if (e.target.className === 'select-store') {
          Drupal.clickAndCollect.storeSelectedStore($(this), storeObj);
        }
        // Choose the selected store to display on map.
        if (e.target.className === 'store-on-map') {
          Drupal.clickAndCollect.storeViewOnMapSelected($(this), storeObj);
          $('#click-and-collect-list-view').hide();
          $('#click-and-collect-map-view').show();
        }
        return false;
      });

      $('#selected-store-wrapper', context).once('bind-events').on('click', 'a.change-store', function (e) {
        if (e.target.className === 'change-store') {
          $('#selected-store-wrapper').hide();
          $('#store-finder-wrapper').show();
        }
      });

      // Load the store list if geoperm is true.
      if (geoPerm && typeof coords !== 'undefined') {
        Drupal.clickAndCollect.storeListAll(coords);
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
    Drupal.clickAndCollect.storeListAll(coords);
  };

  // Make Ajax call to get stores list and render html.
  Drupal.clickAndCollect.storeListAll = function (coords) {
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
            Drupal.clickAndCollect.storeViewOnMapAll(storeList);
          }
        });
      }
    }
  };

  // Render html for Selected store.
  Drupal.clickAndCollect.storeSelectedStore = function (selectedButton, StoreObj) {

    $.ajax({
      url: Drupal.url('click-and-collect/selected-store'),
      type: 'post',
      data: StoreObj,
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

  // View selected store on map
  Drupal.clickAndCollect.storeViewOnMapSelected = function (selectedButton, StoreObj) {
    // Create/Get map object.
    var map = Drupal.clickAndCollect.mapCreate();
    // Remove any/all existing marker.
    Drupal.geolocation.removeMapMarker(map);
    // Create a new marker with the current store information.
    var thismarker = Drupal.clickAndCollect.mapCreateMarker(StoreObj, map, 1);
    // Make the marker by default open.
    google.maps.event.trigger(thismarker, 'click');
  };

  // Display All the stores on map.
  Drupal.clickAndCollect.storeViewOnMapAll = function (storeItems) {
    if (storeItems === null) {
      storeItems = storeList;
    }
    // Create/Get map object.
    var map = Drupal.clickAndCollect.mapCreate();

    if (map) {
      // Set the index to 0 to display marker label starting with 1.
      index = 0;
      // Invoke function to add marker for each store.
      _.invoke(storeItems, Drupal.clickAndCollect.mapPushMarker, {geolocationMap: map});
    }
  };

  // Create map.
  Drupal.clickAndCollect.mapCreate = function () {
    // Create googleMap if property is not set.
    // Tried to mimic from /contrib/geolocation/js/geolocation-common-map.js.
    if (typeof geolocationMap.googleMap === 'undefined') {
      var mapWrapper = $('#click-and-collect-map-view');
      geolocationMap.container = mapWrapper.children('.geolocation-common-map-container');
      geolocationMap.container.show();
      geolocationMap.lat = coords.lat;
      geolocationMap.lng = coords.lng;
      geolocationMap.googleMap = Drupal.geolocation.addMap(geolocationMap);
    }
    return geolocationMap;
  };

  // push marker to add to map.
  Drupal.clickAndCollect.mapPushMarker = function (param, extra) {
    index++;
    Drupal.clickAndCollect.mapCreateMarker(this, param.geolocationMap, index);
  };

  // Create marker on map for the given store object.
  Drupal.clickAndCollect.mapCreateMarker = function (store, mapObj, index) {
    // Copied from /contrib/geolocation/js/geolocation-common-map.js.
    var position = new google.maps.LatLng(parseFloat(store.lat), parseFloat(store.lng));
    var markerConfig = {
      position: position,
      map: mapObj.googleMap,
      title: store.name,
      infoWindowContent: store.address,
      infoWindowSolitary: true,
      label: (index).toString()
    };
    // markerConfig.icon: param.geolocationMap.settings.google_map_settings.marker_icon_path;
    return Drupal.geolocation.setMapMarker(mapObj, markerConfig, false);
  };

})(jQuery, Drupal);
