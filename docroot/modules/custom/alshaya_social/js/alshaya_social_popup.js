/**
 * @file
 * Alshaya Social auth popup.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.alshayaSocial = {
    attach: function (context, settings) {
      // create new Social auth popup window and monitor it
      $('.auth-link').click(function () {
        var authLink = $(this).attr('social-auth-link');
        Drupal.socialAuthPopup({
          path: authLink,
          callback: function () {
            window.location.reload();
          }
        });
      });
    }
  };

  /**
   * Helper function to process popup window.
   * @param {string} options popup window.
   */
  Drupal.socialAuthPopup = function (options) {
    options.windowName = options.windowName || 'ConnectWithSocialAuth';
    options.windowOptions = options.windowOptions || 'location=0,status=0,width=600,height=600,top=100,left=500';
    options.callback = options.callback || function () { window.location.reload(); };
    var that = this;
    that._socialAuthWindow = window.open(options.path, options.windowName, options.windowOptions);
    that._socialAuthInterval = window.setInterval(function () {
      try {
        if (typeof that._socialAuthWindow.drupalSettings.user.uid !== 'undefined' && that._socialAuthWindow.drupalSettings.user.uid !== 0) {
          window.clearInterval(that._socialAuthInterval);
          that._socialAuthWindow.close();
          options.callback();
        }
      }
      catch (e) {
        // do nothing.
      }
    }, 1000);
  };
})(jQuery, Drupal, drupalSettings);
