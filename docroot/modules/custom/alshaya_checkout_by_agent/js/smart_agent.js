(function ($, Drupal) {
  Drupal.behaviors.smartAgent = {
    attach: function (context, settings) {
      // Add agent login message as soon as smartAgent cookie is set.
      var smartAgentCookie = $.cookie('smart_agent_cookie');

      if (smartAgentCookie !== undefined) {
        var cookieArray = JSON.parse(atob(smartAgentCookie));

        // @todo: Move style to css file.
        var loggedInMessageMarkup = '<div class="smart-agent-header-wrapper" style="position: absolute;top: 9px;right: 10%;z-index: 99;font-size: 0.8125rem;line-height: 1.2;vertical-align: middle;letter-spacing: 0.8px;text-transform: capitalize;font-weight: bold;">';
        loggedInMessageMarkup += '<span class="agent-logged-in">';
        loggedInMessageMarkup += Drupal.t('Logged in as Agent') + ' : ' + cookieArray['name'] + '</span>';
        loggedInMessageMarkup += '<span class="agent-logout" style="padding-left: 20px;">' + Drupal.t('Logout') + '</span>';
        loggedInMessageMarkup += '</div>';
        $('body').append(loggedInMessageMarkup);
      }

      // On click on smart agent logout, remove cookie and logged-in message.
      $('.smart-agent-header-wrapper .agent-logout').once('smart-agent-logout').on('click', function () {
        $.removeCookie('smart_agent_cookie', {path: '/'});
        $('.smart-agent-header-wrapper').remove();
      });
    }
  };
})(jQuery, Drupal);
