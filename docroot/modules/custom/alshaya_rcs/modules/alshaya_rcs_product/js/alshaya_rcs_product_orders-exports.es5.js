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
  if (inputs) {
    // Get row item HTML.
    let rowElms = innerHtmlObj.find('tr.order-item-row');
    // Iterate each order row item and replace the placeholders with proper
    // data.
    if (rowElms) {
      rowElms.each((index, row) => {
        const dataAttr = jQuery(row).find('img.rcs-image').data();
        if (dataAttr) {
          jQuery(row).html(replaceOrderPlaceHolders(
            inputs[dataAttr.itemSku],
            row.outerHTML,
            settings,
          ));
        }
      });
    }
  }

  return innerHtmlObj.html();
};

/**
 * Replace the placeholders with the Mobile menu block items.
 *
 * @param {object} product
 *   The product object for table row item.
 * @param {string} itemHtml
 *   The row HTML with Placeholders.
 * @param {object} settings
 *   The drupalSettings object.
 * @returns
 *   {string} Row HTML with proper data.
 */
const replaceOrderPlaceHolders = function (product, itemHtml, settings) {
  // Return if variant is empty.
  if (typeof product === 'undefined') {
    return itemHtml;
  }
  let htmlElms = '';
  // Covert innerHtml to a jQuery object.
  const innerHtmlObj = jQuery('<div>').html(itemHtml);
  // Prepare the data object with image placeholder variable.
  let imagePlaceHolder = innerHtmlObj.find('img.rcs-image');
  if (imagePlaceHolder.length > 0) {
    htmlElms = replaceIndividualPlaceHolder(
      imagePlaceHolder[0].outerHTML,
      'orderDetails',
      // @todo To change the product image to teaser image.
      { 'image': product.image, 'name': product.title },
      settings,
    );
    imagePlaceHolder.replaceWith(htmlElms);
  }
  // Update the product name placeholder if present.
  htmlElms = replaceIndividualPlaceHolder(
    innerHtmlObj[0].innerHTML,
    'orderDetails',
    { 'name': product.name },
    settings,
  );
  innerHtmlObj.html(htmlElms);
  // Prepare the attributes placeholder and data object.
  let attrPlaceHolder = innerHtmlObj.find('div.attr-wrapper:first');
  if (attrPlaceHolder.length > 0) {
    htmlElms = '';
    product.options.forEach(item => {
      // Get labelValue if attr_code is color.
      htmlElms += replaceIndividualPlaceHolder(
        attrPlaceHolder[0].outerHTML,
        'orderDetailAttribute',
        { 'attr_label': item.label, 'attr_value': item.value },
        settings,
      );
    });
    attrPlaceHolder.replaceWith(htmlElms);
  }

  return innerHtmlObj.html();
}

/**
 * Replace individual placeholders.
 *
 * @param {string} itemHtml
 *   The row HTML with Placeholders.
 * @param {object} data
 *   The individual table row item object.
 * @param {object} settings
 *   The drupalSettings object.
 */
const replaceIndividualPlaceHolder = function (itemHtml, entity, data, settings) {
  rcsPhReplaceEntityPh(itemHtml, entity, data, settings.path.currentLanguage)
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
