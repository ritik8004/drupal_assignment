/**
 * Listens to the 'rcsUpdateResults' event and updated the result object.
 */
(function main() {

  /**
   * Fetch composition attribute for pdp.
   *
   * @param {object} entity
   *   Rcs Product entity.
   *
   * @return {string}
   *   Returns compostion for the product.
   */
  var fetchCompositionAttribute = function fetchCompositionAttribute (entity) {
      if (entity.type_id == 'configurable') {
      return entity.variants[0].product.composition;
    }
    else {
      return entity.composition;
    }
  };

  /**
   * Process PDP description.
   *
   * @param {object} result
   *   Rcs Product entity.
   */
  var processDescription = function processDescription (result) {
    var data = result;
    // Attributes to be displayed on main page.
    let mainAttributesCode = {
      'fit' : 'FIT',
      'article_description' : 'ARTICLE DESCRIPTION',
    };
    let descriptionDetails = [];
    for (var attributesCode in mainAttributesCode) {
      if (Drupal.hasValue(data[attributesCode])) {
        // Attribute codes are comma separated if they have multiple values.
        let attr_values = data[attributesCode].split(",");
        let labels = [];
        for (let attr_value of attr_values) {
          let label = window.commerceBackend.getAttributeValueLabel(attributesCode, attr_value);
          labels.push(label);
        }
        descriptionDetails.push({
          title: mainAttributesCode[attributesCode],
          data: labels,
        });
      }
    };

    // Get composition attribute based on product type.
    let composition = fetchCompositionAttribute(data);

    // Add extra data to product description.
    // This will be rendered using handlebars templates to add P tags and H3 titles.
    result.description = {
      html: data.description.html,
      composition: composition,
      washing_instructions: data.washing_instructions,
      article_warning: data.article_warning,
      sku: data.sku,
      show_product_detail_title: (data.composition || data.washing_instructions || data.article_warning),
      description_details: descriptionDetails,
      title_name: data.title_name,
    };

    // Append field values to short_description.
    // The text will be trimmed if the description is longer than 160 characters.
    var shortDescription = { html: data.description.html };
    shortDescription.html += (data.composition) ? '' + data.composition : '';
    shortDescription.html += (data.washing_instructions) ? '' + data.washing_instructions : '';
    shortDescription.html += (data.article_warning) ? '' + data.article_warning : '';
    result.short_description = shortDescription;
  };

  // Event listener to update the data layer object with the proper product
  // data.
  RcsEventManager.addListener('rcsUpdateResults', (e) => {
    // Return if result is empty.
    if (typeof e.detail.result === 'undefined' || (e.detail.pageType !== 'product' && e.detail.placeholder !== "products-in-style")) {
      return;
    }
    if (!Array.isArray(e.detail.result)) {
      result = processDescription(e.detail.result);
    }
    else {
      e.detail.result.forEach(function eachValue (result) {
        result = processDescription(result);
      });
    }
  });

  RcsEventManager.addListener('alshayaRcsAlterPdpSwatch', function (e) {
    const rawProductData = window.commerceBackend.getProductData(e.detail.sku, false, false);
    rawProductData.variants.forEach(function (variant) {
      if (variant.product.sku === e.detail.variantSku) {
        // Update swatch elements.
        if (variant.product.swatch_data.swatch_type === 'image') {
          try {
            const data = JSON.parse(variant.product.assets_swatch);
            const uri = variant.product.media[0].thumbnails;
            e.detail.colorOptionsList = Object.assign(e.detail.colorOptionsList, {
              display_value: '<img src="' + uri + '">',
              swatch_type: data[0].image_type,
            });
          }
          catch (e) {
            Drupal.alshayaLogger('warning', 'Invalid swatch asset data for sku @sku', {
              '@sku': variant.product.sku,
            });
          }
        }
        // Override color label.
        e.detail.colorOptionsList.display_label = variant.product.color_label;
      }
    })
  });

})();
