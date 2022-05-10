// Render function to prepare markup and data attributes for PLP.
exports.render = function render(
  entity,
  innerHtml
) {
  // Proceed only if entity is present.
  if (entity !== null) {
    // Covert innerHtml to a jQuery object.
    const innerHtmlObj = jQuery(innerHtml);
    // Add required data as data-attributes.
    innerHtmlObj.attr({
      'data-promotion-id': entity.id,
    });

    return innerHtmlObj[0].outerHTML;
  }

  // Add class to remove loader styles after RCS info is filled.
  jQuery('.page-type-promotion').addClass('rcs-loaded');

  return '';
}
