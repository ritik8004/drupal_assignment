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
        if ($('.smart-agent-header-wrapper').length < 1) {
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
      }
    }
  };

  Drupal.smartAgent.locationPushedInCookie = false;

  Drupal.smartAgent.setLocationInCookie = function () {
    if (Drupal.smartAgent.locationPushedInCookie === true) {
      return;
    }

    var agentInfo = Drupal.smartAgent.getInfo();

    if (agentInfo === false) {
      return;
    }

    // Add agent location in cookie.
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition((pos) => {
        agentInfo['lat'] = pos.coords.latitude;
        agentInfo['lng'] = pos.coords.longitude;
        $.cookie('smart_agent_cookie', btoa(JSON.stringify(agentInfo)), {path: '/', secure: true});
        Drupal.smartAgent.locationPushedInCookie = true;
      });
    }

  };
})(jQuery, Drupal, drupalSettings);
