(function ($, Drupal) {
  'use strict';

  /* global google */

  // Browser geo permision.
  var geoPerm;
  // Last selected coordinates.
  var lastCoords;
  // Selected coordinates.
  var ascoords;
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

  Drupal.checkoutClickCollect = Drupal.checkoutClickCollect || {};
  Drupal.geolocation = Drupal.geolocation || {};

  Drupal.behaviors.checkoutClickCollect = {
    attach: function (context, settings) {
      if (typeof Drupal.geolocation.loadGoogle === 'function') {
        // First load the library from google.
        Drupal.geolocation.loadGoogle(function () {
          var field = $('#edit-guest-delivery-collect').find('input[name="store_location"]')[0];
          // Create autocomplete object for places.
          new Drupal.ClickCollect(field, [Drupal.checkoutClickCollect.storeListAll]);

        });
      }

      // Ask for location permission when click and collect tab selected.
      $('.tab').once('initiate-stores').on('click', function () {
        if ($(this).hasClass('tab-click-collect') && $('#click-and-collect-list-view').html().length <= 0) {
          // Display the loader.
          $('#click-and-collect-list-view').html(progressElement);

          // Get the permission track the user location.
          Drupal.click_collect.getCurrentPosition(Drupal.checkoutClickCollect.locationSuccess, Drupal.checkoutClickCollect.locationError);
        }
      });

      // Toggle between store list view and map view.
      $('#edit-guest-delivery-collect', context).once('bind-events').on('click', 'a.stores-list-view, a.stores-map-view', function (e) {
        if (e.target.className === 'stores-list-view') {
          e.preventDefault();
          $('#click-and-collect-list-view').show();
          $('#click-and-collect-map-view').hide();
          $('.stores-list-view').toggleClass('active');
          $('.stores-map-view').toggleClass('active');
          return false;
        }
        else if (e.target.className === 'stores-map-view') {
          e.preventDefault();
          $('#click-and-collect-list-view').hide();
          $('#click-and-collect-map-view').show();
          $('.stores-list-view').toggleClass('active');
          $('.stores-map-view').toggleClass('active');
          return false;
        }
      });

      // Select this store and view on map.
      $('#click-and-collect-list-view', context).once('bind-events').on('click', 'a.select-store, a.store-on-map', function (e) {
        e.preventDefault();
        // Find the store object with the given store-code from the store list.
        var storeObj = _.findWhere(storeList, {code: $(this).closest('li').data('store-code')});
        // Choose the selected store to proceed with checkout.
        if (e.target.className === 'select-store') {
          Drupal.checkoutClickCollect.storeSelectedStore($(this), storeObj);
        } // Choose the selected store to display on map.
        else if (e.target.className === 'store-on-map') {
          Drupal.checkoutClickCollect.storeViewOnMapSelected($(this), storeObj);
          $('a.stores-map-view').trigger('click');

          /* $('#click-and-collect-list-view').hide();
          $('#click-and-collect-map-view').show();
          $('.stores-list-view').toggleClass('active');
          $('.stores-map-view').toggleClass('active'); */
        }
        return false;
      });

      // Select this store and view on map.
      $('#click-and-collect-map-view', context).once('bind-events').on('click', 'a.select-store', function (e) {
        e.preventDefault();
        // Choose the selected store to proceed with checkout.
        if (e.target.className === 'select-store') {
          // Find the store object with the given store-code from the store list.
          var storeObj = _.findWhere(storeList, {code: $(this).data('store-code')});
          Drupal.checkoutClickCollect.storeSelectedStore($(this), storeObj);
          $(this).addClass('ajax-ladda-spinner');
        }
      });

      $('#selected-store-wrapper', context).once('bind-events').on('click', 'a.change-store', function (e) {
        if (e.target.className === 'change-store') {
          $('#selected-store-wrapper').hide();
          $('#store-finder-wrapper').show();
        }
      });

      // Load the store list if geoperm is true.
      if (geoPerm && typeof ascoords !== 'undefined') {
        Drupal.checkoutClickCollect.storeListAll(ascoords);
      }
    }
  };

  // Success callback.
  Drupal.checkoutClickCollect.locationSuccess = function (position) {
    ascoords = {
      lat: position.coords.latitude,
      lng: position.coords.longitude
    };
    geoPerm = true;
    Drupal.checkoutClickCollect.storeListAll(ascoords);
  };

  // Error callback.
  Drupal.checkoutClickCollect.locationError = function (error) {
    // Do nothing, we already have the search form displayed by default.
    geoPerm = false;
    $('#click-and-collect-list-view').html('');
  };

  // Make Ajax call to get stores list and render html.
  Drupal.checkoutClickCollect.storeListAll = function (coords) {
    if (typeof this.lat !== 'undefined' && typeof coords === 'undefined') {
      coords = this;
    }

    if (typeof coords !== 'undefined') {
      ascoords = coords;

      var cartId = drupalSettings.alshaya_click_collect.cart_id;
      var checkLocation = true;

      if (typeof lastCoords !== 'undefined' && lastCoords !== null) {
        checkLocation = (lastCoords.lat !== ascoords.lat || lastCoords.lng !== ascoords.lng);
      }

      if (checkLocation) {
        lastCoords = ascoords;

        $.ajax({
          url: Drupal.url('click-and-collect/stores/cart/' + cartId + '/' + ascoords.lat + '/' + ascoords.lng),
          beforeSend: function (xmlhttprequest) {
            $('#click-and-collect-list-view').html(progressElement);
            $('#click-and-collect-map-view > .geolocation-common-map-container').html(progressElement);
          },
          success: function (response) {
            storeList = response.raw;
            $('#click-and-collect-list-view').html(response.output);
            $('#click-and-collect-map-view > .geolocation-common-map-locations').html(response.mapList);
            var $element = $('#click-and-collect-list-view').find('.selected-store-location');
            Drupal.click_collect.getFormattedAddress(ascoords.lat, ascoords.lng, $element);
            Drupal.checkoutClickCollect.storeViewOnMapAll(storeList);
          },
          complete: function (xmlhttprequest, status) {
            if (status === 'error' || status === 'parsererror') {
              $('#click-and-collect-list-view').html(Drupal.t('There\'s some error'));
              $('#click-and-collect-map-view').html(Drupal.t('There\'s some error'));
              throw new Error(Drupal.t('The callback URL is not working: !url', {'!url': 'test'}));
            }
          }
        });
      }
    }
  };

  // Render html for Selected store.
  Drupal.checkoutClickCollect.storeSelectedStore = function (selectedButton, StoreObj) {
    $.ajax({
      url: Drupal.url('click-and-collect/selected-store'),
      type: 'post',
      data: StoreObj,
      dataType: 'json',
      beforeSend: function (xmlhttprequest) {
        selectedButton.addClass('ajax-ladda-spinner');
      },
      success: function (response) {
        $('#selected-store-wrapper > #selected-store-content').html(response.output);
        $('#selected-store-wrapper').show();
        $('#store-finder-wrapper').hide();
      },
      complete: function (xmlhttprequest, status) {
        if (status === 'error' || status === 'parsererror') {
          $('#selected-store-wrapper > #selected-store-content').html(Drupal.t('There\'s some error'));
        }
        selectedButton.removeClass('ajax-ladda-spinner');
      }
    });
  };

  // View selected store on map
  Drupal.checkoutClickCollect.storeViewOnMapSelected = function (selectedButton, StoreObj) {
    // Create/Get map object.
    var map = Drupal.checkoutClickCollect.mapCreate();
    // Get the index of current location.
    var index = (parseInt(selectedButton.closest('li').data('index')) - 1);
    // Get the lat/lng of current store to center the map.
    var newLocation = new google.maps.LatLng(parseFloat(StoreObj.lat), parseFloat(StoreObj.lng));
    map.googleMap.setCenter(newLocation);
    // Zoom the current map to store location.
    map.googleMap.setZoom(11);
    // Make the marker by default open.
    google.maps.event.trigger(map.mapMarkers[index], 'click');
  };

  // Display All the stores on map.
  Drupal.checkoutClickCollect.storeViewOnMapAll = function (storeItems) {
    if (storeItems === null) {
      storeItems = storeList;
    }
    // Create/Get map object.
    var map = Drupal.checkoutClickCollect.mapCreate();

    if (map) {
      // Set the index to 0 to display marker label starting with 1.
      index = 0;
      // Invoke function to add marker for each store.
      _.invoke(storeItems, Drupal.checkoutClickCollect.mapPushMarker, {geolocationMap: map});
    }
  };

  // Create map.
  Drupal.checkoutClickCollect.mapCreate = function () {
    // Create googleMap if property is not set.
    // Tried to mimic from /contrib/geolocation/js/geolocation-common-map.js.
    if (typeof geolocationMap.googleMap === 'undefined') {
      var mapWrapper = $('#click-and-collect-map-view');
      geolocationMap.container = mapWrapper.children('.geolocation-common-map-container');
      geolocationMap.container.show();
      geolocationMap.lat = ascoords.lat;
      geolocationMap.lng = ascoords.lng;
      geolocationMap.googleMap = Drupal.geolocation.addMap(geolocationMap);
    }
    return geolocationMap;
  };

  // push marker to add to map.
  Drupal.checkoutClickCollect.mapPushMarker = function (param, extra) {
    index++;
    Drupal.checkoutClickCollect.mapCreateMarker(this, param.geolocationMap, index);
  };

  // Create marker on map for the given store object.
  Drupal.checkoutClickCollect.mapCreateMarker = function (store, mapObj, index) {
    // Copied from /contrib/geolocation/js/geolocation-common-map.js.
    var locationEle = $('.geolocation-common-map-locations').find('.geolocation[data-store-code="' + store.code + '"]');
    var position = new google.maps.LatLng(parseFloat(store.lat), parseFloat(store.lng));
    var markerConfig = {
      position: position,
      map: mapObj.googleMap,
      title: store.name,
      infoWindowContent: locationEle.html(),
      infoWindowSolitary: true,
      label: (index).toString()
    };
    // markerConfig.icon: param.geolocationMap.settings.google_map_settings.marker_icon_path;
    return Drupal.geolocation.setMapMarker(mapObj, markerConfig, false);
  };

})(jQuery, Drupal);
