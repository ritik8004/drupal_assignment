/**
 * @file
 * Store Finder.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.storeFinder = {
    attach: function (context, settings) {

      $('.set-center-location').on('click', function () {
        // Id of the row.
        var elementID = $(this).attr('id');
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
        $('.alias--store-finder').addClass('modal-overlay');

        if (navigator.geolocation) {

          navigator.geolocation.getCurrentPosition(successCallback, errorCallback);
        }

        return false;
      });

      // Error callback.
      var errorCallback = function (error) {
        $('.alias--store-finder').removeClass('modal-overlay');
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
              $('.current-view .ui-autocomplete-input').val(results[1].formatted_address);
            }
            else {
              $('.block-views-exposed-filter-blockstores-finder-page-1 .ui-autocomplete-input').val(results[1].formatted_address);
            }
          }

          // Set proximity boundries.
          var south_west_lat = results[1].geometry.bounds.getSouthWest().lat();
          var south_west_lng = results[1].geometry.bounds.getSouthWest().lng();
          var north_east_lat = results[1].geometry.bounds.getNorthEast().lat();
          var north_east_lng = results[1].geometry.bounds.getNorthEast().lng();

          if ($('.current-view').length !== 0) {
            $('.current-view input[name="field_latitude_longitude_boundary[lat_north_east]"]').val(north_east_lat);
            $('.current-view input[name="field_latitude_longitude_boundary[lng_north_east]"]').val(north_east_lng);
            $('.current-view input[name="field_latitude_longitude_boundary[lat_south_west]"]').val(south_west_lat);
            $('.current-view input[name="field_latitude_longitude_boundary[lng_south_west]"]').val(south_west_lng);
            $('.current-view form #edit-submit-stores-finder').trigger('click');
          }
          else {
            $('.block-views-exposed-filter-blockstores-finder-page-1 input[name="field_latitude_longitude_boundary[lat_north_east]"]').val(north_east_lat);
            $('.block-views-exposed-filter-blockstores-finder-page-1 input[name="field_latitude_longitude_boundary[lng_north_east]"]').val(north_east_lng);
            $('.block-views-exposed-filter-blockstores-finder-page-1 input[name="field_latitude_longitude_boundary[lat_south_west]"]').val(south_west_lat);
            $('.block-views-exposed-filter-blockstores-finder-page-1 input[name="field_latitude_longitude_boundary[lng_south_west]"]').val(south_west_lng);
            $('.block-views-exposed-filter-blockstores-finder-page-1 form #edit-submit-stores-finder').trigger('click');
          }
        });
        $('.alias--store-finder').removeClass('modal-overlay');
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
          $('.block-views-exposed-filter-blockstores-finder-page-1 form #edit-submit-stores-finder').trigger('click');
      });

      // Trigger click on autocomplete selection.
      $('.block-views-exposed-filter-blockstores-finder-page-3 .ui-autocomplete-input').on('autocompleteselect', function( event, ui ) {
        $('.block-views-exposed-filter-blockstores-finder-page-3 form #edit-submit-stores-finder').trigger('click');
      });

    }
  };

})(jQuery, Drupal);
