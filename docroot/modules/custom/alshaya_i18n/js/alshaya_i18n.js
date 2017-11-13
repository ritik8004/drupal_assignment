(function ($) {
  'use strict';

  var cookie_options = {path: '/', expires: 30, secure: true};
  var language = $.cookie('alshaya_lang');
  var current_language = $('html').attr('lang');

  if (typeof language === 'undefined' || !language) {
    // Set current language as selected one on page load.
    $.cookie('alshaya_lang', current_language, cookie_options);
  }
  else if (current_language !== language) {
    // If current language doesn't match the language in cookie
    // we redirect to language in cookie.
    window.location.href = $('.language-switcher-language-url:first .language-link[hreflang="' + language + '"]').attr('href');
  }
  else {
    // Keep increasing the time, we don't want it to expire at all.
    $.cookie('alshaya_lang', $(this).attr('hreflang'), cookie_options);
  }

  // Bind event to all language links, update cookie on switching
  // to different language.
  $('.language-switcher-language-url .language-link').once('bind-js').on('mousedown click', function () {
    $.cookie('alshaya_lang', $(this).attr('hreflang'), cookie_options);
  });

})(jQuery);
