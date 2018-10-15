/* eslint-disable */
/**
 * @file
 * Custom js for videos on PLP page.
 */

(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.alshayaPLPVideos = {
    attach: function (context, settings) {
      videojs('plp-video-player').ready(function () {

        // Store the video object
        var plpPlayer = this, id = plpPlayer.id();

        plpPlayer.play();

        // Set click functions.
        $('.video-js, .plp-mute-button').click(function () {
          if (plpPlayer.muted()) {
            plpPlayer.muted(false);
            $('.plp-mute-button').removeClass('plp-video-muted');
          }
          else {
            plpPlayer.muted(true);
            $('.plp-mute-button').addClass('plp-video-muted');
          }
        });

      });
    }
  };
})(jQuery, Drupal);
