/**
 * @file
 * Globaly required scripts to disable modal for mobile.
 */

(function ($, Drupal) {
  'use strict';

  if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
    $('a[data-dialog-type="modal"]').each(function () {
      var href = $(this).attr('href');
      $(this).click(function () {
        window.open(Drupal.settings.basePath + href);
        return false;
      })
    })
  }

})(jQuery, Drupal);
