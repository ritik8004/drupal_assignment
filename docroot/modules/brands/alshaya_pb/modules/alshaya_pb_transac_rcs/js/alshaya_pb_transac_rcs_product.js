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

    // Add extra data to product description.
    // @todo Display field short_description provided in CORE-34020
    var description = data.description.html;
    e.detail.result.description = {
      html: description,
      sku: data.sku,
    };

    // Append field values to short_description.
    // The text will be trimmed if the description is longer than 160 characters.
    e.detail.result.short_description = { html: data.description.html };
  });
})(RcsEventManager);
