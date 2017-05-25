/**
 * @file
 * One time language selection slider.
 */

(function ($, Drupal) {
    'use strict';

    Drupal.behaviors.oneTimeLanguageSlider = {
        attach: function (context, settings) {
            var already_visited = $.cookie('Drupal.visitor.already_visited');
            // If visiting first time.
            if (typeof already_visited === 'undefined') {
                // Set expiry for 30 days.
                $.cookie('Drupal.visitor.already_visited', '1', {path: '/', expires: 30, secure: true});
                // Show the slider.
                $('.only-first-time').show();
            }
        }
    };

})(jQuery, Drupal);
