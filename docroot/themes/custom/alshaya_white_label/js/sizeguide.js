/**
 * @file
 * Size and Color Guide js.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.sizeguide = {
    attach: function (context, settings) {
      // JS for converting select list to unformatted list on PDP pages.

      if ($('.content__sidebar .form-item-configurables-size .select2Option').length === 0) {
        $('.content__sidebar .form-item-configurables-size select').select2Option();
      }
    }
  };

})(jQuery, Drupal);
