(function ($, Drupal) {
  Drupal.behaviors.restrictAgentLogin = {
    attach: function (context, settings) {
      if (Drupal.smartAgent !== 'undefined') {
        var agentInfo = Drupal.smartAgent.getInfo();

        if (agentInfo !== false) {
          // Logout agent if location sharing is not enabled.
          if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
              function () {},
              function () {
                // Set cookie to identify that agent location sharing is disabled.
                $.cookie('smart_agent_location_shared', 0, {path: '/', secure: true});
                // Logout agent and redirect to login.
                Drupal.smartAgent.logout('user/login');
              }
            );
          }
        }

        // If agent location is not shared, then show message and clear smart agent cookie.
        // We use `smart_agent_location_shared` cookie to check location is shared or not.
        var locationShared = $.cookie('smart_agent_location_shared') || 1;

        if (parseInt(locationShared) == false) {
          // Show a message to smart agent to share location and then log in.
          $('#block-page-title').prepend('<div class="location-not-shared-message">' + Drupal.t('Smart agent, we have not been able to confirm your location. Please enable location services and allow location tracking then try log in again') + '</div>');

          // Clear smart agent location cookie.
          $.removeCookie('smart_agent_location_shared', {path: '/'});
        }
      }
    }
  };

})(jQuery, Drupal);
