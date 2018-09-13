(function ($) {
  'use strict';

  $.fn.updateLanguageSwitcherLinkQuery = function (langcode, query) {
      console.log(langcode);
      console.log(query);
    $('.' + langcode + ' a.language-link').each(function () {
      var url = $(this).attr('href');
      var url_parts = url.split('?');
      $(this).attr('href', url_parts[0] + '?' + query);
    });
  };

}(jQuery));
