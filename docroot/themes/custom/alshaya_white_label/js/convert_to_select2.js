/**
 * @file
 * Select2 select.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.select2select = {
    attach: function (context, settings) {
      $('.select2-select').once('bind-events').each(function () {
        $(this).select2();
      });
    }
  };
})(jQuery, Drupal);
