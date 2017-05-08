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
        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(successCallback, errorCallback);
        }
        else {
          alert('Your browser doesn\'t geolocation.');
        }
        return false;
      });

      // Error callback.
      var errorCallback = function (error) {
        // Close overlay here.
      };

      // Success callback.
      var successCallback = function (position) {
        var x = position.coords.latitude;
        var y = position.coords.longitude;
        displayLocation(x, y);
      };

      function displayLocation(latitude, longitude) {
        var geocoder = new google.maps.Geocoder();
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
        });
        // Close overlay here.
      }

    }
  };

})(jQuery, Drupal);
