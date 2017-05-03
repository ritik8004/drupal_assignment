/**
 * @file
 * Store Finder.
 */

(function ($, Drupal) {
    'use strict';

    Drupal.behaviors.store_finder = {
        attach: function (context, settings) {

            $('.set-center-location').click(function(){
                // Id of the row.
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

                    }


                });
            });
        }
    };

})(jQuery, Drupal);
