/**
 * @file
 * Forms.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.formsInput = {
    attach: function (context, settings) {
      $('.webform-submission-alshaya-contact-form input, .webform-submission-alshaya-contact-form textarea, .profile-form input').focusout(function () {
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
