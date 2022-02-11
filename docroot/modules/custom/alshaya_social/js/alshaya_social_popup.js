/**
 * @file
 * Alshaya Social auth popup.
 */

(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.alshayaSocial = {
    attach: function (context, settings) {
      // create new Social auth popup window and monitor it
      $('.auth-link').click(function () {
        var authLink = $(this).attr('social-auth-link');
        var destination = $.urlParam('destination');
        Drupal.socialAuthPopup({
          path: authLink,
          callback: function () {
            if (destination) {
              window.location.href = destination;
            } else {
              // Log the social login.
              Drupal.alshayaLogger('warning', 'User performed social authentication on @authLink and failed.', {
                '@authLink': authLink,
              });
              window.location.reload();
            }
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

  $.urlParam = function (name) {
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (results == null){
       return null;
    }
    else {
       return decodeURI(results[1]) || 0;
    }
  }
})(jQuery, Drupal, drupalSettings);
