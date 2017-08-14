/**
 * @file
 * Custom js file.
 */

 (function ($, Drupal) {
   'use strict';

  // Home page email sign up form popup.
   var SignupField = $('.form--signup-elements');

   if (SignupField.hasClass('form-item--error')) {
     $('.signup-popup').show();
   }
   else {
     $('.signup-popup').hide();
   }

   $('#contact, .email-signup').on('click', function (e) {
     $('.signup-popup').show();
     e.preventDefault();
   });

   $('.c-footer__copy a').on('click', function (e) {
     $('.privacy-popup').show();
     e.preventDefault();
   });

   $('.close-popup').on('click', function () {
     $('.signup-popup, .privacy-popup').hide();
     $('.messages--status').hide();
   });

 })(jQuery, Drupal);
