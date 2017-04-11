/**
 * @file
 * Unmask Password.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.unmaskPassword = {
    attach: function (context, settings) {
      $('.form-type-password').append('<div id="unmask_password">Show</div>');
      $('#unmask_password').on('click', function () {
        if ($('#edit-pass').attr('type') === 'password') {
          $('#edit-pass').attr('type', 'text');
          $('#unmask_password').html('Hide password');
        }
        else if ($('#edit-pass').attr('type') === 'text') {
          $('#edit-pass').attr('type', 'password');
          $('#unmask_password').html('Show password');
        }
      });
      $('#edit-submit').on('click', function () {
        if ($('#edit-pass').attr('type') === 'text') {
          $('#edit-pass').attr('type', 'password');
        }
      });
    }
  };

})(jQuery, Drupal);
