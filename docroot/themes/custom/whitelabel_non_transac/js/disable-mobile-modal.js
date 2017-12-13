/**
 * @file
 * Globaly required scripts to disable modal for mobile.
 */

(function ($, Drupal) {
  'use strict';
  
  if (navigator.userAgent.match(/Mobi/)) {
    $('a[data-dialog-type="modal"],  a.mobile-link').each(function () {
      $(this).removeClass('use-ajax');
      var href = $(this).attr('href');
      $(this).click(function (e) {
        e.preventDefault();
        window.location.href = href;
        return false;
      });
    });
  }

})(jQuery, Drupal);
