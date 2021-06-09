(function ($, Drupal) {
  Drupal.behaviors.restrictAgentLogin = {
    attach: function (context, settings) {
      var agentInfo = Drupal.smartAgent.getInfo();

      if (agentInfo !== false) {
        // Logout agent if location sharing is not enabled.
        $(window).once('location-fetch-failed').on('alshaya-agent-location-fetch-failed', function () {
          // Set cookie to identify that agent location sharing is disabled.
          $.cookie('smart_agent_location_fetch_failed', 1, {path: '/', secure: true});
          // Logout agent and redirect to login.
          Drupal.smartAgent.logout('user/login');
        });
      }
    }
  };

  // If agent location is not shared, then show message and clear smart agent cookie.
  // We use `smart_agent_location_fetch_failed` cookie to check location is shared or not.
  var locationFetchFailed = $.cookie('smart_agent_location_fetch_failed');

  if (parseInt(locationFetchFailed) === 1) {
    // Show a message to smart agent to share location and then log in.
    $('.region__hero-content').append('<div class="location-not-shared-message messages messages--error">' + Drupal.t('Smart agent, we have not been able to confirm your location. Please enable location services and allow location tracking then try to log in again.') + '</div>');

    // Clear smart agent location cookie.
    $.removeCookie('smart_agent_location_fetch_failed', {path: '/'});
  }

})(jQuery, Drupal);
