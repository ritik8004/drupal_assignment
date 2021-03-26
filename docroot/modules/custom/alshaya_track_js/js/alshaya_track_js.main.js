(function ($, Drupal) {
  'use strict';

  Drupal.logViaTrackJs = function (message) {
    try {
      if (window.TrackJS === undefined) {
        return false;
      }

      window.TrackJS.track(message);
      return true;
    }
    catch (e) {
    }

    return false;
  };

})(jQuery, Drupal);
