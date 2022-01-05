(function ($, Drupal, drupalSettings) {
  // Track user activity around login/reset/register forms.
  var userLogger = JSON.parse(localStorage.getItem('user_action_logger'));
  if (userLogger && (drupalSettings.user.uid > 0 || userLogger.type === 'change-pwd-form')) {
    Drupal.alshayaLogger(userLogger.level, userLogger.message, userLogger.context);
    // Remove the local storage after logging the message.
    localStorage.removeItem('user_action_logger');
  }

  Drupal.behaviors.alshayaUserAction = {
    attach: function (context) {
      // User action via Form.
      $("#user-login-form, #user-register-form, #user-pass, #change-pwd-form", context).on('submit', function () {
        localStorage.setItem('user_action_logger', JSON.stringify({
          'level': 'debug',
          'type': $(this).attr('id'),
          'message': 'Guest user attempted to submit in form @formId.',
          'context': {
            '@formId': $(this).attr('id'),
          }
        }));
      });
      // User logout link action.
      $(".signout-link", context).on('click', function () {
        Drupal.alshayaLogger('debug', 'User performed logout action');
      });
      // Social login action.
      $('.auth-link').on('click', function () {
        localStorage.setItem('user_action_logger', JSON.stringify({
          'level': 'debug',
          'type': 'social_authentication',
          'message': 'User performed social authentication for Login.',
          'context': '',
        }));
      });
    }
  }

})(jQuery, Drupal, drupalSettings);
