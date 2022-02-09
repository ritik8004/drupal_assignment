/**
 * @file
 * Custom js file.
 */

(function ($, Drupal) {

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

  // Mobile Language Toggle
  // Language Settings In Mobile View.
  var hide = localStorage.getItem('hide');

  if ($(window).width() <= 1024) {
    var ReachedBottom = $(window).scrollTop() + $(window).height() > $(document).height() - 100;

    if (hide === 'true' || ReachedBottom === true) {
      $('body').removeClass('mobile-language-toggle-active');
      localStorage.setItem('hide', 'true');
    }
    else {
      $('body').addClass('mobile-language-toggle-active');
    }

    $(window).scroll(function () {
      if ($(window).scrollTop() + $(window).height() > $(document).height() - 100 === true && $('body').hasClass('mobile-language-toggle-active')) {
        $('body').removeClass('mobile-language-toggle-active');
        localStorage.setItem('hide', 'true');
      }
    });
  }

  $('.close-lang-toggle').click(function () {
    $('body').removeClass('mobile-language-toggle-active');
    localStorage.setItem('hide', 'true');
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
