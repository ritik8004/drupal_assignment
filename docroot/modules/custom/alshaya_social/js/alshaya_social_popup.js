/**
 * @file
 * Alshaya Social auth popup.
 */

(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.alshayaSocial = {
    attach: function (context, settings) {
      // create new Social auth popup window and monitor it
      $('.auth-link').once('auth-link').on('click', function (event) {
        event.preventDefault();

        var authLink = $(this).attr('social-auth-link');
        var destination = urlParam('destination', authLink) || Drupal.url('user');

        // Log the social login initiation.
        Drupal.alshayaLogger('notice', 'User started social authentication on @authLink with destination: @destination.', {
          '@authLink': authLink,
          '@destination': destination,
        });

        Drupal.socialAuthPopup({
          path: authLink,
          destination: destination,
          callback: function (authLink, destination) {
            // Log the social login completion.
            Drupal.alshayaLogger('notice', 'User logged in via social authentication on @authLink.', {
              '@authLink': authLink,
            });

            window.location.href = destination;
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

    var that = this;
    that._socialAuthWindow = window.open(options.path, options.windowName, options.windowOptions);
    that._socialAuthInterval = window.setInterval(function () {
      try {
        // If popup closed by user, reload the page.
        if(that._socialAuthWindow.closed) {
          // Log the social login failure.
          Drupal.alshayaLogger('warning', 'User login via social authentication on @authLink failed.', {
            '@authLink': options.path,
          });

          window.clearInterval(that._socialAuthInterval);
        }

        if (typeof that._socialAuthWindow.drupalSettings.user.uid !== 'undefined' && that._socialAuthWindow.drupalSettings.user.uid !== 0) {
          window.clearInterval(that._socialAuthInterval);
          that._socialAuthWindow.close();
          options.callback(options.path, options.destination);
        }
      }
      catch (e) {
        // do nothing.
      }
    }, 1000);
  };

  /**
   * Helper function to get param value from URL query string.
   *
   * @param name
   * @returns {string|number|null}
   */
  function urlParam (name, url = null) {
    url = url || window.location.href;
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(url);
    if (results == null){
       return null;
    }
    else {
       return decodeURI(results[1]) || 0;
    }
  }
})(jQuery, Drupal, drupalSettings);
