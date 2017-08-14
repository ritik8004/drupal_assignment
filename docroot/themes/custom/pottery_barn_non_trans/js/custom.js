/**
 * @file
 * Custom js file.
 */

(function ($, Drupal) {
  'use strict';

  // Home page email sign up form popup.
  $('#contact,.email-signup').on('click', function (e) {
    $('.messagepop').show();
    e.preventDefault();
  });

  $('.close-popup').on('click', function () {
    $('.messagepop').hide();
    $('.messages--status').hide();
  });

})(jQuery, Drupal);
