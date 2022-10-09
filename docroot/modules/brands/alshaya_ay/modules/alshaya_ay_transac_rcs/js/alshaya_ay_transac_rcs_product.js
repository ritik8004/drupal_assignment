/**
 * Listens to the 'rcsUpdateResults' event and updated the result object.
 */
(function main(RcsEventManager) {
  // Event listener to update the product data object.
  RcsEventManager.addListener('rcsUpdateResults', function updateProductData(e) {
    // Return if result is empty.
    if (typeof e.detail.result === 'undefined' || e.detail.pageType !== 'product') {
      return;
    }

    var data = e.detail.result;

    // Additional attributes to be displayed in pdp.
    var additional_attributes = {
      'fit': Drupal.t('FIT'),
      'fabrication': Drupal.t('FABRICATION'),
      'how_to_use': Drupal.t('HOW TO USE'),
      'why_we_love_it': Drupal.t('WHY WE LOVE IT'),
    };
  
    let additionalAttributes = [];
    for (var attributesCode in additional_attributes) {
      if (Drupal.hasValue(data[attributesCode])) {
        additionalAttributes[attributesCode] = {
          'value': data[attributesCode],
          'label': additional_attributes[attributesCode],
        }
      }
    };

    // Add extra data to product description.
    var description = data.description.html;
    e.detail.result.description = {
      html: [{value: {'#markup': description }}],
      additional_attributes: additionalAttributes,
    };
  
    // Append field values to short_description.
    // The text will be trimmed if the description is longer than 160 characters.
    e.detail.result.short_description = { html: data.description.html[0].value['#markup'] };
  });
})(RcsEventManager);
