// Breadcrumb renderer for product pages.

const breadcrumbRenderer = require('../../alshaya_rcs_magento_placeholders/js/alshaya_rcs_magento_placeholders_breadcrumb-exports.es5');

exports.render = function render(
  settings,
  entity,
  innerHtml
) {
  let breadcrumbs = [];

  // Get categories from entity.
  let { categories } = entity;

  // Get category flagged as `category_ids_in_admin`.
  categories = categories.filter((e) => {
    return entity.category_ids_in_admin.includes(e.id.toString());
  });

  // Build the breadcrumb using the category with deepest level.
  // If they are all at same level, use the first category.
  let max = 0;
  let deepestCategory = null;
  Object.keys(categories).forEach(function (i) {
    const depth = categories[i].level;
    if (depth > max) {
      deepestCategory = categories[i];
      max = depth;
    }
  });

  // Prepare the breadcrumb array.
  Object.keys(deepestCategory.breadcrumbs).forEach(function (i) {
    breadcrumbs.push({
      url: deepestCategory.breadcrumbs[i].category_url_path,
      text: deepestCategory.breadcrumbs[i].category_name,
    });
  });

  // Push the last crumb without a url.
  breadcrumbs.push({
    url: null,
    text: entity.name,
  });

  return breadcrumbRenderer.render(settings, breadcrumbs, innerHtml);
};
