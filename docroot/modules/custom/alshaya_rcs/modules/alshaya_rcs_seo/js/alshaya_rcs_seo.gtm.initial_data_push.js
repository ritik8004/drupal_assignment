/**
 * @file
 * Event Listener to alter datalayer.
 */

(function (drupalSettings) {
  'use strict';

  // Load product details into initial Data layer.
  document.addEventListener('alterInitialDataLayerData', (e) => {
    if (e.detail.type === 'product') {
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
      let image = window.commerceBackend.getFirstImage(entity);
      data.productPictureURL = image.url;
      data.magentoProductID = entity.id;

      // Set categories.
      let categories = getCategoriesAndDepartment(entity);
      for (let prop in categories) {
        data[prop] = categories[prop];
      }

      // @todo To be done in CORE-37241.
      data.productColor = '';
      data.productRating = '';
      data.productReview = '';
    }
  });

  /**
   * Get categories and department for Product GTM data.
   * @see Drupal\alshaya_seo_transac\AlshayaGtmManager::fetchDepartmentAttributes()
   *
   * @param {object} entity
   *   The product entity object.
   * @returns
   *   {object} Category and department data.
   */
  function getCategoriesAndDepartment(entity) {
    var categories = {
      subcategory : '',
      minorCategory: '',
      majorCategory: '',
      listingName: '',
      listingId: '',
      departmentId: '',
      departmentName: '',
    };

    // Get categories from breadcrumb.
    var breadcrumbs = renderRcsBreadcrumb.normalize(entity);
    if (Array.isArray(breadcrumbs)) {
      // Remove the product from breadcrumb.
      breadcrumbs.pop();
      // Get the department name.
      for (let i = 0; i < breadcrumbs.length;  i++) {
        if (categories.departmentName === '') {
          categories.departmentName = breadcrumbs[i].text;
        }
        else {
          categories.departmentName += '|' + breadcrumbs[i].text;
        }
      }
      categories.departmentId = breadcrumbs[0].id;
      categories.majorCategory = breadcrumbs[0].text;
      categories.minorCategory = breadcrumbs[1].text;
      if (typeof breadcrumbs[2] !== 'undefined') {
        categories.subcategory = breadcrumbs[2].text;
      }
      // Lowest category as listing category.
      var listing_category = breadcrumbs.pop();
      categories.listingId = listing_category.text;
      categories.listingName = listing_category.id;
    }

    return categories;
  }
})(drupalSettings);
