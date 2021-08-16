(function ($, drupalSettings) {

  var schemaOrgMetadataAdded = false;

  // Add schema.org metadata to the head for RCS Product.
  Drupal.behaviors.alshaya_rcs_schema_org = {
    attach: function (context, settings) {
      var node = $('.entity--type-node').not('[data-sku *= "#"]');
      if (schemaOrgMetadataAdded || !node.length > 0) {
        return;
      }
      schemaOrgMetadataAdded = true;
      var pageMainSku = node.attr('data-sku');
      var schemaOrgProductData = JSON.stringify(JSON.parse($('.rcs-templates--product_schema_data').attr('data-product-schema')));

      // @todo This will be added by Bazaar Voice. Once the queries related to
      // BV are resolved, then work on this.
      if (typeof schemaOrgProductData.aggregateRating !== 'undefined') {
        delete schemaOrgProductData.aggregateRating;
      }

      rcsPhReplaceEntityPh(schemaOrgProductData, 'product', RcsPhStaticStorage.get('product_' + pageMainSku), drupalSettings.path.currentLanguage)
        .forEach(function eachReplacement(r) {
          const fieldPh = r[0];
          const entityFieldValue = r[1];
          schemaOrgProductData = schemaOrgProductData.replace(fieldPh, entityFieldValue);
        });

      var schemaOrgProductScript = $('<script>').attr('type', 'application/ld+json').text(schemaOrgProductData);
      $('head').append(schemaOrgProductScript);
    }
  }
})(jQuery, drupalSettings);
