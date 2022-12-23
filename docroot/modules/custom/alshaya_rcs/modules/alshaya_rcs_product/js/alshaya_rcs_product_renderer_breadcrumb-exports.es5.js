const rcsPhBreadcrumb = require('../../alshaya_rcs_magento_placeholders/js/alshaya_rcs_magento_placeholders_breadcrumb-exports.es5');

/**
 * Breadcrumb renderer for product pages.
 *
 * @param settings
 *    Drupal settings.
 * @param entity
 *    The entity.
 * @param innerHtml
 *    The html.
 * @returns
 *    Return the placeholder replaced breadcrumb markup.
 */
exports.render = function render(
  settings,
  entity,
  innerHtml
) {
  const breadcrumbs = this.normalize(entity);
  return rcsPhBreadcrumb.render(settings, breadcrumbs, innerHtml);
};

/**
 * Normalize the original data.
 *
 * @param data
 *    The original data.
 */
exports.normalize = function normalize(
  data,
  keys = { nameKey: 'name', breadcrumbTermNameKey: 'category_name' }
) {
  if (!Array.isArray(data.categories) || data.categories.length < 1) {
    return [];
  }

  let normalized = [];

  // Get categories from data.
  let { categories } = data;

  // Get category flagged as `category_ids_in_admin`.
  categories = categories.filter((e) => {
    return data.category_ids_in_admin.includes(e.id.toString());
  });

  // Filter out the categories array based on the deepest category id. In our
  // case we are getting the deepest category from breadcrumb_category_id
  // attribute.
  let deepestCategory = null;
  let deepestCategoryId = parseInt(data.breadcrumb_category_id, 10);
  Object.keys(categories).forEach(function (i) {
    if (Drupal.hasValue(categories[i].id)
      && categories[i].id === deepestCategoryId
    ) {
      deepestCategory = categories[i];
      // Return from here once we have the deepest category object.
      return;
    }
  });

  // Build the breadcrumb array.
  if (deepestCategory) {
    normalized = rcsPhBreadcrumb.getNormalizedBreadcrumbs(normalized, deepestCategory, keys);

    // Push the last part of the normalized.
    normalized.push({
      url: deepestCategory.url_path,
      text: deepestCategory[keys.nameKey],
      data_url: deepestCategory.url_path,
      id: deepestCategory.id,
    });
  }

  // Push the last crumb without a url.
  normalized.push({
    url: null,
    text: data[keys.nameKey],
  });

  return normalized;
};
