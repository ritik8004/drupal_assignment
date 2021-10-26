(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.alshayaUserAction = {
    attach: function (context, settings) {
      // Track user activity around login/reset/register forms.
      $("#user-login-form", context).on('submit', function (e) {
        console.log(e);
        Drupal.alshayaLogger('notice', 'User performed login operation via Login form.');
      });

      $("#user-register-form", context).on('submit', function (e) {
        Drupal.alshayaLogger('notice', 'User performed register operation via Register form.');
      });
    }
  }

})(jQuery, Drupal);
