/**
 * Listens to the 'rcsUpdateResults' event and updated the result object.
 */
(function main() {
  // Event listener to update the data layer object with the proper category
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
  });

  RcsEventManager.addListener('alshayaRcsAlterPdpSwatch', function (e) {
    const rawProductData = window.commerceBackend.getProductData(e.detail.sku, false, false);
    rawProductData.variants.forEach(function (variant) {
      if (variant.product.sku === e.detail.variantSku) {
        try {
          const data = JSON.parse(variant.product.assets_swatch);
          // @todo Uncomment this when proper type is available.
          // if (data.type === 'StillMedia/Fabricswatch') {
            e.detail.colorOptionsList = Object.assign(e.detail.colorOptionsList, {
              // @todo Use the proper image style.
              display_value: '<img loading="lazy" src="' + data[0].styles.pdp_gallery_thumbnail + '">',
              swatch_type: data[0].image_type,
            });
          // }
        } catch (e) {
          // Do nothing.
        }
      }
    })
  });
})();
