/**
 * @file
 * Globaly required scripts to disable modal for mobile.
 */

(function ($, Drupal) {
  'use strict';

  console.log(drupalSettings.alshaya_master.device);

  if (drupalSettings.alshaya_master.device === 'mobile') {
    $('a[data-dialog-type="modal"],  a.mobile-link').each(function () {
      var href = $(this).attr('href');
      $(this).click(function () {
        window.open(drupalSettings.path.baseUrl + href);
        return false;
      });
    });
  }

})(jQuery, Drupal);
