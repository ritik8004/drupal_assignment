(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.sofaSectionalUtilities = {
    attach: function(context) {
      // This event listener Further trigger jquery event to refresh gallery, price block etc,
      // when variant is selected in react sofa sectional component.
      document.addEventListener('react-variant-select', function ({ detail }) {
        const { variant } = detail;
        $('form.sku-base-form').trigger(
          'variant-selected',
          [
            variant,
            null
          ]
        );
      });
    }
  };
})(jQuery, Drupal);
