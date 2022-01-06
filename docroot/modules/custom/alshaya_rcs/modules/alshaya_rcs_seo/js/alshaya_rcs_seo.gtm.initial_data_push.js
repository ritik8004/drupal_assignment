/**
 * @file
 * Event Listener to alter datalayer.
 */

(function ($, Drupal) {
  'use strict';

  // Load product details into initial Data layer.
  document.addEventListener('alterInitialDataLayerData', (e) => {
      if (e.detail.type === 'product') {
        var entity = e.detail.page_entity;
        // Load product info from local storage.
        var product_article = $("article.node--type-rcs-product")
        var product_sku = product_article.attr('gtm-product-sku');
        var langcode = $('html').attr('lang');
        var key = 'product:' + langcode + ':' + product_sku;
        var productInfo = JSON.parse(localStorage.getItem(key));

        // Assign product GTM variables.
        e.detail.data().productSKU = entity.type_id === 'configurable'? '' : entity.style_code;
        e.detail.data().productStyleCode = entity.style_code;
        e.detail.data().stockStatus = entity.stock_status;
        e.detail.data().pageType = 'product detail page';
        if (entity.stock_status === 'IN_STOCK') {
          e.detail.data().stockStatus = 'in stock';
        }
        else {
          e.detail.data().stockStatus = 'out of stock';
        }
        e.detail.data().productName = entity.name;
        e.detail.data().productBrand = entity.gtm_attributes.brand;
        e.detail.data().productColor = '';
        e.detail.data().productPrice = entity.gtm_attributes.price;
        e.detail.data().productOldPrice = (parseFloat(productInfo.price) !== parseFloat(entity.gtm_attributes.price)) ? productInfo.price : '';
        e.detail.data().productPictureURL = productInfo.image;
        e.detail.data().productRating = '';
        e.detail.data().productReview = '';
        e.detail.data().magentoProductID = entity.id;
        // Set categories.
        var categories = getCategoriesAndDepartment(entity);
        e.detail.data().subcategory = categories.subcategory;
        e.detail.data().minorCategory = categories.minorCategory;
        e.detail.data().majorCategory = categories.majorCategory;
        e.detail.data().listingName = categories.listingName;
        e.detail.data().listingId = categories.listingId;
        e.detail.data().departmentId = categories.departmentId;
        e.detail.data().departmentName = categories.departmentName;
      }
  });

/**
 * Get categories and department for Product GTM data.
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

    for (let i = 0; i < entity.category_ids_in_admin.length ; i++) {
      for (let j = 0; j < entity.categories.length; j++) {
        if (entity.categories[j].id == entity.category_ids_in_admin[i]) {
          if (entity.categories[j].level == 4) {
            categories.subcategory = entity.categories[j].name;
            categories.listingName = entity.categories[j].name;
            categories.listingId = entity.categories[j].id;
            for (let x = 0; x < entity.categories[j].breadcrumbs.length; x++) {
              if (entity.categories[j].breadcrumbs[x].category_level == 2) {
                categories.majorCategory = entity.categories[j].breadcrumbs[x].category_name;
                categories.departmentId = entity.categories[j].breadcrumbs[x].category_id;
                categories.departmentName = categories.majorCategory;
              }
              if (entity.categories[j].breadcrumbs[x].category_level == 3) {
                categories.minorCategory = entity.categories[j].breadcrumbs[x].category_name;
                if (categories.departmentName == '') {
                  categories.departmentName = categories.minorCategory;
                }
                else {categories.departmentName
                  categories.departmentName += '|' +  categories.minorCategory;
                }
              }
            }
            if (categories.departmentName == '') {
              categories.departmentName = categories.subcategory;
            }
            else {
              categories.departmentName += '|' +  categories.subcategory;
            }
            return categories;
          }
        }
      }
    }
    return;
  }
})(jQuery, Drupal);
