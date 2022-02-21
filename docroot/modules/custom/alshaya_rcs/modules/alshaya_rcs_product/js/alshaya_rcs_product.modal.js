(function ($, Drupal) {
  'use strict';

  Drupal.alshayaRcs = Drupal.alshayaRcs || {};

  Drupal.behaviors.alshayaRcsModalBehavior = {
    attach: function (context, settings) {
      $('.open-modal-pdp, #dy-recommendation a.product-quick-view-link').once('pdp-modal-processed').on('click', function (e) {
        e.preventDefault();

        // Display loader.
        if (typeof Drupal.cartNotification.spinner_start === 'function') {
          Drupal.cartNotification.spinner_start();
        }

        // Get the template with placeholders for modal view.
        var content = $('<div>').append($('.rcs-templates--product-modal').clone());
        // Rename the class by removing the dummy suffix.
        content
          .find('.acq-content-product-modal-template')
          .removeClass('acq-content-product-modal-template')
          .addClass('acq-content-product-modal');

        // Reset cloud zoom image attributes.
        content.find('.acq-content-product-modal #cloud-zoom-wrap img').attr('data-zoom-url', '"#rcs.product_modal._self|teaser_image#"');
        content.find('.acq-content-product-modal #cloud-zoom-wrap img').attr('src', '"#rcs.product_modal._self|teaser_image#"');

        // Try to get sku from the element clicked. Works with DY block.
        var sku = $(this).data('sku');
        if (!sku) {
          // Try to get sku from parent article. Works with RCS recommended block.
          sku = $(this).parent('article').data('sku');
        }

        globalThis.rcsPhCommerceBackend.getData('product-recommendation', {sku: sku}, null, null, null, true)
          .then(function (entity) {
            if (entity === null || typeof entity === 'undefined') {
              return;
            }

            // Replace placeholders of modal content with product entity.
            let finalMarkup = content.html();
            rcsPhReplaceEntityPh(finalMarkup, 'product_modal', entity, settings.path.currentLanguage)
              .forEach(function eachReplacement(r) {
                const fieldPh = r[0];
                const entityFieldValue = r[1];
                finalMarkup = rcsReplaceAll(finalMarkup, fieldPh, entityFieldValue);
              });
            content.html(finalMarkup);

            // Open modal dailog.
            Drupal.dialog(content, {
              dialogClass: 'pdp-modal-box',
              resizable: false,
              closeOnEscape: false,
              width: 'auto',
              title:"do you want to publish this content ?",
            }).showModal();

            $('.pdp-modal-box').find('.ui-widget-content').attr('id', 'drupal-modal');

            // Modal quantity field.
            $('.sku-base-form .form-item-quantity .form-select').once('select2select').select2({
              minimumResultsForSearch: -1
            });

            // Call behaviours with modal context.
            var modalContext = $('.pdp-modal-box');
            rcsPhApplyDrupalJs(modalContext);
          },
          function () {
            Drupal.alshayaLogger('debug', 'Could not fetch data!');
          });

        return false;
      });
    }
  };
})(jQuery, Drupal);
