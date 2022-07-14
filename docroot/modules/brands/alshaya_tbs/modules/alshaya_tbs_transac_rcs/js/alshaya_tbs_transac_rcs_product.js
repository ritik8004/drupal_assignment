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

    // Top title attributes field values.
    var topTitleAttributes = [];
    if (Drupal.hasValue(data.top_three_attributes_1)) {
      topTitleAttributes.push(data.top_three_attributes_1);
    }
    if (Drupal.hasValue(data.top_three_attributes_2)) {
      topTitleAttributes.push(data.top_three_attributes_2);
    }
    if (Drupal.hasValue(data.top_three_attributes_3)) {
      topTitleAttributes.push(data.top_three_attributes_3);
    }

    if (topTitleAttributes.length !== 0) {
      // Append top title attributes field values in result.
      e.detail.result.top_title_attributes = topTitleAttributes.join(', ');
    }

    // Add extra data to product description.
    // This will be rendered using handlebars templates to add P tags and H3 titles.
    // @todo We have to work on the description part once we get the "season"
    // atrribute for the specifications part tracked in the following ticket -
    // https://alshayagroup.atlassian.net/browse/CORE-34014
    var description = data.description.html;
    description += (Drupal.hasValue(data.bullet_points)) ? data.bullet_points : '';
    description += (drupalSettings.pdpShowSpecifications && Drupal.hasValue(data.color)) ? data.color : '';
    e.detail.result.description = {
      html: description,
      sku: data.sku,
    };

    // Append field values to short_description.
    // The text will be trimmed if the description is longer than 160 characters.
    e.detail.result.short_description = { html: data.description.html };
  });
})();
