/**
 * @file
 * Globaly required scripts.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.menuToggle = {
    attach: function () {
      $('.menu-toggle').on('click', function (e) {
        $('.menu-navigation').toggleClass('show-menu');
        e.preventDefault();
      });
    }
  };

})(jQuery, Drupal);
