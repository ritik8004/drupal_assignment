/**
 * Listens to the 'rcsUpdateResults' event and updated the result object.
 */
(function main($) {
  // Event listener to update the data layer object with the proper category
  // data.
  RcsEventManager.addListener('rcsUpdateResults', (e) => {
    // Return if result is empty.
    if (typeof e.detail.result === 'undefined' || e.detail.pageType !== 'category') {
      return null;
    }

    // Prepare the Department Name and Id based on the breadcrumb hierarchy.
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
    // Updated the existing result with the datalayer object.
    e.detail.result = {
      ...e.detail.result,
      ...dataLayer,
    }
  });
})(jQuery);
