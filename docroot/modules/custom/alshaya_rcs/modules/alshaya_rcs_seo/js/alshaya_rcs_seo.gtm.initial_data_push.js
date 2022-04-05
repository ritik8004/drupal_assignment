/**
 * @file
 * Event Listener to alter datalayer.
 */

(function (drupalSettings) {
  'use strict';

  // Load product details into initial Data layer.
  document.addEventListener('dataLayerContentAlter', (e) => {
    switch (e.detail.type) {
      case 'product':
        var entity = e.detail.page_entity;
        // Assign product GTM variables.
        var data = e.detail.data();
        data.productSKU = (entity.type_id === 'configurable') ? '' : entity.style_code;
        data.productStyleCode = entity.style_code;
        data.pageType = 'product detail page';
        data.stockStatus = (entity.stock_status === 'IN_STOCK') ? 'in stock' : 'out of stock';
        data.productName = entity.name;
        data.productBrand = entity.gtm_attributes.brand;
        data.productPrice = entity.gtm_attributes.price;
        const prices = window.commerceBackend.getPrices(entity, false);
        data.productOldPrice = (prices.price !== entity.gtm_attributes.price) ? prices.price : '';

        // Get product image.
        var image = window.commerceBackend.getFirstImage(entity);
        data.productPictureURL = image.url;
        data.magentoProductID = entity.id;

        // Set categories.
        var categories = getCategoriesAndDepartment(entity, e.detail.type);
        data = Object.assign(data, categories);

        // @todo To be done in CORE-37241.
        data.productColor = '';
        data.productRating = '';
        data.productReview = '';
        break;

      case 'category':
        // Assign product GTM variables.
        var data = e.detail.data();
        // Set categories.
        var categories = getCategoriesAndDepartment(e.detail.page_entity, e.detail.type);
        data = Object.assign(data, categories);
        break;
    }
  });

  /**
   * Get categories and department for Product GTM data.
   * @see Drupal\alshaya_seo_transac\AlshayaGtmManager::fetchDepartmentAttributes()
   *
   * @param {object} entity
   *   The product entity object.
   * @param {string} type
   *   The string key of pageType.
   * @returns
   *   {object} Category and department data.
   */
  function getCategoriesAndDepartment(entity, type) {
    var categories = {
      subCategory : '',
      minorCategory: '',
      majorCategory: '',
      listingName: '',
      listingId: '',
      departmentId: '',
      departmentName: '',
      list: '',
    };

    // Get categories from breadcrumb.
    var breadcrumbs = renderRcsBreadcrumb.normalize(entity);
    var breadcrumbTitles = [];
    if (Array.isArray(breadcrumbs)) {
      // Remove the product from breadcrumb.
      if (type === 'product') {
        breadcrumbs.pop();
      }
      // Get the department name.
      for (let i = 0; i < breadcrumbs.length;  i++) {
        // Store the breadcrumb titles.
        breadcrumbTitles.push(breadcrumbs[i].text);
      }
      categories.departmentName = breadcrumbTitles.join('|'),
      categories.departmentId = breadcrumbs[0].id;
      categories.list = breadcrumbTitles.join('|');
      // Lowest category as listing category.
      var listing_category = breadcrumbs.pop();
      categories.listingName = listing_category.text;
      categories.listingId = listing_category.id;

      categories.majorCategory = breadcrumbTitles.shift();
      categories.minorCategory = breadcrumbTitles.shift();
      categories.subCategory = breadcrumbTitles.shift();
    }

    return categories;
  }
})(drupalSettings);
