/**
 * @file
 * Product category accordion.
 */

(function ($, Drupal) {
  'use strict';

  /**
   * Function to create accordion.
   *
   * @param {object} element
   *   The HTML element inside which we want to make accordion.
   */
  function covertToAccordion(element) {
    element.once('accordion-init').accordion({
      heightStyle: 'content',
      collapsible: true,
      active: false
    });
  }

  Drupal.behaviors.accordion = {
    attach: function (context, settings) {
      // Accordion for advance page category for mobile.
      $('.c-accordion').once('accordian').each(function () {
        if ($(this).find('ul').length > 0) {
          covertToAccordion($(this));
        }
        else {
          $(this).addClass('empty-accordion-delivery-options');
        }
        // Add class on parent of c-accordion-delivery-options so we can hide
        // the paragraph with margin in desktop.
        $(this).parents('.c-promo__item').addClass('c-accordion-delivery-option-parent');
      });
    }
  };

})(jQuery, Drupal);
