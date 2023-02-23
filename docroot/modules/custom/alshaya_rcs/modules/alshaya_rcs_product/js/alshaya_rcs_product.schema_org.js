(function ($, Drupal, drupalSettings) {
  // Flag to check if metadata has already been added to the HTML.
  var schemaOrgMetadataAdded = false;

  /**
   * Gets additional schema data to add for the main product on PDP.
   *
   * @param {string} sku
   *   The sku value.
   *
   * @returns array
   *   An array of additional product schema data.
   */
  function getProductAdditionalSchemaData(sku) {
    var additionalSchemaData = [];

    // Check for Bazaar voice metadata.
    if (Drupal.hasValue(drupalSettings.alshaya_bazaar_voice) && Drupal.hasValue(drupalSettings.alshaya_bazaar_voice.product_review_schema_request_data)) {
      var bvData = null;
      var url = drupalSettings.alshaya_bazaar_voice.product_review_schema_request_data.url;
      var requestParams = drupalSettings.alshaya_bazaar_voice.product_review_schema_request_data.query;
      requestParams.filter = 'id:' + sku;

      $.ajax({
        url,
        type: 'GET',
        data: requestParams,
        async: false,
        success: function (response) {
          if (!response['HasErrors']) {
            bvData = response;
          }
        }
      });

      var results = bvData && (typeof bvData.Results !== 'undefined') ? bvData.Results : null;
      if (results && results.length > 0) {
        var reviewSchemaData = {};

        var averageOverallRating = (results[0].ReviewStatistics.AverageOverallRating);
        if (Drupal.hasValue(averageOverallRating)) {
          averageOverallRating = averageOverallRating.toString().replace(/,/g, '');
          averageOverallRating = parseFloat(averageOverallRating).toFixed(2);

          // Add schema for aggregate rating.
          reviewSchemaData.aggregateRating = {
            '@type': 'AggregateRating',
              ratingValue: averageOverallRating,
              reviewCount: typeof results[0].ReviewStatistics.TotalReviewCount,
          };
        }

        var reviews = Drupal.hasValue(bvData.Includes)
          ? Object.values(bvData.Includes.Reviews)
          : [];
        if (reviews.length > 0) {
          // Add schema for review.
          reviewSchemaData.review = [];
          reviews.forEach(function (review) {
            reviewSchemaData.review.push({
              '@type': 'Review',
              author:  review.UserNickname,
              datePublished: review.reviewSubmissionTime,
              description: review.ReviewText,
              name: review.Title,
              reviewRating: {
                '@type': 'Rating',
                ratingValue: review.Rating,
              },
            });
          });
        }

        // Add the aggregate ratings and reviews schema metadata.
        additionalSchemaData.push(reviewSchemaData);
      }
    }

    return additionalSchemaData;
  }

  // Add schema.org metadata to the head for RCS Product.
  Drupal.behaviors.alshaya_rcs_schema_org = {
    attach: function (context, settings) {
      var node = $('.entity--type-node').not('[data-sku *= "#"]');
      if (schemaOrgMetadataAdded || !node.length > 0) {
        return;
      }

      var pageMainSku = node.attr('data-sku');
      var pageEntity = globalThis.RcsPhStaticStorage.get('product_data_' + pageMainSku);

      // Wait for entity to be loaded.
      if (!Drupal.hasValue(pageEntity)) {
        return;
      }

      schemaOrgMetadataAdded = true;

      // Check if some additional schema data is required from what is already
      // passed as template.
      var additionalSchemaData = getProductAdditionalSchemaData(pageMainSku);

      // @todo Check Arabic schema data once the page is available.
      var schemaOrgProductData = JSON.parse($('.rcs-templates--product_schema_data').attr('data-product-schema'));
      additionalSchemaData.forEach(function (additionalData) {
        Object.entries(additionalData).forEach(function ([key, data]) {
          schemaOrgProductData[key] = data;
        });
      });

      // Convert to string and pretty print it.
      schemaOrgProductData = JSON.stringify(schemaOrgProductData, null, '\t');

      // Replace any entity placeholders.
      globalThis.rcsPhReplaceEntityPh(schemaOrgProductData, 'product', pageEntity, drupalSettings.path.currentLanguage)
        .forEach(function eachReplacement(r) {
          const fieldPh = r[0];
          const entityFieldValue = r[1];
          schemaOrgProductData = schemaOrgProductData.replace(fieldPh, entityFieldValue);
        });

      // Prepare the script tag with the schema data to attach to the head.
      var schemaOrgProductScript = $('<script>').attr('type', 'application/ld+json').text(schemaOrgProductData);
      // Attach the script tag to the head tag.
      $('head').append(schemaOrgProductScript);
    }
  }
})(jQuery, Drupal, drupalSettings);
