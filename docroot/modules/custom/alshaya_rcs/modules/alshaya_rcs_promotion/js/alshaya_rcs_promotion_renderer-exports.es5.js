// Render function to prepare markup and data attributes for PLP.
exports.render = function render(
  settings,
  entity,
  innerHtml
) {
  // Covert innerHtml to a jQuery object.
  const innerHtmlObj = jQuery(innerHtml);

  // Proceed only if entity is present.
  if (entity !== null) {
    // Add required data as data-attributes.
    innerHtmlObj.attr({
      'data-promotion-id': entity.id,
    });
  }

  return innerHtmlObj[0].outerHTML;
}
