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
 * @param {Boolean} processed
 *   Whether we require the processed product data or not.
 *
 * @returns {Object}
 *    The product data.
 */
window.commerceBackend.getProductData = function (sku, productKey, processed) {
  if (typeof drupalSettings[productKey] === 'undefined' || typeof drupalSettings[productKey][sku] === 'undefined') {
    return null;
  }

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

/**
 * Gets the configurable combinations for the given sku.
 *
 * @param {string} sku
 *   The sku value.
 *
 * @returns {object}
 *   The object containing the configurable combinations for the given sku.
 */
window.commerceBackend.getConfigurableCombinations = function (sku) {
  return drupalSettings.configurableCombinations[sku];
}

/**
 * Renders the gallery for the given SKU.
 *
 * @param {object} product
 *   The jQuery product object.
 * @param {string} layout
 *   The layout value.
 * @param {string} productGallery
 *   The gallery for the product.
 * @param {string} sku
 *   The sku value.
 * @param {string} parentSku
 *   The parent SKU value if exists.
 */
window.commerceBackend.updateGallery = function (product, layout, gallery, sku, parentSku) {
  if (gallery === '' || gallery === null) {
    return;
  }

  if ($(product).find('.gallery-wrapper').length > 0) {
    // Since matchback products are also inside main PDP, when we change the variant
    // of the main PDP we'll get multiple .gallery-wrapper, so we are taking only the
    // first one which will be of main PDP to update main PDP gallery only.
    $(product).find('.gallery-wrapper').first().replaceWith(gallery);
  }
  else {
    $(product).find('#product-zoom-container').replaceWith(gallery);
  }

  if (layout === 'pdp-magazine') {
    // Set timeout so that original behavior attachment is not affected.
    setTimeout(function () {
      Drupal.behaviors.magazine_gallery.attach(document);
      Drupal.behaviors.pdpVideoPlayer.attach(document);
    }, 1);
  }
  else {
    // Hide the thumbnails till JS is applied.
    // We use opacity through a class on parent to ensure JS get's applied
    // properly and heights are calculated properly.
    $('#product-zoom-container', product).addClass('whiteout');
    setTimeout(function () {
      Drupal.behaviors.alshaya_product_zoom.attach(document);
      Drupal.behaviors.alshaya_product_mobile_zoom.attach(document);

      // Show thumbnails again.
      $('#product-zoom-container', product).removeClass('whiteout');
    }, 1);
  }
};
