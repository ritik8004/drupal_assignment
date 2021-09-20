/**
 * Listens to the 'updateResults' event and updated the result object.
 *
 * @return {object}
 *   An updated object of listing page containing datalayer attributes.
 */
document.addEventListener('updateResults', (e) => {
  // Return if result is empty.
  if (typeof e.detail.result == 'undefined') {
    return null;
  }
  // Prepare the Department Name based on the breadcrumb hierarchy.
  let breadcrumbTitles = [];
  let breadcrumbIds = [];
  if (e.detail.result.breadcrumbs && e.detail.result.breadcrumbs.length) {
    breadcrumbTitles = e.detail.result.breadcrumbs.map(breadcrumb => breadcrumb.category_name);
    breadcrumbIds = e.detail.result.breadcrumbs.map(breadcrumb => breadcrumb.category_id);
  }
  // Push the current term in the array.
  breadcrumbTitles.push(e.detail.result.name);
  breadcrumbIds.push(e.detail.result.id);
  // Prepare datalayer object.
  let dataLayer = {
    departmentName: breadcrumbTitles.join('|'),
    departmentId: breadcrumbIds.shift(),
    listingName: e.detail.result.name,
    listingId: e.detail.result.id,
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
    ...e.detail.result,
    ...dataLayer,
  };
});
