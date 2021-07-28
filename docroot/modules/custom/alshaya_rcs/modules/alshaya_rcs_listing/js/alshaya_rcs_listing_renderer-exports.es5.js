// @codingStandardsIgnoreFile
// This is because the linter is throwing errors where we use backticks here.
// Once we enable webapack for the custom modules directory, we should look into
// removing the above ignore line.
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

  if (entity.breadcrumbs && entity.breadcrumbs.length) {
    entity.breadcrumbs.forEach(item => {
      hierarchy_list.push(item.category_name);
      // Also, build the rule context.
      context_list.push(formatCleanRuleContext(item.category_name));
      contexts.push(context_list.join('__'));
    });
  }
  // Push the current category name.
  hierarchy_list.push(entity.name);
  context_list.push(formatCleanRuleContext(entity.name));
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

  return innerHtmlObj[0].outerHTML;
}

/**
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
