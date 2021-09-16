/**
 * Global variable which will contain acq_product related data/methods among
 * other things.
 */
window.commerceBackend = window.commerceBackend || {};

/**
 * Gets the required data for acq_product.
 *
 * @param {string} sku
 *   The product sku value.
 * @param {string} productKey
 *   The product view mode.
 *
 * @returns {Object}
 *    The product data.
 */
window.commerceBackend.getProductData = function (sku, productKey) {
  return drupalSettings[productKey][sku];
}

/**
 * Processes product data and stores it to local storage.
 *
 * @param {string} viewMode
 *   The product view mode, eg. matchback.
 * @param {object} productData
 *   An object containing some processed product data.
 */
window.commerceBackend.storeProductDataOnAddToCart = function (viewMode, productData) {
  var productInfo = drupalSettings[viewMode][productData.parentSku];
  var options = [];
  var productUrl = productInfo.url;
  var price = productInfo.priceRaw;
  var promotions = productInfo.promotionsRaw;
  var freeGiftPromotion = productInfo.freeGiftPromotion;
  var productDataSKU = productData.sku;
  var parentSKU = productData.sku;
  var maxSaleQty = productInfo.maxSaleQty;
  var maxSaleQtyParent = productInfo.max_sale_qty_parent;
  var gtmAttributes = productInfo.gtm_attributes;
  var isNonRefundable = productInfo.is_non_refundable;

  if (productInfo.type === 'configurable') {
    var productVariantInfo = productInfo['variants'][productData.variant];
    productDataSKU = productData.variant;
    price = productVariantInfo.priceRaw;
    parentSKU = productVariantInfo.parent_sku;
    promotions = productVariantInfo.promotionsRaw;
    freeGiftPromotion = productVariantInfo.freeGiftPromotion || freeGiftPromotion;
    options = productVariantInfo.configurableOptions;
    maxSaleQty = productVariantInfo.maxSaleQty;
    maxSaleQtyParent = productVariantInfo.max_sale_qty_parent;

    if (productVariantInfo.url !== undefined) {
      var langcode = jQuery('html').attr('lang');
      productUrl = productVariantInfo.url[langcode];
    }
    gtmAttributes.price = productVariantInfo.gtm_price || price;
  }
  else if (productInfo.group !== undefined) {
    var productVariantInfo = productInfo.group[productData.sku];
    price = productVariantInfo.priceRaw;
    parentSKU = productVariantInfo.parent_sku;
    promotions = productVariantInfo.promotionsRaw;
    freeGiftPromotion = productVariantInfo.freeGiftPromotion || freeGiftPromotion;
    if (productVariantInfo.grouping_options !== undefined
      && productVariantInfo.grouping_options.length > 0) {
      options = productVariantInfo.grouping_options;
    }
    maxSaleQty = productVariantInfo.maxSaleQty;
    maxSaleQtyParent = productVariantInfo.max_sale_qty_parent;

    var langcode = jQuery('html').attr('lang');
    productUrl = productVariantInfo.url[langcode];
    gtmAttributes.price = productVariantInfo.gtm_price || price;
  }

  // Store proper variant sku in gtm data now.
  gtmAttributes.variant = productDataSKU;
  Drupal.alshayaSpc.storeProductData({
    sku: productDataSKU,
    parentSKU: parentSKU,
    title: productData.product_name,
    url: productUrl,
    image: productData.image,
    price: price,
    options: options,
    promotions: promotions,
    freeGiftPromotion: freeGiftPromotion,
    maxSaleQty: maxSaleQty,
    maxSaleQtyParent: maxSaleQtyParent,
    gtmAttributes: gtmAttributes,
    isNonRefundable: isNonRefundable,
  });
}
