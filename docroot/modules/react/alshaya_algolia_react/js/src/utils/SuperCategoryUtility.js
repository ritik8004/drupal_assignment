/**
 * Gets the current page super category.
 *
 * @returns string|null
 *   The supercategory if found or null.
 */
function getSuperCategory() {
  const activeMenuItem = document.querySelector('.main--menu .menu--one__link.active');
  if (activeMenuItem !== null) {
    return activeMenuItem.getAttribute('data-super-category-label');
  }
  return null;
}

/**
  * Uses the Algolia optionalFilter feature.
  * Super Category is currently the only optional filter in use.
  * We want to promote the products belonging to current page super category
  * to the top of the search results.
  *
  * @returns string|null
  *   The optional filters or null.
  */
function getSuperCategoryOptionalFilter() {
  const supercategory = getSuperCategory();
  const optionalFilter = drupalSettings.superCategory && supercategory
    ? `${drupalSettings.superCategory.search_facet}:${supercategory}`
    : null;

  return optionalFilter;
}


export {
  getSuperCategory,
  getSuperCategoryOptionalFilter,
};
