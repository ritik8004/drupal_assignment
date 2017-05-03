/**
 * @file
 * Store Finder.
 */

(function ($, Drupal) {
    'use strict';

    Drupal.behaviors.store_finder = {
        attach: function (context, settings) {

            $('.set-center-location').click(function(){
                // Id of the node.
                var element_id = $(this).attr('id');
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
                            $('#' + element_id + ' .lat-lng .lat').html(),
                            $('#' + element_id + ' .lat-lng .lng').html()
                        );
                        geolocationMap.googleMap.setCenter(newCenter);

                        // var markup = {};
                        // for (var i = geolocationMap.mapMarkers.length - 1; i >= 0; --i) {
                        //     console.log(geolocationMap.mapMarkers[i]);
                        //     console.log(geolocationMap.mapMarkers[i].position.lat());
                        //     console.log(geolocationMap.mapMarkers[i].position.lng());
                        //     if (geolocationMap.mapMarkers[i].position.lat() == $('#' + element_id + ' .lat-lng .lat').html() && geolocationMap.mapMarkers[i].position.lng() == $('#' + element_id + ' .lat-lng .lng').html()) {
                        //         markup = geolocationMap.mapMarkers[i];
                        //     }
                        // }
                        // new google.maps.event.trigger( markup, 'click' );

                    }


                });
            });
        }
    };

})(jQuery, Drupal);
