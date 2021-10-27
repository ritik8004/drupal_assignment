(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.alshayaUserAction = {
    attach: function (context, settings) {
      // Track user activity around login/reset/register forms.
      let userLogger = JSON.parse(localStorage.getItem('user_action_logger'));
      if (userLogger && (settings.user.uid > 0 || userLogger.type === 'password_change')) {
        Drupal.alshayaLogger(userLogger.level, userLogger.message);
        // Remove the local storage after logging the message.
        localStorage.removeItem('user_action_logger');
      }

      // User login via Form.
      $("#user-login-form", context).on('submit', function (e) {
        localStorage.setItem('user_action_logger', JSON.stringify({
          'level': 'debug',
          'type': 'user_login',
          'message': 'User performed login operation via Login form.',
        }));
      });
      // User register via Form.
      $("#user-register-form", context).on('submit', function (e) {
        localStorage.setItem('user_action_logger', JSON.stringify({
          'level': 'debug',
          'type': 'user_register',
          'message': 'User performed registeration and email verification operation via Register form.',
        }));
      });
      // User logout link action.
      $(".signout-link", context).on('click', function (e) {
        Drupal.alshayaLogger('debug', 'User performed logout action');
      });
      // Social login action.
      $('.auth-link').on('click', function (e) {
        localStorage.setItem('user_action_logger', JSON.stringify({
          'level': 'debug',
          'type': 'social_authentication',
          'message': 'User performed social authentication for Login.',
        }));
      });
      // User password reset.
      $("#user-pass", context).on('submit', function () {
        localStorage.setItem('user_action_logger', JSON.stringify({
          'level': 'debug',
          'type': 'password_reset',
          'message': 'User performed password reset.',
        }));
      });
      // Password change.
      $("#change-pwd-form").on('submit', function () {
        localStorage.setItem('user_action_logger', JSON.stringify({
          'level': 'debug',
          'type': 'password_change',
          'message': 'User performed password change action.',
        }));
      });
    }
  }

})(jQuery, Drupal);
