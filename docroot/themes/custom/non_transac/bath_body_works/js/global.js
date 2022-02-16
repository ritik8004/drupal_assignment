/**
 * @file
 * Globaly required scripts.
 */

(function ($) {

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

})(jQuery);
