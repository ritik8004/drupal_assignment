/**
 * @file
 * Forms.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.formsInput = {
    attach: function (context, settings) {
      $('.profile-form input').each(function () {
        if ($.trim($(this).val()).length !== 0) {
          $(this).parent().find('label').last().addClass('active-label');
        }
        else {
          $(this).parent().find('label').last().removeClass('active-label');
        }
      });

      $('.contact-form input, .contact-form textarea, .profile-form input').focusout(function () {
        if ($(this).val() !== '') {
          $(this).parent().find('label').last().addClass('active-label');
        }
        else {
          $(this).parent().find('label').last().removeClass('active-label');
        }
      });
    }
  };

})(jQuery, Drupal);
