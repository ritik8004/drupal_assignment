/**
 * @file
 * Forms.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.formsInput = {
    attach: function (context, settings) {
      $('.address-book-address input:not([type=hidden]), .profile-form input, .c-user-edit .user-form input, .user-login-form input, .order-confirmation .user-register-form input').each(function () {
        if ($.trim($(this).val()).length !== 0) {
          $(this).parent().find('label').last().addClass('active-label');
        }
        else {
          $(this).parent().find('label').last().removeClass('active-label');
        }
      });

      $('.webform-submission-alshaya-contact-form input, .webform-submission-alshaya-contact-form textarea, .profile-form input, .c-user-edit .user-form input, .address-book-address input').focusout(function () {
        if ($(this).val() !== '') {
          $(this).parent().find('label').last().addClass('active-label');
        }
        else {
          $(this).parent().find('label').last().removeClass('active-label');
        }
      });

      // Move mobile number error on contact details.
      var mobileNumber = $('.form-item-field-mobile-number-0-mobile');
      var mobileNumberError = mobileNumber.find('.form-item--error-message');
      if (mobileNumberError.length > 0) {
        mobileNumber.parent().append(mobileNumberError);
        mobileNumberError.addClass('is-visible');
      }

      $(window).on('load', function () {
        $('.webform-submission-alshaya-contact-form input, .webform-submission-alshaya-contact-form textarea, .profile-form input, .address-book-address input:not([type=hidden])').each(function () {
          if ($(this).val() !== '') {
            $(this).parent().find('label').last().addClass('active-label');
          }
        });
      });

      var inputFields = [
        '.c-content input[type=password]',
        '.c-content input[type=text]',
        '.c-content input[type=email]',
        '.c-content input[type=tel]',
        '.c-content textarea'
      ];

      // Move input-bar adjacent to the input field for material design effect to work.
      $(inputFields).each(function () {
        $(this).on('focusout focus', function () {
          if ($(this).next().hasClass('error')) {
            var bar = $(this).parent().find('.c-input__bar');
            $(this).after(bar);
          }
        });
      });

      // Handling error for mobile number fields.
      if ($('.mobile-number-field').find('.form-item-mobile-number-mobile').hasClass('form-item--error') ||
        $('.mobile-number-field').find('.form-item-field-mobile-number-0-mobile').hasClass('form-item--error')) {
        $('.mobile-number-field').addClass('form-item--error');
      }

      // Click event trigger for privilege card field on register page.
      $('.user-register-form .privilege-card-wrapper-title').bind('click touchstart', function () {
        $('.privilege-card-wrapper summary').click();
        return false;
      });

      if ($('.password-tooltip').length > 0) {
        $('#edit-pass').focus(function () {
          $(this).addClass('is-active');
        });
      }

      // On register page, hide multiple inline error messages for email field.
      $('#user-register-form .form-type-email input').once().on('keyup', function () {
        var serverErrorWrapper = '#user-register-form .form-type-email .form-item--error-message';
        if ($('#user-register-form .form-type-email label.error').is(':visible') === true) {
          $(serverErrorWrapper).empty();
        }
      });
    }
  };

})(jQuery, Drupal);
