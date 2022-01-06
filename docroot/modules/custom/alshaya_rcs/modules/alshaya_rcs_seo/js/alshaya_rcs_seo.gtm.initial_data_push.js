/**
 * @file
 * Event Listener to alter datalayer.
 */

(function ($, drupalSettings) {
  'use strict';

  // Load product details into initial Data layer.
  document.addEventListener('alterInitialDataLayerData', (e) => {
      if (e.detail.type === 'product') {
        var entity = e.detail.page_entity;
        // Load product info from local storage.
        var productArticle = $("article.node--type-rcs-product")
        var productSku = productArticle.attr('gtm-product-sku');
        var langcode = drupalSettings.path.currentLanguage;
        var key = 'product:' + langcode + ':' + productSku;
        var productInfo = JSON.parse(localStorage.getItem(key));

        // Assign product GTM variables.
        var data = e.detail.data();
        data.productSKU = entity.type_id === 'configurable'? '' : entity.style_code;
        data.productStyleCode = entity.style_code;
        data.stockStatus = entity.stock_status;
        data.pageType = 'product detail page';
        if (entity.stock_status === 'IN_STOCK') {
          data.stockStatus = 'in stock';
        }
        else {
          data.stockStatus = 'out of stock';
        }
        data.productName = entity.name;
        data.productBrand = entity.gtm_attributes.brand;
        data.productPrice = entity.gtm_attributes.price;
        data.productOldPrice = (parseFloat(productInfo.price) !== parseFloat(entity.gtm_attributes.price)) ? productInfo.price : '';
        data.productPictureURL = productInfo.image;
        data.magentoProductID = entity.id;
        // Set categories.
        var categories = getCategoriesAndDepartment(entity);
        data.subcategory = categories.subcategory;
        data.minorCategory = categories.minorCategory;
        data.majorCategory = categories.majorCategory;
        data.listingName = categories.listingName;
        data.listingId = categories.listingId;
        data.departmentId = categories.departmentId;
        data.departmentName = categories.departmentName;
        // TODO.
        data.productColor = '';
        data.productRating = '';
        data.productReview = '';
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
})(jQuery, drupalSettings);
