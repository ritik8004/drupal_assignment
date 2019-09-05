/**
 * @file
 * Accordion order confirmation.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.accordionOrderConfirmation = {
    attach: function () {
      if ($('.multistep-checkout .user__order--detail').length) {
        $('.collapse-row').fadeOut();
        $('.product--count').on('click', function () {
          $('#edit-confirmation-continue-shopping')
            .toggleClass('expanded-table');
          $(this).toggleClass('expanded-row');
          $(this).nextAll('.collapse-row').fadeToggle('slow');
        });
      }
    }
  };
})(jQuery, Drupal);
