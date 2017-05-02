/**
 * @file
 * Unmask Password.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.unmaskPassword = {
    attach: function (context, settings) {
      var show = Drupal.t('Show');
      var showPassword = Drupal.t('Show password');
      var hidePassword = Drupal.t('Hide password');
      if (!$('.form-type-password-confirm').find('#unmask_password').length) {
        $('.form-type-password').append('<div id="unmask_password">' + show + '</div>');
      }
      $('#unmask_password').on('click', function () {
        if ($('#edit-pass').attr('type') === 'password') {
          $('#edit-pass').attr('type', 'text');
          $('#unmask_password').html(hidePassword);
        }
        else if ($('#edit-pass').attr('type') === 'text') {
          $('#edit-pass').attr('type', 'password');
          $('#unmask_password').html(showPassword);
        }
      });

      $('.change-pwd-form #unmask_password').on('click', function () {
        var current = $(this).siblings('.form-text');

        if ($(current).attr('type') === 'password') {
          $(current).attr('type', 'text');
          $(this).html(hidePassword);
        }
        else if ($(current).attr('type') === 'text') {
          $(current).attr('type', 'password');
          $(this).html(showPassword);
        }
      });

      $('.user-register-form #unmask_password').on('click', function () {
        var current = $(this).siblings('.form-text');

        if ($(current).attr('type') === 'password') {
          $(current).attr('type', 'text');
          $(this).html(hidePassword);
        }
        else if ($(current).attr('type') === 'text') {
          $(current).attr('type', 'password');
          $(this).html(showPassword);
        }
      });

      $('#edit-submit').on('click', function () {
        if ($('#edit-pass').attr('type') === 'text') {
          $('#edit-pass').attr('type', 'password');
        }
        if ($('#edit-pass-pass1').attr('type') === 'text') {
          $('#edit-pass-pass1').attr('type', 'password');
        }
        if ($('#edit-pass-pass2').attr('type') === 'text') {
          $('#edit-pass-pass2').attr('type', 'password');
        }
      });
    }
  };

})(jQuery, Drupal);
