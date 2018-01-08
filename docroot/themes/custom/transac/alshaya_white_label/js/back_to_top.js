/**
 * @file
 * Back To Top.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.backToTop = {
    attach: function () {
      $('#backtotop').prependTo('.c-footer');
    }
  };
})(jQuery, Drupal);
