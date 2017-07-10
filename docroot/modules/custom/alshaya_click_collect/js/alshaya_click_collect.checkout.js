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
  var progressElement = $('<div class="ajax-progress ajax-progress-throbber"><div class="throbber">&nbsp;</div></div>');
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

      // Get the permission track the user location.
      Drupal.click_collect.getCurrentPosition(Drupal.checkoutClickCollect.locationSuccess, Drupal.checkoutClickCollect.locationError);

      // Ask for location permission when click and collect tab selected.
      $('.tab').once('initiate-stores').on('click', function () {
        $('input[data-drupal-selector="edit-actions-ccnext"]').hide();

        if ($(this).hasClass('tab-click-collect') && $('#click-and-collect-list-view').html().length <= 0) {
          // Display the loader.
          Drupal.checkoutClickCollect.storeListAll(ascoords);
          $('#edit-actions input:not(.cc-action)').addClass('hidden');
        }
        else {
          $('#edit-actions input:not(.cc-action)').removeClass('hidden');
        }
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
  };

  // Error callback.
  Drupal.checkoutClickCollect.locationError = function (error) {
    // Do nothing, we already have the search form displayed by default.
    geoPerm = false;
    progressElement.remove();
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
            if ($('a.stores-map-view').hasClass('active')) {
              $('#click-and-collect-map-view').after(progressElement);
            }
            else {
              $('#click-and-collect-list-view').after(progressElement);
            }
          },
          success: function (response) {
            storeList = response.raw;
            var hideMap = true;
            progressElement.remove();
            $('#click-and-collect-list-view').html(response.output);
            if (storeList !== null && storeList.length > 0) {
              mapWrapper.children('.geolocation-common-map-locations').html(response.mapList);
              var $element = $('#click-and-collect-list-view').find('.selected-store-location');
              Drupal.click_collect.getFormattedAddress(ascoords.lat, ascoords.lng, $element);
              var map = Drupal.checkoutClickCollect.mapCreate();
              Drupal.geolocation.removeMapMarker(map);
              Drupal.checkoutClickCollect.storeViewOnMapAll(storeList);
              mapWrapper.children('.geolocation-common-map-locations').hide();
              if ($('a.stores-map-view').hasClass('active')) {
                hideMap = false;
              }
            }

            if (hideMap) {
              $('#click-and-collect-map-view').hide();
            }
            else {
              $('#click-and-collect-map-view').show();
            }

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
    else {
      $('#click-and-collect-list-view').html('');
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
        $('#selected-store-wrapper').find('input[name="store_code"]').val(StoreObj.code);
        $('#selected-store-wrapper').find('input[name="shipping_type"]').val(response.shipping_type);
        $('input[data-drupal-selector="edit-actions-ccnext"]').show();
        Drupal.behaviors.cvJqueryValidate.attach(jQuery("#block-alshaya-white-label-content"));
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
