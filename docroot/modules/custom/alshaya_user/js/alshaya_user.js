/**
 * @file
 * User login.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.userLogin = {
    attach: function (context, settings) {
      $('.messages__wrapper .messages__list .messages__item').each(function() {
        var string = $(this).html();
        if (~string.indexOf('1 error has been found')) {
          $(this).html($('#user-login-form .form-type-email .form-item--error-message').html());
        }
        else {
          $(this).remove();
        }
      });
    }
  };

})(jQuery, Drupal);
