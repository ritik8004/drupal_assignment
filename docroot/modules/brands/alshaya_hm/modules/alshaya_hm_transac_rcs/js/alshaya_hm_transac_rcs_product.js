/**
 * Listens to the 'rcsUpdateResults' event and updated the result object.
 */
(function main() {
  // Event listener to update the data layer object with the proper product
  // data.
  RcsEventManager.addListener('rcsUpdateResults', (e) => {
    // @todo This is copied from COS brand module. We need to work on it for HM.
    // Return if result is empty.
    if (typeof e.detail.result === 'undefined' || e.detail.pageType !== 'product') {
      return;
    }

    var data = e.detail.result;

    // Attributes to be displayed on main page.
    let main_attributes_code = {
      'fit' : 'FIT',
      'article_description' : 'ARTICLE DESCRIPTION',
    };
    let description_details = [];
    for (var attributes_code in main_attributes_code) {
      if (Drupal.hasValue(data[attributes_code])) {
        // Attribute codes are comma seperated if they have multiple values.
        let attr_values = data[attributes_code].split(",");
        let labels = [];
        for (let attr_value of attr_values) {
          let label = window.commerceBackend.getAttributeValueLabel(attributes_code, attr_value);
          labels.push(label);
        }
        description_details.push({
          title: main_attributes_code[attributes_code],
          data: labels,
        });
      }
    };

    // Additional attributes to be displayed on overlay.
    let overlay_attributes_codes = {
      'product_designer_collection' : 'DESIGNER COLLECTION',
      'concept' : 'CONCEPT',
      'product_collection' : 'COLLECTION',
      'product_environment' : 'ENVIRONMENT',
      'product_quality' : 'QUALITY',
      'product_feature' : 'FEATURE',
      'function' : 'FUNCTION',
      'washing_instructions' : 'WASHING INSTRUCTION',
      'dry_cleaning_instructions' : 'DRY CLEAN INSTRUCTION',
      'style' : 'STYLE',
      'clothing_style' : 'CLOTHING STYLE',
      'collar_style' : 'COLLAR STYLE',
      'neckline_style' : 'NECKLINE STYLE',
      'accessories_style' : 'ACCESSORIES STYLE',
      'footwear_style' : 'FOOTWEAR STYLE',
      'fit' : 'FIT',
      'descriptive_length' : 'DESCRIPTIVE LENGTH',
      'garment_length' : 'GARMENT LENGTH',
      'sleeve_length' : 'SLEEVE LENGTH',
      'waist_rise' : 'WAIST RISE',
      'heel_height' : 'HEEL HEIGHT',
      'measurements_in_cm' : 'MEASURMENTS IN CM',
      'color_name' : 'COLOR NAME',
      'fragrance_name' : 'FRAGRANCE NAME',
      'article_fragrance_description' : 'FRAGRANCE DESCRIPTION',
      'article_pattern' : 'PATTERN',
      'article_visual_description' : 'VISUAL DESCRIPTION',
      'textual_print' : 'TEXTUAL PRINT',
      'article_license_company' : 'LICENSE COMPANY',
      'article_license_item' : 'LICENSE ITEM',
    };
    let overlay_properties = [];
    for (var attributes_code in overlay_attributes_codes) {
      if (Drupal.hasValue(data[attributes_code])) {
        // Attribute codes are comma seperated if they have multiple values.
        let attr_values = data[attributes_code].split(",");
        let labels = [];
        for (let attr_value of attr_values) {
          let label = window.commerceBackend.getAttributeValueLabel(attributes_code, attr_value);
          labels.push(label);
        }
        overlay_properties.push({
          title: overlay_attributes_codes[attributes_code],
          data: labels,
        });
      }
    };

    // Get composition attribute based on product type.
    let composition = fetchCompositionAttribute(data);

    // Add extra data to product description.
    // This will be rendered using handlebars templates to add P tags and H3 titles.
    e.detail.result.description = {
      html: data.description.html,
      composition: composition,
      washing_instructions: data.washing_instructions,
      article_warning: data.article_warning,
      sku: data.sku,
      show_product_detail_title: (data.composition || data.washing_instructions || data.article_warning),
      description_details: description_details,
      title_name: data.title_name,
      overlay_properties: overlay_properties,
    };

    // Append field values to short_description.
    // The text will be trimmed if the description is longer than 160 characters.
    var short_description = { html: data.description.html };
    short_description.html += (data.composition) ? '' + data.composition : '';
    short_description.html += (data.washing_instructions) ? '' + data.washing_instructions : '';
    short_description.html += (data.article_warning) ? '' + data.article_warning : '';
    e.detail.result.short_description = short_description;
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

    /**
   * Fetch composition attribute for pdp.
   */
  var fetchCompositionAttribute = function(entity) {
    if (entity.type_id == 'configurable') {
    return entity.variants[0].product.composition;
  }
  else {
    return entity.composition;
  }
};
})();
