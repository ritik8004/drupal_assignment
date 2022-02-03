/**
 * @file
 * One time language selection slider.
 */

(function ($, Drupal) {

  Drupal.behaviors.oneTimeLanguageSlider = {
    attach: function (context, settings) {
      var already_visited = $.cookie('Drupal.visitor.already_visited');
      // If visiting first time.
      if (typeof already_visited === 'undefined') {
        // Set expiry for 30 days.
        $.cookie('Drupal.visitor.already_visited', '1', {path: '/', expires: 30, secure: true});

        var languge_switcher = $('.only-first-time');
        var languge_switcher_close = $('.language-switcher-close');

        var mobile = window.matchMedia('(max-width: 767px)').matches;
        var tablet = window.matchMedia('(max-width: 1024px)').matches;
        if (mobile || tablet) {
          languge_switcher.show();
        }
        else {
          // Desktop.
          languge_switcher.hide();
        }

        // Gradually Hide the language slider once user starts to scroll.
        $(window, context).on('scroll', function () {
          languge_switcher.fadeOut(2000);
        });

        // Close the block when clicked on close button.
        languge_switcher_close.on('click', function () {
          // Hide the slider.
          languge_switcher.hide();
        });
      }
    }
  };

})(jQuery, Drupal);
