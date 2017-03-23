/**
 * @file
 * Sliders.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.accordion = {
    attach: function (context, settings) {
      $('.region__footer-primary').accordion({
        header: 'h2'
      });
    }
  };

})(jQuery, Drupal);
