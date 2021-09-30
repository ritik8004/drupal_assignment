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

    // Add extra data to product description.
    // This will be rendered using handlebars templates to add P tags and H3 titles.
    e.detail.result.description = {
      html: data.description.html,
      composition: data.composition,
      washing_instructions: data.washing_instructions,
      article_warning: data.article_warning,
      // @TODO product_guide will be generated dynamically from config (to be done in V1)
      // The content will be a text similar to the text below, with a link to a page.
      // See https://alshayagroup.atlassian.net/browse/CORE-34431?focusedCommentId=595906.
      product_guide: 'Make sure that your favorite items remain long-loved pieces for years to come. <a href="/product/guides">Read our product care guide.</a>',
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

    // @todo add image brand overrides. See CORE-34424.
  });
})(jQuery);
