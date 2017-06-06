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

        var show_slider = false;
        if (window.matchMedia('(max-width: 767px)').matches) {
          // Mobile.
          show_slider = true;
        }
        else if (window.matchMedia('(max-width: 1024px)').matches) {
          // Tablet.
          show_slider = true;
        }
        else {
          // Desktop.
          show_slider = false;
        }

        // Show the slider only for the tablet and mobile not for desktop.
        if (show_slider) {
          // Show the slider.
          $('.only-first-time').show();
        }

        // Hide the language slider after 5 seconds.
        setTimeout(function () {
          $('.only-first-time').hide();
        }, 5000);

      }
      // Close the block when clicked on close button.
      $('.language-switcher-close').on('click', function () {
        // Hide the slider.
        $('.only-first-time').hide();
      });
    }
  };

})(jQuery, Drupal);
