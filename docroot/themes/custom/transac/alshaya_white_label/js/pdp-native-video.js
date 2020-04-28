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
   * The video element.
   */
  function playPause(element) {
    if (element.get(0).paused) {
      element.get(0).play();
    }
    else {
      element.get(0).pause();
    }
  }

  /**
   * Make video play in fullscreen modal.
   *
   * @param {*} element
   * The video element.
   */
  function playFullScreen(element) {
    var parent = element.parent();
    parent.addClass('go-fullscreen');
  }

  Drupal.behaviors.pdpVideoPlayer = {
    attach: function (context) {
      $('.pdp-video-close').once('video-fullscreen-control').on('click', function () {
        playPause($(this).next());
        $(this).parent().removeClass('go-fullscreen');
      });

      // Video player controls to play pause.
      $('video.gallery-video').once('video-load').on('click', function () {
        var videoElement = $(this);
        playPause(videoElement);
        if ($(window).width() < 768) {
          playFullScreen(videoElement);
        }
      });

      // When video ends or is paused.
      $('video.gallery-video').once('video-player-ended').on('ended pause', function () {
        $(this).removeClass('playing');
        $(this).removeClass('hide-controls');
      });

      // When video is playng.
      $('video.gallery-video').once('video-player-playing').on('playing', function () {
        $(this).addClass('playing');
        $(this).addClass('hide-controls');
      });
    }
  };

})(jQuery, Drupal);
