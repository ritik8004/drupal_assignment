/**
 * @file
 * Globaly required scripts to disable modal for mobile.
 */

(function ($, Drupal) {
  'use strict';

  if (drupalSettings.alshaya_master.device === 'mobile') {
    $('a[data-dialog-type="modal"],  a.mobile-link').each(function () {
      var href = $(this).attr('href');
      $(this).click(function () {
        window.open(Drupal.settings.basePath + href);
        return false;
      });
    });
  }

})(jQuery, Drupal);
