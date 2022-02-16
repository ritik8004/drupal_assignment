/**
 * @file
 * JS around updating language switcher link query.
 */

(function ($) {

  $.fn.updateLanguageSwitcherLinkQuery = function (langcode, query, pretty_filters) {
    $('.' + langcode + ' a.language-link').each(function () {
      var url = $(this).attr('href');
      var url_parts = url.split('?');
      url_parts = url_parts[0].split('/--')[0];
      var new_url = url_parts + pretty_filters + '?' + query;
      new_url = Drupal.removeURLParameter(new_url, 'facet_filter_url');
      $(this).attr('href', new_url.replace(/\/{2,}/g,'/'));
    });
  };

}(jQuery));
