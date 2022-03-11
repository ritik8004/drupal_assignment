(function ($) {

  var cookie_options = {path: '/', expires: 30, secure: true};
  var language = $.cookie('alshaya_lang');
  var current_language = $('html').attr('lang');

  if (typeof language === 'undefined' || !language) {
    // Don't set the cookie, let it be blank and redirection
    // if required be handled by default langcode setting.
    // For any page except / we don't do any redirection
    // so we are fine.
  }
  else {
    // Keep increasing the time, we don't want it to expire at all.
    $.cookie('alshaya_lang', language, cookie_options);
  }

  // Bind event to all language links, update cookie on switching
  // to different language.
  $('.language-switcher-language-url .language-link').once('bind-js').on('mousedown click', function () {
    $.cookie('alshaya_lang', $(this).attr('hreflang'), cookie_options);
  });

})(jQuery);
