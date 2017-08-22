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

  $('.c-footer__copy a.popup-link').on('click', function (e) {
    $('.privacy-popup').show();
    e.preventDefault();
  });

  $('.close-popup').on('click', function () {
    $('.signup-popup, .privacy-popup').hide();
    $('.messages--status').hide();
  });

  Drupal.behaviors.potteryBarnStoreFinder = {
    attach: function (context, settings) {
      // Store Finder, opening hours toggle.
      // @todo: Remove when the site is reinstalled, or configs are imported.
      $('.view-id-stores_finder, .geolocation-common-map-container')
        .on('click', '.hours--wrapper', function () {
          $(this).find('.hours--label').toggleClass('open');
        });
    }
  };

  // Mobile Language Toggle
  // Language Settings In Mobile View.
  if ($(window).width() < 767) {
    setTimeout(function () {
      $('body').addClass('mobile-language-toggle-active');
    }, 1000);

    $(window).scroll(function () {
      if ($(window).scrollTop() + $(window).height() > $(document)
          .height() - 100) {
        $('body').removeClass('mobile-language-toggle-active');
      }
    });
  }

  $('.close-lang-toggle').click(function () {
    setTimeout(function () {
      $('body').removeClass('mobile-language-toggle-active');
    }, 1000);
  });

})(jQuery, Drupal);
