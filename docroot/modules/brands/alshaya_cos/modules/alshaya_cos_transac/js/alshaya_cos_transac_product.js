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

    // Add extra data to product description. This will be rendered using handlebars templates.
    data.description.show_product_detail_title = (data.composition || data.washing_instructions || data.article_warning);
    data.description.composition = data.composition;
    data.description.washing_instructions = data.washing_instructions;
    data.description.article_warning = data.article_warning;
    data.description.product_guide = ''; // @todo create a new config for this.
    data.description.sku = data.sku;
    e.detail.result.description = data.description;

    // Append text to short_description. This is will be trimmed if the description is longer than 160 characters.
    e.detail.result.short_description = e.detail.result.description + ' ' + data.composition + ' ' + data.washing_instructions +  data.article_warning;

    // @todo add image brand overrides. See CORE-34424.
  });
})(jQuery);
