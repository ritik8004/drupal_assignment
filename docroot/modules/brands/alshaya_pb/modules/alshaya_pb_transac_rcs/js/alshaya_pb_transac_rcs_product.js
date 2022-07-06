/**
 * Listens to the 'rcsUpdateResults' event and updated the result object.
 */
(function main() {
  // Event listener to update the data layer object with the proper product data.
  RcsEventManager.addListener('rcsUpdateResults', (e) => {
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
})();
