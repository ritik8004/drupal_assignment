/**
 * @file
 * Store Finder.
 */

(function ($, Drupal) {
  'use strict';

  /* global google */

  /**
   * @namespace
   */
  Drupal.geolocation = Drupal.geolocation || {};
  Drupal.alshaya_stores_finder = Drupal.alshaya_stores_finder || {};

  Drupal.behaviors.storeFinder = {
    attach: function (context, settings) {

      // Opening hours toggle.
      $('[data-drupal-selector^="views-form-stores-finder-"], .individual--store', context).once('opening-hrs-init').on('click', '.hours--label', function () {
        $(this).toggleClass('open');
      });

      var storeFinderPageSelector = $('.view-id-stores_finder.view-display-id-page_1', context);
      if (storeFinderPageSelector.length > 0) {
        var loadmoreItemLimit = settings.stores_finder.load_more_item_limit;
        var storeLocatorSelector = 'div.list-view-locator';
        var loadMoreButtonSelector = '.load-more-button';

        Drupal.alshaya_stores_finder.paginateStores(storeLocatorSelector, loadMoreButtonSelector, loadmoreItemLimit);
      }

      $('.view-stores-finder .list-view-locator', context).once('marker-trigger').on('click', function () {
        // Add class for making it active and remove 'selected' class from all siblings.
        $(this)
          .addClass('selected')
          .siblings('.list-view-locator').removeClass('selected');

        // Id of the row.
        var elementID = $(this).find('.set-center-location').attr('id');
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
            var currentLat = parseFloat($('#' + elementID + ' .lat-lng .lat').html()).toFixed(6);
            var currentLng = parseFloat($('#' + elementID + ' .lat-lng .lng').html()).toFixed(6);

            var newCenter = new google.maps.LatLng(currentLat, currentLng);
            geolocationMap.googleMap.setCenter(newCenter);

            // Clicking the markup.
            var markers = geolocationMap.mapMarkers;
            var current_marker = {};
            for (var i = 0, len = markers.length; i < len; i++) {
              // If markup has same latitude and longitude that we clicked.
              if (markers[i].position.lat().toFixed(6) === currentLat && markers[i].position.lng().toFixed(6) === currentLng) {
                current_marker = markers[i];
                break;
              }
            }

            // Trigger marker click.
            google.maps.event.trigger(current_marker, 'click');
          }
        });
      });

      $('.current-location').once('location-init').on('click', function () {
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
        if (typeof Drupal.geolocation.geocoder.googleGeocodingAPI.geocoder === 'undefined') {
          Drupal.geolocation.geocoder.googleGeocodingAPI.geocoder = new google.maps.Geocoder();
        }

        var componentRestrictions = {};
        if (typeof drupalSettings.geolocation.geocoder.googlePlacesAPI.restrictions !== 'undefined') {
          componentRestrictions = drupalSettings.geolocation.geocoder.googlePlacesAPI.restrictions;
        }

        var geocoder = Drupal.geolocation.geocoder.googleGeocodingAPI.geocoder;
        var latlng = {lat: parseFloat(latitude), lng: parseFloat(longitude)};
        var runscript = true;
        geocoder.geocode({location: latlng}, function (results, status) {
          if (status === 'OK') {

            // Check if the current location country and the restricted country are same.
            if (!$.isEmptyObject(componentRestrictions) && componentRestrictions.country) {
              $.each(results, function (index, result) {
                var addressType = result.types[0];
                if (addressType === 'country') {
                  if (result.address_components[0].short_name !== componentRestrictions.country) {
                    runscript = false;
                    return false;
                  }
                }
              });
            }

            if (runscript) {
              if ($('.current-view').length) {
                $('.current-view #edit-geolocation-geocoder-google-places-api').val(results[1].formatted_address);
                $('.current-view input[name="field_latitude_longitude_proximity-lat"]').val(latitude);
                $('.current-view input[name="field_latitude_longitude_proximity-lng"]').val(longitude);
              }
              else {
                $('.block-views-exposed-filter-blockstores-finder-page-1 #edit-geolocation-geocoder-google-places-api').val(results[1].formatted_address);
                $('.block-views-exposed-filter-blockstores-finder-page-1 input[name="field_latitude_longitude_proximity-lat"]').val(latitude);
                $('.block-views-exposed-filter-blockstores-finder-page-1 input[name="field_latitude_longitude_proximity-lng"]').val(longitude);
              }
            }
          }

          if ($('.current-view').length !== 0) {
            setTimeout(function () {
              if (runscript) {
                $('.current-view form #edit-submit-stores-finder').trigger('click');
              }
              // Close the overlay.
              $('body').removeClass('modal-overlay--spinner');
            }, 500);
          }
          else {
            setTimeout(function () {
              if (runscript) {
                $('.block-views-exposed-filter-blockstores-finder-page-1 form #edit-submit-stores-finder').trigger('click');
              }
              // Close the overlay.
              $('body').removeClass('modal-overlay--spinner');
            }, 500);
          }
        });
      }

      // Remove the store node title from breadcrumb.
      $.fn.updateStoreFinderBreadcrumb = function (data) {
        var breadcrumb = $('.block-system-breadcrumb-block').length;
        if (breadcrumb > 0) {
          var li_count = $('.block-system-breadcrumb-block ol li').length;
          if (li_count > 2) {
            $('.block-system-breadcrumb-block ol li:last').remove();
          }
        }
      };

      // Trigger click on autocomplete selection.
      $('[class*="block-views-exposed-filter-blockstores-finder-page"]').each(function () {
        var storeFinder = $(this);
        // Add class to store finder exposed form.
        // Adding class to hook_form_alter for store finder form is adding it to the
        // wrapper div. So, adding it using js to apply css.
        storeFinder.find('form').addClass('store-finder-exposed-form');
        // Trigger form submit on selecting location in autocomplete.
        storeFinder.find('.ui-autocomplete-input').on('autocompleteselect', function (event, ui) {
          setTimeout(function () {
            storeFinder.find('input[id^="edit-submit-stores-finder"]').trigger('click');
          }, 500);
        });
      });

    }
  };

  /**
   * Helper function to add client-side pagination.
   *
   * @param {HTMLElement} storeLocatorSelector
   *   The store locator selector html string.
   * @param {HTMLElement} loadMoreButtonSelector
   *   The Load more button html string.
   * @param {int} loadmoreItemLimit
   *   Limit to load new items.
   */
  Drupal.alshaya_stores_finder.paginateStores = function (storeLocatorSelector, loadMoreButtonSelector, loadmoreItemLimit) {
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

// Open Maps app depending on the device ios or Andriod.
function mapsApp(lat, lng) {
  // If it is an iPhone..
  if ((navigator.platform.indexOf('iPhone') !== -1)
    || (navigator.platform.indexOf('iPod') !== -1)
    || (navigator.platform.indexOf('iPad') !== -1)) {window.open('maps://maps.google.com/maps?daddr=' + lat + ',' + lng + '&amp;ll=');}
  else {window.open('geo:' + lat + ',' + lng + '');}
}
