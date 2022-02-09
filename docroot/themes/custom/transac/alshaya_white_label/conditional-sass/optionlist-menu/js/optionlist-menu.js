/**
 * @file
 * Optionlist menu link.
 */

(function ($, Drupal) {

  Drupal.behaviors.optionlistMenuLink = {
    attach: function (context, settings) {
      // Accordion for option list menu.
      if ($(window).width() > 1023) {
        $('.block-alshaya-options-list-menu.alshaya-multiple-links').find('.alshaya-options-menu').each(function () {
          Drupal.convertIntoAccordion($(this));
        });
      }
    }
  };

})(jQuery, Drupal);
