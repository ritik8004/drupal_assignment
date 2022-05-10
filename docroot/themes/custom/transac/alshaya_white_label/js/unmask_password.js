/**
 * @file
 * Unmask Password.
 */

(function ($, Drupal) {

  Drupal.behaviors.unmaskPassword = {
    attach: function (context, settings) {
      var showPassword = Drupal.t('Show');
      var hidePassword = Drupal.t('Hide');

      $('.form-type-password').each(function () {
        if (!$(this).find('#unmask_password').length) {
          $(this).append('<div id="unmask_password" class="unmask-password">' + showPassword + '</div>');
        }
      });

      $('.unmask-password', context).once('unmaskPassword').on('click', function () {
        var $input = $(this).parent().find('.form-text:first');
        if ($input.attr('type') === 'password') {
          $input.attr('type', 'text').addClass('password-visible-input');
          $(this).html(hidePassword);
        }
        else {
          $input.attr('type', 'password').removeClass('password-visible-input');
          $(this).html(showPassword);
        }
      });

      $('#edit-submit, .form-submit').on('click', function () {
        $(this).parents('form:first').find('.password-visible-input').each(function () {
          $(this).parent().find('.unmask-password').trigger('click');
        });
      });
    }
  };

})(jQuery, Drupal);
