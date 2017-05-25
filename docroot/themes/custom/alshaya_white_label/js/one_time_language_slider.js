/**
 * @file
 * One time language selection slider.
 */

(function ($, Drupal) {
    'use strict';

    Drupal.behaviors.oneTimeLanguageSlider = {
        attach: function (context, settings) {
            var already_visited = getOneTimeCookie("Drupal.visitor.already_visited");
            // If visiting first time.
            if (already_visited == "") {
                // Set expiry for 30 days.
                setOneTimeCookie("Drupal.visitor.already_visited", 1, 30);
                // Show the slider.
                $('.only-first-time').show();
            }
        }
    };

    var setOneTimeCookie = function (name, value, expiry) {
        var d = new Date();
        d.setTime(d.getTime() + (expiry*24*60*60*1000));
        var expires = "expires="+ d.toUTCString();
        document.cookie = name + "=" + value + ";" + expires + ";path=/";
    };

    var getOneTimeCookie = function (name) {
        var name = name + "=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');
        for(var i = 0; i <ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    };

})(jQuery, Drupal);
