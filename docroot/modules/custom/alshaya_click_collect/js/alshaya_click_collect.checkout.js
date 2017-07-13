(function ($, Drupal) {
  'use strict';

  /* global google */

  // Browser geo permision.
  var geoPerm;
  // Last selected coordinates.
  var lastCoords;
  // Selected coordinates.
  var ascoords;
  // Store list
  var storeList;
  // Index to show in marker as label.
  var index;
  // Map wrapper
  var mapWrapper = $('#click-and-collect-map-view');

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
          var field = $('.store-location-input')[0];
          // Create autocomplete object for places.
          new Drupal.ClickCollect(field, [Drupal.checkoutClickCollect.storeListAll]);
        });
      }

      $('body').once('get-location').each(function () {
        // Get the permission track the user location.
        Drupal.click_collect.getCurrentPosition(Drupal.checkoutClickCollect.locationSuccess, Drupal.checkoutClickCollect.locationError);
      });

      $('.hours--wrapper > .hours--label').on('click', function () {
        $(this).toggleClass('open');
      });

      if (settings.alshaya_click_collect.selected_store) {
        if ($('#selected-store-wrapper').is(':visible')) {
          $('input[data-drupal-selector="edit-actions-ccnext"]').show();
        }
        $('[data-drupal-selector="edit-actions-next"]').hide();
      }

      $('#click-and-collect-list-view').once('initiate-stores').each(function () {
        $('input[data-drupal-selector="edit-actions-ccnext"]').hide();
        Drupal.checkoutClickCollect.storeListAll(ascoords);
      });

      // Toggle between store list view and map view.
      $('#edit-guest-delivery-collect, #edit-member-delivery-collect', context).once('bind-events').on('click', 'a.stores-list-view, a.stores-map-view', function (e) {
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

          if (typeof ascoords !== 'undefined') {
            var map = Drupal.checkoutClickCollect.mapCreate();
            var center = map.googleMap.getCenter();
            google.maps.event.trigger(map.googleMap, 'resize');
            map.googleMap.setCenter(center);
          }
          return false;
        }
      });

      // Select this store and view on map.
      $('#click-and-collect-list-view', context).once('bind-events').on('click', 'a.select-store, a.store-on-map', function (e) {
        e.preventDefault();

        // Find the store object with the given store-code from the store list.
        var storeObj = _.findWhere(storeList, {code: $(this).closest('li').data('store-code')});

        if (e.target.className.indexOf('select-store') >= 0) {
          // Choose the selected store to proceed with checkout.
          Drupal.checkoutClickCollect.storeSelectedStore($(this), storeObj);
        }
        else if (e.target.className.indexOf('store-on-map') >= 0) {
          $('#click-and-collect-list-view').hide();
          $('#click-and-collect-map-view').show();
          $('.stores-list-view').toggleClass('active');
          $('.stores-map-view').toggleClass('active');
          // Choose the selected store to display on map.
          Drupal.checkoutClickCollect.storeViewOnMapSelected($(this), storeObj);
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
        }
      });

      $('#selected-store-wrapper', context).once('bind-events').on('click', 'a.change-store', function (e) {
        if (e.target.className === 'change-store') {
          $('#selected-store-wrapper').hide();
          $('#store-finder-wrapper').show();
          $('input[data-drupal-selector="edit-actions-ccnext"]').hide();
        }
      });

      // Load the store list if geoperm is true.
      if (geoPerm && typeof ascoords !== 'undefined') {
        Drupal.checkoutClickCollect.storeListAll(ascoords);
      }

      // Select click and collect if value available.
      $('#edit-delivery-tabs').once('select-default').each(function () {
        var selectedTab = $('#selected-tab').val();
        $('.tab[gtm-type="' + selectedTab + '"]', $(this)).trigger('click');

        if ($('.form-item-cc-mobile-number-mobile').is(':visible')) {
          // If we are coming back to delivery from payment we show the button.
          $('input[data-drupal-selector="edit-actions-ccnext"]').show();
        }
        else {
          $('input[data-drupal-selector="edit-actions-ccnext"]').hide();
        }
      });
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
  };

  // Make Ajax call to get stores list and render html.
  Drupal.checkoutClickCollect.storeListAll = function (coords) {
    if (typeof coords !== 'undefined') {
      ascoords = coords;

      var cartId = drupalSettings.alshaya_click_collect.cart_id;
      var checkLocation = true;

      if (typeof lastCoords !== 'undefined' && lastCoords !== null) {
        checkLocation = (lastCoords.lat !== ascoords.lat || lastCoords.lng !== ascoords.lng);
      }

      if (checkLocation) {
        lastCoords = ascoords;

        var storeListAjax = Drupal.ajax({
          url: Drupal.url('click-and-collect/stores/cart/' + cartId + '/' + ascoords.lat + '/' + ascoords.lng),
          element: $('#store-finder-wrapper').get(0),
          base: false,
          progress: {type: 'throbber'},
          submit: {js: true}
        });

        // Custom command function to render map and map markers.
        storeListAjax.commands.clickCollectStoresView = function (ajax, response, status) {
          if (status === 'success') {
            storeList = response.data.raw;
            var showMap = false;

            if (storeList !== null && storeList.length > 0) {
              Drupal.click_collect.getFormattedAddress(ascoords, $('#click-and-collect-list-view').find('.selected-store-location'));
              var map = Drupal.checkoutClickCollect.mapCreate();
              Drupal.geolocation.removeMapMarker(map);
              Drupal.checkoutClickCollect.storeViewOnMapAll(storeList);
              if ($('a.stores-map-view').hasClass('active')) {
                showMap = true;
              }
            }
            $('#click-and-collect-map-view').toggle(showMap);
          }
        };

        storeListAjax.execute();
      }
    }
    else {
      $('#click-and-collect-list-view').html('');
    }
  };

  // Render html for Selected store.
  Drupal.checkoutClickCollect.storeSelectedStore = function (selectedButton, StoreObj) {
    Drupal.ajax({
      url: Drupal.url('click-and-collect/selected-store'),
      element: selectedButton.get(0),
      base: false,
      progress: {type: 'throbber'},
      submit: StoreObj
    }).execute();
  };

  // View selected store on map
  Drupal.checkoutClickCollect.storeViewOnMapSelected = function (selectedButton, StoreObj) {
    // Create/Get map object.
    var map = Drupal.checkoutClickCollect.mapCreate();
    // Adjust the map, when we trigger the map view.
    google.maps.event.trigger(map.googleMap, 'resize');
    // Get the index of current location.
    var index = (parseInt(selectedButton.closest('li').data('index')) - 1);
    // Zoom the current map to store location.
    map.googleMap.setZoom(11);
    // Make the marker by default open.
    google.maps.event.trigger(map.mapMarkers[index], 'click');
    // Get the lat/lng of current store to center the map.
    var newLocation = new google.maps.LatLng(parseFloat(StoreObj.lat), parseFloat(StoreObj.lng));
    // Set the google map center.
    map.googleMap.setCenter(newLocation);
  };

  // Display All the stores on map.
  Drupal.checkoutClickCollect.storeViewOnMapAll = function (storeItems) {
    if (storeItems === null) {
      storeItems = storeList;
    }
    // Create/Get map object.
    var map = Drupal.checkoutClickCollect.mapCreate();

    // Set the index to 0 to display marker label starting with 1.
    index = 0;

    if (map) {
      // Invoke function to add marker for each store.
      _.invoke(storeItems, Drupal.checkoutClickCollect.mapPushMarker, {geolocationMap: map});
    }
  };

  // Create map.
  Drupal.checkoutClickCollect.mapCreate = function () {
    // Create googleMap if property is not set.
    // Tried to mimic from /contrib/geolocation/js/geolocation-common-map.js.
    if (typeof geolocationMap.googleMap === 'undefined') {
      geolocationMap.container = mapWrapper.children('.geolocation-common-map-container');
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
    locationEle = locationEle.wrapInner('<div class="scroll-fix"></div>');
    var position = new google.maps.LatLng(parseFloat(store.lat), parseFloat(store.lng));
    var markerConfig = {
      position: position,
      map: mapObj.googleMap,
      title: store.name,
      infoWindowContent: locationEle.html(),
      infoWindowSolitary: true,
      label: (index).toString()
    };
    return Drupal.geolocation.setMapMarker(mapObj, markerConfig, false);
  };

})(jQuery, Drupal);
