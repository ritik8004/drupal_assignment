/**
 * @file
 * Forms.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.formsInput = {
    attach: function (context, settings) {
      $('.address-book-address input:not([type=hidden]), .profile-form input, .c-user-edit .user-form input, .user-login-form input, .order-confirmation .user-register-form input, #edit-contact-information input').each(function () {
        if ($.trim($(this).val()).length !== 0) {
          $(this).parent().find('label').last().addClass('active-label');
        }
        else {
          $(this).parent().find('label').last().removeClass('active-label');
        }
      });

      $('.webform-submission-alshaya-contact-form input, .webform-submission-alshaya-contact-form textarea, .profile-form input, .c-user-edit .user-form input, .address-book-address input, #edit-contact-information input').focusout(function () {
        if ($(this).val() !== '') {
          $(this).parent().find('label').last().addClass('active-label');
        }
        else {
          $(this).parent().find('label').last().removeClass('active-label');
        }
      });

      $(window).on('load', function () {
        $('.webform-submission-alshaya-contact-form input:not([type=hidden]), .webform-submission-alshaya-contact-form textarea, .profile-form input, .address-book-address input:not([type=hidden])').each(function () {
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

      // Add active-label for basket promo code field label.
      var promocodeselector = $('.promo-continue-shopping-wrapper .form-item-coupon input');
      promocodeselector.focusout(function () {
        if ($.trim($(this).val()).length !== 0) {
          $(this).parent().find('label').last().addClass('active-label');
        }
        else {
          $(this).parent().find('label').last().removeClass('active-label');
        }
      });
      if ($.trim(promocodeselector.val()).length !== 0) {
        $(promocodeselector).parent().find('label').last().addClass('active-label');
      }
      else {
        $(promocodeselector).parent().find('label').last().removeClass('active-label');
      }
    }
  };

})(jQuery, Drupal);
