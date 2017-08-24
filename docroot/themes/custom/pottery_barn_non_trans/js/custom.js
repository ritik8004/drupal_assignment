/**
 * @file
 * Custom js file.
 */

(function ($, Drupal) {
  'use strict';

  // Home page email sign up form popup.

  $('#contact, .email-signup').on('click', function (e) {
    $('.signup-popup').show();
    $('body').addClass('block-scroll');
    e.preventDefault();
  });

  $('.c-footer__copy a.popup-link').on('click', function (e) {
    $('.privacy-popup').show();
    $('body').addClass('block-scroll');
    e.preventDefault();
  });

  $('.close-popup').on('click', function () {
    $('.signup-popup, .privacy-popup').hide();
    $('.messages--status').hide();
    $('body').removeClass('block-scroll');
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

  $(document).on('mouseup', function (e) {
    var popup = $('.popup-container');
    if (!popup.is(e.target) && popup.has(e.target).length === 0) {
      $('.popup-window').hide();
      $('body').removeClass('block-scroll');
    }
  });

  var button = document.querySelectorAll('.button');
  for (var i = 0; i < button.length; i++) {
    button[i].onmousedown = function (e) {
      var x = (e.offsetX === '') ? e.layerX : e.offsetX;
      var y = (e.offsetY === '') ? e.layerY : e.offsetY;
      var effect = document.createElement('div');
      effect.className = 'effect';
      effect.style.top = y + 'px';
      effect.style.left = x + 'px';
      e.srcElement.appendChild(effect);
      setTimeout(function () {
        e.srcElement.removeChild(effect);
      }, 1100);
    };

    button[i].onmouseover = function (e) {
      var x = (e.offsetX === '') ? e.layerX : e.offsetX;
      var y = (e.offsetY === '') ? e.layerY : e.offsetY;
      var effect = document.createElement('div');
      effect.className = 'effect';
      effect.style.top = y + 'px';
      effect.style.left = x + 'px';
      e.srcElement.appendChild(effect);
      setTimeout(function () {
        e.srcElement.removeChild(effect);
      }, 1100);
    };
  }

})(jQuery, Drupal);
