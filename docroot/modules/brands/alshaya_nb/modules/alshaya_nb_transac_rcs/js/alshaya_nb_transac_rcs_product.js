/**
 * Listens to the 'rcsUpdateResults' event and updated the result object.
 */
 (function main($) {
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
      primary_material: data.primary_material,
      technologies: data.technologies,
      feature_bullets: data.feature_bullets,
      green_leaf_notice: data.green_leaf_notice,
      green_leaf: true,
      sku: data.sku,
    };

    // Append field values to short_description.
    // The text will be trimmed if the description is longer than 160 characters.
    var short_description = { html: data.description.html };
    short_description.html += (data.primary_material) ? '' + data.primary_material : '';
    short_description.html += (data.technologies) ? '' + data.technologies : '';
    short_description.html += (data.feature_bullets) ? '' + data.feature_bullets : '';
    short_description.html += (data.green_leaf_notice) ? '' + data.green_leaf_notice : '';
    short_description.html += (data.weight) ? '' + data.weight : '';
    e.detail.result.short_description = short_description;
  });
})(jQuery);
