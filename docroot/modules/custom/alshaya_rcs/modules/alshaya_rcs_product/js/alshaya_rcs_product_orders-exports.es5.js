/**
 * Order renderer for order page.
 *
 * @param settings
 *    Drupal settings.
 * @param inputs
 *    The entity.
 * @param innerHtml
 *    The html.
 * @returns
 *    Return the placeholder replaced order markup.
 */
exports.render = function render(
  settings,
  inputs,
  innerHtml
) {
  // Covert innerHtml to a jQuery object.
  const innerHtmlObj = jQuery('<div>').html(innerHtml);
  // Proceed only if inputs is not empty.
  if (inputs.length) {
    // Get row item HTML.
    let rowElms = innerHtmlObj.find('tr.order-item-row');
    let rowHtml = '';
    // Prepare the input variable.
    let data = [];
    inputs.forEach(input => {
      data[input['sku']] = input;
    });
    if (rowElms) {
      rowElms.each((index, row) => {
        const sku = jQuery(row).find('img').data('sku');
        rowHtml += replaceOrderPlaceHolders(data[sku], row.outerHTML, settings);
      });
    }
    rowElms.html(rowHtml);
  }

  return innerHtmlObj.html();
};

/**
 * Replace the placeholders with the Mobile menu block items.
 *
 * @param {object} data
 *   The individual table row item object.
 * @param {string} itemHtml
 *   The row HTML with Placeholders.
 * @param {object} settings
 *   The drupalSettings object.
 * @returns
 *   {string} Row HTML with proper data.
 */
const replaceOrderPlaceHolders = function (data, itemHtml, settings) {
  // Return if data is empty.
  if (!data.length) {
    return;
  }
  // Prepare the data object with placeholder variables.
  data['variants'].forEach(item => {
    assests = JSON.parse(item['product']['assets_teaser']);
    data.image = assests.product_teaser;
  });

  rcsPhReplaceEntityPh(itemHtml, 'orderDetails', data, settings.path.currentLanguage)
  .forEach(function eachReplacement(r) {
    const fieldPh = r[0];
    const entityFieldValue = r[1];
    // Apply the replacement on all the elements containing the
    // placeholder. We filter to keep only the child element
    // and not the parent ones.
    itemHtml = rcsReplaceAll(itemHtml, fieldPh, entityFieldValue);
  });

  return itemHtml;
}
