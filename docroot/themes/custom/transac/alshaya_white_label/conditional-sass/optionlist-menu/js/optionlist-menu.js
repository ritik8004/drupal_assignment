/**
 * @file
 * Optionlist menu link.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.optionlistMenuLink = {
    attach: function (context, settings) {
      // Accordion for option list menu.
      if ($(window).width() > 1023) {
        $('.block-alshaya-options-list-menu').find('.alshaya-options-menu').each(function () {
          Drupal.convertIntoAccordion($(this));
        });
      }
    }
  };

})(jQuery, Drupal);
