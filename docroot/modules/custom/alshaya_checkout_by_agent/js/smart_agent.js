(function ($, Drupal, drupalSettings) {

  Drupal.smartAgent = Drupal.smartAgent || {};

  Drupal.smartAgent.getInfo = function () {
    var smartAgentCookie = $.cookie('smart_agent_cookie');

    if (smartAgentCookie !== undefined) {
      return JSON.parse(atob(smartAgentCookie));
    }

    return false;
  };

  Drupal.smartAgent.logout = function () {
    $.removeCookie('smart_agent_cookie', {path: '/'});
    // Redirect to home page.
    window.location.href = Drupal.url('');
  };

  Drupal.smartAgent.endTransaction = function () {
    // Remove middleware cookies.
    // Cart data in local storage will be removed by SPC code.
    $.removeCookie('PHPSESSID-legacy', {path: '/'});
    $.removeCookie('PHPSESSID', {path: '/'});

    // Redirect to home page.
    var redirectUrl = Drupal.url('');
    if (drupalSettings.user.uid > 0) {
      // If user is logged in, logout first.
      redirectUrl = Drupal.url('user/logout?destination=/');
    }

    window.location.href = redirectUrl;
  };

  Drupal.behaviors.smartAgent = {
    attach: function (context, settings) {
      // Add agent login message as soon as smartAgent cookie is set.
      var agentInfo = Drupal.smartAgent.getInfo();

      if (typeof agentInfo !== 'undefined') {
        var locationShared = $.cookie('smart_agent_location_shared') || 1;

        if (parseInt(locationShared) == true && $('.smart-agent-header-wrapper').length < 1) {
          var loggedInMessageMarkup = '<div class="smart-agent-header-wrapper">';
          loggedInMessageMarkup += '<span class="agent-logged-in">';
          loggedInMessageMarkup += Drupal.t('Smart Agent: @name', {'@name': agentInfo['name']}) + '</span>';
          loggedInMessageMarkup += '<span class="agent-logout smart-agent-logout-link">' + Drupal.t('Sign out') + '</span>';
          loggedInMessageMarkup += '</div>';

          $('nav.menu--account').addClass('smart-agent-login');
          $('nav.menu--account').after(loggedInMessageMarkup);
        }

        // On click on smart agent logout, remove cookie and logged-in message.
        $('.smart-agent-logout-link').once('smart-agent-logout').on('click', function () {
          Drupal.smartAgent.logout();
        });

        $('body').once('smart-agent-end-transaction').on('click', '.smart-agent-end-transaction', function () {
          Drupal.smartAgent.endTransaction();
        });

        // Error callback for getCurrentPosition method.
        var errorCallback = function (error) {
          // Redirect to user login.
          var redirectUrl = Drupal.url('user/login');
          if (drupalSettings.user.uid > 0) {
            // If user is logged in, logout first.
            redirectUrl = Drupal.url('user/logout?destination=user/login');
          }

          $.cookie('smart_agent_location_shared', 0, {path: '/', secure: true});
          window.location.href = redirectUrl;
        };

        // Success callback for getCurrentPosition method.
        var successCallback = function (pos) {
          agentInfo['lat'] = pos.coords.latitude;
          agentInfo['lng'] = pos.coords.longitude;
          $.cookie('smart_agent_cookie', btoa(JSON.stringify(agentInfo)), {path: '/', secure: true});
        };

        // Add or update smart agent location in cookie.
        // We do this on every page load to ensure we have the latest location data of the Agent every-time.
        if (parseInt(locationShared) == true && navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(successCallback, errorCallback);
        }

        // If agent location is not shared, then show message and clear smart agent cookie.
        // We use `smart_agent_location_shared` cookie to check location is shared or not.
        if (parseInt(locationShared) == false) {
          // Show a message to smart agent to share location and then log in.
          $('#block-page-title').prepend('<div class="location-not-shared-message">' + Drupal.t('Smart agent, we have not been able to confirm your location. Please enable location services and allow location tracking then try log in again') + '</div>');

          // Clear all smart agent cookies.
          $.removeCookie('smart_agent_cookie', {path: '/'});
          $.removeCookie('smart_agent_location_shared', {path: '/'});
        }
      }
    }
  };

})(jQuery, Drupal, drupalSettings);
