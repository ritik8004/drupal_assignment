/**
 * @file
 * PDP native video player controls.
 */

(function ($, Drupal) {
  'use strict';

  /**
   * Play/Pause videos.
   *
   * @param {*} element
   */
  function playPause(element) {
    if (element.get(0).paused) {
      element.get(0).play();
    }
    else {
      element.get(0).pause();
    }
  }

  Drupal.behaviors.pdpVideoPlayer = {
    attach: function (context) {
      $('video.gallery-video').once('video-load').on('click', function () {
        var videoElement = $(this);
        playPause(videoElement);
      });

      $('video.gallery-video').once('video-player-ended').on('ended pause', function () {
        $(this).removeClass('playing');
        $(this).removeClass('hide-controls');
      });

      $('video.gallery-video').once('video-player-playing').on('playing', function () {
        $(this).addClass('playing');
        $(this).addClass('hide-controls');
      });
    }
  };

})(jQuery, Drupal);
