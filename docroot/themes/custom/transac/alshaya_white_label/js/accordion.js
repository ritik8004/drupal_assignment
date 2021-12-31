/**
 * @file
 * Product category accordion.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.accordion = {
    attach: function (context, settings) {

      /**
       * Function to create accordion.
       *
       * @param {object} element
       *   The HTML element inside which we want to make accordion.
       */
      Drupal.covertToAccordion = function (element) {
        element.once('accordion-init').accordion({
          heightStyle: 'content',
          collapsible: true,
          active: false
        });
      };

      // Accordion for advance page category for mobile.
      if ($('.c-accordion').length) {
        $('.c-accordion').each(function () {
          if ($(this).find('ul').length > 0) {
            Drupal.covertToAccordion($(this));
          }
          else {
            $(this).addClass('empty-accordion-delivery-options');
          }
          // Add class on parent of c-accordion-delivery-options so we can hide
          // the paragraph with margin in desktop.
          $(this).parents('.c-promo__item').addClass('c-accordion-delivery-option-parent');
        });
      }
    }
  };
})(jQuery, Drupal);
