/* eslint-disable */
/**
 * @file
 * Custom js for videos on PLP page.
 */

(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.alshayaPLPVideos = {
    attach: function (context, settings) {
      videojs('my-player').ready(function () {

        // Store the video object
        var myPlayer = this, id = myPlayer.id();

        myPlayer.play();

        // Set click functions.
        $('.video-js').click(function () {
          if (myPlayer.muted()) {
            myPlayer.muted(false);
          }
          else {
            myPlayer.muted(true);
          }
        });

      });
    }
  };
})(jQuery);
