/**
 * @file
 * Store Finder.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.alshaya_stores_finder = Drupal.alshaya_stores_finder || {};

  Drupal.behaviors.storeFinder = {
    attach: function (context, settings) {

      var storeFinderPageSelector = $('.view-id-stores_finder.view-display-id-page_1', context);
      if (storeFinderPageSelector.length > 0) {
        var loadmoreItemLimit = settings.stores_finder.load_more_item_limit;
        var storeLocatorSelector = 'div.list-view-locator';
        var loadMoreButtonSelector = '.load-more-button';

        Drupal.alshaya_stores_finder.paginateStores(storeLocatorSelector, loadMoreButtonSelector, loadmoreItemLimit);
      }

      $('.set-center-location .views-field-field-store-address').on('click', function () {
        // Get all elements having 'selected' class and then remove class.
        var active_stores = $('.list-view-locator.selected');
        if (active_stores.length > 0) {
          active_stores.removeClass('selected');
        }
        // Add class to parent for making it active.
        $(this).parents('.list-view-locator').addClass('selected');

        // Id of the row.
        var elementID = $(this).parents('.set-center-location').attr('id');
        Drupal.geolocation.loadGoogle(function () {
          var geolocationMap = {};

          if (typeof Drupal.geolocation.maps !== 'undefined') {
            $.each(Drupal.geolocation.maps, function (index, map) {
              if (typeof map.container !== 'undefined') {
                geolocationMap = map;
              }
            });
          }

          if (typeof geolocationMap.googleMap !== 'undefined') {
            var newCenter = new google.maps.LatLng(
              $('#' + elementID + ' .lat-lng .lat').html(),
              $('#' + elementID + ' .lat-lng .lng').html()
            );
            geolocationMap.googleMap.setCenter(newCenter);

            // Clicking the markup.
            var markers = geolocationMap.mapMarkers;
            var current_marker = {};
            for (var i = 0, len = markers.length; i < len; i++) {
              var marker = markers[i];
              var mapLat = marker.position.lat().toFixed(6);
              var mapLng = marker.position.lng().toFixed(6);
              var htmlLat = parseFloat($('#' + elementID + ' .lat-lng .lat').html()).toFixed(6);
              var htmlLng = parseFloat($('#' + elementID + ' .lat-lng .lng').html()).toFixed(6);
              // If markup has same latitude and longitude that we clicked.
              if (mapLat === htmlLat && mapLng === htmlLng) {
                current_marker = markers[i];
                break;
              }
            }

            // Trigger marker click.
            google.maps.event.trigger(current_marker, 'click');
          }

        });
      });

      $('.current-location').on('click', function () {
        // Start overlay here.
        $('body').addClass('modal-overlay--spinner');

        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(successCallback, errorCallback);
        }

        return false;
      });

      // Error callback.
      var errorCallback = function (error) {
        // Close the overlay.
        $('body').removeClass('modal-overlay--spinner');
      };

      // Success callback.
      var successCallback = function (position) {
        var x = position.coords.latitude;
        var y = position.coords.longitude;
        displayLocation(x, y);
      };

      function displayLocation(latitude, longitude) {
        //var geocoder = new google.maps.Geocoder();
        if (typeof Drupal.geolocation.geocoder.googleGeocodingAPI.geocoder === 'undefined') {
          Drupal.geolocation.geocoder.googleGeocodingAPI.geocoder = new google.maps.Geocoder();
        }
        var geocoder = Drupal.geolocation.geocoder.googleGeocodingAPI.geocoder;
        var latlng = {lat: parseFloat(latitude), lng: parseFloat(longitude)};
        geocoder.geocode({location: latlng}, function (results, status) {
          if (status === 'OK') {
            if ($('.current-view').length) {
              $('.current-view #edit-geolocation-geocoder-google-geocoding-api').val(results[1].formatted_address);
              $('.current-view input[name="field_latitude_longitude_proximity-lat"]').val(latitude);
              $('.current-view input[name="field_latitude_longitude_proximity-lng"]').val(longitude);
            }
            else {
              $('.block-views-exposed-filter-blockstores-finder-page-1 #edit-geolocation-geocoder-google-geocoding-api').val(results[1].formatted_address);
              $('.block-views-exposed-filter-blockstores-finder-page-1 input[name="field_latitude_longitude_proximity-lat"]').val(latitude);
              $('.block-views-exposed-filter-blockstores-finder-page-1 input[name="field_latitude_longitude_proximity-lng"]').val(longitude);
            }
          }

          if ($('.current-view').length !== 0) {
            setTimeout(function() {
              $('.current-view form #edit-submit-stores-finder').trigger('click');
              // Close the overlay.
              $('body').removeClass('modal-overlay--spinner');
            }, 500);
          }
          else {
            setTimeout(function() {
              $('.block-views-exposed-filter-blockstores-finder-page-1 form #edit-submit-stores-finder').trigger('click');
              // Close the overlay.
              $('body').removeClass('modal-overlay--spinner');
            }, 500);
          }
        });
      }

      // Remove the store node title from breadcrumb.
      $.fn.updateStoreFinderBreadcrumb = function(data) {
        var breadcrumb = $('.block-system-breadcrumb-block').length;
        if (breadcrumb > 0) {
          var li_count = $('.block-system-breadcrumb-block ol li').length;
          if (li_count > 2) {
            $('.block-system-breadcrumb-block ol li:last').remove();
          }
        }
      };

      // Trigger click on autocomplete selection.
      $('.block-views-exposed-filter-blockstores-finder-page-1 .ui-autocomplete-input').on('autocompleteselect', function( event, ui ) {
          setTimeout(function() {
            $('.block-views-exposed-filter-blockstores-finder-page-1 form #edit-submit-stores-finder').trigger('click');
          }, 500);
      });

      // Trigger click on autocomplete selection.
      $('.block-views-exposed-filter-blockstores-finder-page-3 .ui-autocomplete-input').on('autocompleteselect', function( event, ui ) {
        setTimeout(function() {
          $('.block-views-exposed-filter-blockstores-finder-page-3 form #edit-submit-stores-finder').trigger('click');
        }, 500);
      });

    }
  };

  /**
   * Helper function to add client-side pagination.
   */
  Drupal.alshaya_stores_finder.paginateStores = function(storeLocatorSelector, loadMoreButtonSelector, loadmoreItemLimit) {
    var viewLocatorCount = $(storeLocatorSelector).length;

      if (viewLocatorCount > loadmoreItemLimit) {
      $(storeLocatorSelector).slice(loadmoreItemLimit, viewLocatorCount).hide();

      $(loadMoreButtonSelector).on('click', function (e) {
        e.preventDefault();
        var hiddenStoreSelector = $(storeLocatorSelector + ':hidden');
        hiddenStoreSelector.slice(0, loadmoreItemLimit).slideDown('slow', function () {
          if ($(storeLocatorSelector + ':hidden').length === 0) {
            $(loadMoreButtonSelector).fadeOut('slow');
          }
        });
      });
    }
    else {
      $(loadMoreButtonSelector).hide();
    }
  };

})(jQuery, Drupal);
