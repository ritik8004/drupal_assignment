/**
 * @file
 * Sliders.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.accordion = {
    attach: function (context, settings) {
      function moveContextualLink(parent, body) {
        $(parent).each(function () {
          var contextualLink = $(this).find('.c-accordion__title').next();
          $(this).append(contextualLink);
        });
      }

      moveContextualLink('.c-accordion');
      $('.region__sidebar-first').accordion({
        header: '.c-accordion__title'
      });
    }
  };

})(jQuery, Drupal);
