/**
 * @file
 * Sliders.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.accordion = {
    attach: function (context, settings) {
      $('.region__footer-primary').accordion({
        header: '.is-accordion'
      });
    }
  };

})(jQuery, Drupal);
