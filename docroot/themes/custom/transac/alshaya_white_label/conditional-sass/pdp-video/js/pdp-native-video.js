/**
 * @file
 * PDP native video player controls.
 */

(function ($, Drupal) {

  /**
   * Play/Pause videos.
   *
   * @param {*} element
   * The video element.
   *
   * @param {*} stopVideo
   * Pass `true` here to pause video when fullscreen closes.
   */
  function playPause(element, stopVideo) {
    if (stopVideo === true) {
      element.get(0).pause();
      return;
    }
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
        playPause($(this).next(), true);
        $(this).parent().removeClass('go-fullscreen');
      });

      // Video player controls to play pause.
      $('video.gallery-video').once('video-load').on('click', function () {
        var videoElement = $(this);
        if ($(window).width() < 768) {
          playFullScreen(videoElement);
          playPause(videoElement, false);
        }
        else {
          playPause(videoElement, false);
        }
      });

      // When video ends or is paused.
      $('video.gallery-video').once('video-player-ended').on('ended pause', function () {
        $(this).removeClass('playing');
        $(this).removeClass('hide-controls');
      });

      // When video is playing.
      $('video.gallery-video').once('video-player-playing').on('playing', function () {
        $(this).addClass('playing');
        $(this).addClass('hide-controls');
      });

      $('video.gallery-video').once('video-player-controls').on('mouseenter', function () {
        if ($(this).hasClass('playing')) {
          $(this).addClass('button-preview');
          // Wait for some time and then remove the controls again.
          setTimeout(function (video) {
            video.removeClass('button-preview');
          }, 700, $(this));
        }
      });
    }
  };

})(jQuery, Drupal);
