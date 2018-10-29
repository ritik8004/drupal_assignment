/**
 * @file
 * JS around updating language switcher link query.
 */

(function ($) {
  'use strict';

  $.fn.updateLanguageSwitcherLinkQuery = function (langcode, query) {
    $('.' + langcode + ' a.language-link').each(function () {
      var url = $(this).attr('href');
      var url_parts = url.split('?');
      $(this).attr('href', url_parts[0] + '?' + query);
    });
  };

}(jQuery));
