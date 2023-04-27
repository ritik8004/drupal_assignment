// Render function to prepare markup and data attributes for PLP.
exports.render = function render(
  entity,
  innerHtml
) {
  // Covert innerHtml to a jQuery object.
  const innerHtmlObj = jQuery(innerHtml);
  // Get all the breadcrumb items and build the hierarchy.
  let hierarchy_list = [];
  let context_list = [];
  let contexts = [];

  // Proceed only if entity is present.
  if (entity !== null) {
    if (entity.breadcrumbs && entity.breadcrumbs.length) {
      entity.breadcrumbs.forEach(item => {
        hierarchy_list.push(item.category_gtm_name);
        // Also, build the rule context.
        context_list.push(formatCleanRuleContext(item.category_gtm_name));
        contexts.push(context_list.join('__'));
      });
    }
    // Push the current category name.
    hierarchy_list.push(entity.gtm_name);
    context_list.push(formatCleanRuleContext(entity.gtm_name));
    contexts.push(context_list.join('__'));
    // Combine all the items.
    hierarchy_list = hierarchy_list.join(' > ');
    // Add required data as data-attributes.
    innerHtmlObj.attr({
      'data-hierarchy': hierarchy_list,
      'data-level': contexts.length - 1,
      'data-rule-context': contexts.reverse(),
      'data-category-field': 'field_category_name.lvl' + (contexts.length - 1),
    });
  }

  return innerHtmlObj[0].outerHTML;
}

/**
 * Format / Clean the rule context string.
 *
 * @param {string} context
 *
 * @returns
 *  {string} Formatted or cleaned rule context.
 */
const formatCleanRuleContext = function (context) {
  context = context.trim().toLowerCase();
  // Remove special characters.
  context = context.replace("/[^a-zA-Z0-9\s]/", "");
  // Ensure duplicate spaces are replaced with single space.
  // H & M would have become H  M after preg_replace.
  context = context.replace('  ', ' ');

  // Replace spaces with underscore.
  context = context.replace(' ', '_');

  return context;
}
