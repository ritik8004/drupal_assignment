/**
 * @file
 * Event Listener to alter datalayer.
 */

(function (Drupal) {
  'use strict';

  // Load product details into initial Data layer.
  document.addEventListener('dataLayerContentAlter', (e) => {
    switch (e.detail.type) {
      case 'product':
        var entity = e.detail.page_entity;
        // Assign product GTM variables.
        var data = e.detail.data();
        data.productSKU = (entity.type_id === 'configurable') ? '' : entity.sku;
        data.productStyleCode = entity.sku;
        data.pageType = 'product detail page';
        data.stockStatus = (entity.stock_status === 'IN_STOCK') ? 'in stock' : 'out of stock';
        data.productName = entity.gtm_attributes.name;
        data.productBrand = entity.gtm_attributes.brand;
        data.productPrice = entity.gtm_attributes.price;
        const prices = window.commerceBackend.getPrices(entity, false);
        data.productOldPrice = (prices.price !== entity.gtm_attributes.price) ? prices.price : '';

        // Get product image.
        var image = window.commerceBackend.getFirstImage(entity);
        data.productPictureURL = (Drupal.hasValue(image) && Drupal.hasValue(image.url))
          ? image.url
          : null;
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
    var breadcrumbs = renderRcsBreadcrumb.normalize(entity, {
      nameKey: 'gtm_name',
      breadcrumbTermNameKey: 'category_gtm_name'
    });
    var breadcrumbTitles = [];
    if (Array.isArray(breadcrumbs) && breadcrumbs.length) {
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
      categories.departmentId = ( Array.isArray(breadcrumbs) && breadcrumbs.length > 0 )
        ? breadcrumbs[0].id
        : '';
      categories.list = breadcrumbTitles.join('|');
      // Lowest category as listing category.
      var listing_category = (Array.isArray(breadcrumbs) && breadcrumbs.length > 0)
        ? breadcrumbs.pop()
        : { text : '', id : ''};
      categories.listingName = listing_category.text;
      categories.listingId = listing_category.id;

      categories.majorCategory = breadcrumbTitles.shift();
      categories.minorCategory = breadcrumbTitles.shift();
      categories.subCategory = breadcrumbTitles.shift();
    }

    return categories;
  }
})(Drupal);
