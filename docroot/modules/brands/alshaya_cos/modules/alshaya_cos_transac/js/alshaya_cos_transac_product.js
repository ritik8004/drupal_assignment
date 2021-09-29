/**
 * Listens to the 'alshayaRcsUpdateResults' event and updated the result object.
 */
(function main($) {
  // Event listener to update the data layer object with the proper category
  // data.
  document.addEventListener('alshayaRcsUpdateResults', (e) => {
    // Return if result is empty.
    if (typeof e.detail.result === 'undefined' || e.detail.pageType !== 'product') {
      return;
    }

    var data = e.detail.result;
    var description = data.description;

    // Add extra data to product description.
    // This will be rendered using handlebars templates to add P tags and H3 titles.
    description.show_product_detail_title = (data.composition || data.washing_instructions || data.article_warning);
    description.composition = data.composition;
    description.washing_instructions = data.washing_instructions;
    description.article_warning = data.article_warning;
    description.product_guide = ''; // @todo create a new config for this.
    description.sku = data.sku;
    e.detail.result.description = description;

    // Append field values to short_description.
    // The text will be trimmed if the description is longer than 160 characters.
    var short_description = { html: description.html };
    short_description.html += (data.composition) ? '' + data.composition : '';
    short_description.html += (data.washing_instructions) ? '' + data.washing_instructions : '';
    short_description.html += (data.article_warning) ? '' + data.article_warning : '';
    e.detail.result.short_description = short_description;

    // @todo add image brand overrides. See CORE-34424.
  });
})(jQuery);
