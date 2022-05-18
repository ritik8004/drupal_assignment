/**
 * Listens to the 'rcsUpdateResults' event and updated the result object.
 */
(function main() {
  // Event listener to update the data layer object with the proper product
  // data.
  RcsEventManager.addListener('rcsUpdateResults', (e) => {
    // Return if result is empty.
    if (typeof e.detail.result === 'undefined' || e.detail.pageType !== 'product') {
      return;
    }

    var data = e.detail.result;

    // Add extra data to product description.
    // This will be rendered using handlebars templates to add P tags and H3 titles.
    e.detail.result.description = {
      html: data.description.html,
      composition: data.composition,
      washing_instructions: data.washing_instructions,
      article_warning: data.article_warning,
      sku: data.sku,
      show_product_detail_title: (data.composition || data.washing_instructions || data.article_warning),
    };

    // Append field values to short_description.
    // The text will be trimmed if the description is longer than 160 characters.
    var short_description = { html: data.description.html };
    short_description.html += (data.composition) ? '' + data.composition : '';
    short_description.html += (data.washing_instructions) ? '' + data.washing_instructions : '';
    short_description.html += (data.article_warning) ? '' + data.article_warning : '';
    e.detail.result.short_description = short_description;
  });
})();
