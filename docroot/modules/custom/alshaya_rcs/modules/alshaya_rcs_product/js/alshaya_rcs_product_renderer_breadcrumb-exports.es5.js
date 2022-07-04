const rcsPhBreadcrumbRenderer = require('../../alshaya_rcs_magento_placeholders/js/alshaya_rcs_magento_placeholders_breadcrumb-exports.es5');

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
  return rcsPhBreadcrumbRenderer.render(settings, breadcrumbs, innerHtml);
};

/**
 * Normalize the original data.
 *
 * @param data
 *    The original data.
 */
exports.normalize = function normalize(
  data,
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

  // Detect the root category.
  let rootCategoryId = null;
  Object.keys(categories).some(function (i) {
    const c = categories[i];
    // Top level categories will not have breadcrumbs and the level is always 2.
    if (c.level === 2 && c.breadcrumbs === null) {
      return rootCategoryId = c.id;
    } else {
      // If we could not find a top level category, we get it from the breadcrumb
      // on the next category.
      if (Array.isArray(c.breadcrumbs) && typeof c.breadcrumbs[0].category_id !== 'undefined') {
        return rootCategoryId = c.breadcrumbs[0].category_id;
      }
    }
  });

  // Build the breadcrumb using the root category, that has the deepest level.
  // If they are all at same level, use the first entry.
  let max = 0;
  let deepestCategory = null;
  Object.keys(categories).forEach(function (i) {
    // Check if the first category in the breadcrumb is the same as the root category.
    if (Array.isArray(categories[i].breadcrumbs)
      && typeof categories[i].breadcrumbs[0].category_id !== 'undefined'
      && categories[i].breadcrumbs[0].category_id === rootCategoryId
    ) {
      const depth = categories[i].level;
      // Find the category with deepest depth and breadcrumbs.
      if (depth > max && categories[i].breadcrumbs !== null) {
        deepestCategory = categories[i];
        max = depth;
      }
    }
  });

  // Build the breadcrumb array.
  if (deepestCategory) {
    Object.keys(deepestCategory.breadcrumbs).forEach(function (i) {
      normalized.push({
        url: deepestCategory.breadcrumbs[i].category_url_path,
        text: deepestCategory.breadcrumbs[i].category_name,
        data_url: deepestCategory.breadcrumbs[i].category_url_path,
        id: deepestCategory.breadcrumbs[i].category_id,
      });
    });

    // Push the last part of the normalized.
    normalized.push({
      url: deepestCategory.url_path,
      text: deepestCategory.name,
      data_url: deepestCategory.url_path,
      id: deepestCategory.id,
    });
  }

  // Push the last crumb without a url.
  normalized.push({
    url: null,
    text: data.name,
  });

  return normalized;
};
