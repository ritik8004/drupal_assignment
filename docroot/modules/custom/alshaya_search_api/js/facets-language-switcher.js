/**
 * @file
 * JS around updating language switcher link query.
 */

(function ($) {
  'use strict';

  $.fn.updateLanguageSwitcherLinkQuery = function (langcode, query, pretty_filters) {
    $('.' + langcode + ' a.language-link').each(function () {
      var url = $(this).attr('href');
      var url_parts = url.split('?');
      url_parts = url_parts[0].split('/--')[0];
      var new_url = url_parts + pretty_filters + '?' + query;
      $(this).attr('href', new_url.replace(/\/{2,}/g,'/'));
    });
  };

}(jQuery));
