/**
 * Prepares the values for Datalayer attribute placeholders.
 *
 * @param {object} result
 *   An object containing info of listing page.
 * @return {object}
 *   An updated object of listing page containing datalayer attributes.
 */
rcsPhPrepareListingDataLayer = (result) => {
  // Return if result is empty.
  if (result == null) {
    return result;
  }
  // Prepare the Department Name based on the breadcrumb hirerchy.
  let breadcrumbTitles = [];
  let breadcrumbIds = [];
  if (result.breadcrumbs && result.breadcrumbs.length) {
    breadcrumbTitles = result.breadcrumbs.map(breadcrumb => breadcrumb.category_name);
    breadcrumbIds = result.breadcrumbs.map(breadcrumb => breadcrumb.category_id);
  }
  // Push the current term in the array.
  breadcrumbTitles.push(result.name);
  breadcrumbIds.push(result.id);
  // Prepare datalayer object.
  let dataLayer = {
    departmentName: breadcrumbTitles.join('|'),
    departmentId: breadcrumbIds.shift(),
    listingName: result.name,
    listingId: result.id,
    majorCategory: breadcrumbTitles.shift(),
    minorCategory: breadcrumbTitles.shift(),
    subCategory: breadcrumbTitles.shift(),
  }
  // Update the window datalayer object.
  window.dataLayer.forEach((dataLayerValue, key) => {
    for (item in dataLayer) {
      if (dataLayerValue.hasOwnProperty(item)) {
        window.dataLayer[key][item] = dataLayer[item];
      }
    }
  });

  return {
    ...result,
    ...dataLayer,
  };
}
