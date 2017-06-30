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
              latitude: place.geometry.location.lat(),
              longitude: place.geometry.location.lng()
            };
            Drupal.clickAndCollect.storesDisplay(coords);
          });
        });
      }

      $('.tab').once('initiate-stores').on('click', function () {
        if ($(this).hasClass('tab-click-collect')) {
          // Display the loader.
          $('#click-and-collect-list-view').html(progressElement);

          // Get the permission track the user location.
          try {
            if (navigator.geolocation) {
              navigator.geolocation.getCurrentPosition(Drupal.clickAndCollect.locationSuccess, Drupal.clickAndCollect.locationError);
            }
          }
          catch (e) {

          }
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
          $('#click-and-collect-list-view').show();
        }
      });

      if (geoPerm) {
        Drupal.clickAndCollect.storesDisplay(coords);
      }
    }
  };

  Drupal.clickAndCollect.selectedStoreEvents = function (selectedButton, selectedStoreObj) {
    $.ajax({
      url: Drupal.url('click-and-collect/selected-store'),
      type: 'post',
      data: selectedStoreObj,
      dataType: 'json',
      beforeSend: function (xmlhttprequest) {
        // Add ladda button / throbber for the select store link.
      },
      success: function (response) {
        $('#selected-store-wrapper').html(response.output).show();
        $('#click-and-collect-list-view').hide();
      }
    });
  };

  // Error callback.
  Drupal.clickAndCollect.locationError = function (error) {
    geoPerm = false;
    $('#click-and-collect-list-view').html('');
    // Do nothing, we already have the search form displayed by default.
  };

  // Success callback.
  Drupal.clickAndCollect.locationSuccess = function (position) {
    coords = {
      latitude: position.coords.latitude,
      longitude: position.coords.longitude
    };
    geoPerm = true;
    Drupal.clickAndCollect.storesDisplay(coords);
  };

  // Make Ajax call to get stores and render html.
  Drupal.clickAndCollect.storesDisplay = function (coords) {
    if (coords !== null) {
      var cartId = drupalSettings.alshaya_acm_checkout.cart_id;
      var checkLocation = true;

      if (lastCoords !== null) {
        checkLocation = (lastCoords.latitude !== coords.latitude || lastCoords.longitude !== coords.longitude);
      }

      if (checkLocation) {
        lastCoords = coords;

        $.ajax({
          url: Drupal.url('click-and-collect/stores/cart/' + cartId + '/' + coords.latitude + '/' + coords.longitude),
          beforeSend: function (xmlhttprequest) {
            $('#click-and-collect-list-view').html(progressElement);
          },
          success: function (response) {
            $('#click-and-collect-list-view').html(response.output);
            storeList = response.raw;
          }
        });
      }
    }
  };

})(jQuery, Drupal);
