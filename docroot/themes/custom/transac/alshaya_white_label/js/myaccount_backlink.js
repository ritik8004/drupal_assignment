/**
 * @file
 * Back link click on my account page.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.backLink = {
    attach: function () {
      $('.back-link').click(function (event) {
        if ($(window).width() > 767) {
          event.preventDefault();
        }
      });
    }
  };
})(jQuery, Drupal);
