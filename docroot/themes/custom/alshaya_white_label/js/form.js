/**
 * @file
 * Forms.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.formsInput = {
    attach: function (context, settings) {
      $('.profile-form input, .c-user-edit .user-form input').each(function () {
        if ($.trim($(this).val()).length !== 0) {
          $(this).parent().find('label').last().addClass('active-label');
        }
        else {
          $(this).parent().find('label').last().removeClass('active-label');
        }
      });

      $('.webform-submission-alshaya-contact-form input, .webform-submission-alshaya-contact-form textarea, .profile-form input, .c-user-edit .user-form input').focusout(function () {
        if ($(this).val() !== '') {
          $(this).parent().find('label').last().addClass('active-label');
        }
        else {
          $(this).parent().find('label').last().removeClass('active-label');
        }
      });

      $(window).on('load', function () {
        $('.webform-submission-alshaya-contact-form input, .webform-submission-alshaya-contact-form textarea, .profile-form input').each(function () {
          if ($(this).val() !== '') {
            $(this).parent().find('label').last().addClass('active-label');
          }
        });
      });
    }
  };

})(jQuery, Drupal);
