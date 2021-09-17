(function ($, Drupal) {
  'use strict';

  Drupal.alshayaRcs = Drupal.alshayaRcs || {};

  Drupal.behaviors.alshayaRcsModalBehavior = {
    attach: function (context, settings) {
      $('.open-modal-pdp').once('pdp-modal-processed').on('click', function (e) {
        e.preventDefault();

        // Get the template with placeholders for modal view.
        var content = $('<div>').append($('.rcs-templates--product-modal').clone());

        // Reset cloud zoom image attributes.
        content.find('.acq-content-product-modal #cloud-zoom-wrap img').attr('data-zoom-url', '"#rcs.product_modal._self|image#"');
        content.find('.acq-content-product-modal #cloud-zoom-wrap img').attr('src', '"#rcs.product_modal._self|image#"');

        // Get Product Details.
        var request = {
          uri: '',
          method: 'GET',
          headers: [],
        };

        request.uri += "graphql";
        request.method = "POST";
        request.headers.push(["Content-Type", "application/json"]);
        request.headers.push(["Store", settings.alshayaRcs.commerceBackend.store]);

        // Get url key for product whose details are required.
        var skuUrlKey = $(this).parent('article').find('.sku-url-key').val();

        // Prepare query for graphQl.
        request.data = JSON.stringify({
          query: Drupal.alshayaRcs.getProductQuery(skuUrlKey)
        });
        var headers = {};

        request.headers.forEach(function (header) {
          headers[header[0]] = header[1];
        });

        $.ajax({
          url: settings.alshayaRcs.commerceBackend.baseUrl + '/' + request.uri,
          method: request.method,
          headers: headers,
          data: request.data,
          success: function (response) {
            // Get product entity from response.
            var entity = response.data.products.items[0];

            if (entity === null || typeof entity === 'undefined') {
              return;
            }

            // Get attributes to be replaced.
            var attributes = rcsPhGetSetting('placeholderAttributes');

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
            rcsPhApplyDrupalJs(modalContext)
          },
          error: function () {
            console.log('Could not fetch data!');
          }
        });

        return false;
      });
    }
  };

  /**
   * Get query for graphQl.
   *
   * @param {string} urlKey
   *   Url key of product.
   * @returns {string}
   *   Query string for graphql.
   */
  Drupal.alshayaRcs.getProductQuery = function (urlKey) {
    return `{products(filter: {url_key: {eq: "`+ urlKey + `"}}) ${rcsPhGraphqlQuery.products}}`;
  };
})(jQuery, Drupal);
