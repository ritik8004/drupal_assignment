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
  var key = productKey === 'undefined' || !productKey ? 'productInfo' : productKey;
  if (typeof sku === 'undefined' || sku === null) {
    return drupalSettings[key];
  }

  if (typeof drupalSettings[key] === 'undefined' || typeof drupalSettings[key][sku] === 'undefined') {
    return null;
  }

  return drupalSettings[key][sku];
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
 * @param {string} pageMainSku
 *   Main sku for PDP.
 * @param {string} selectedSku
 *   The selected sku value.
 */
window.commerceBackend.updateGallery = function (product, layout, gallery, pageMainSku, sku) {
  if (gallery === '' || gallery === null) {
    return;
  }

  if (jQuery(product).find('.gallery-wrapper').length > 0) {
    // Since matchback products are also inside main PDP, when we change the variant
    // of the main PDP we'll get multiple .gallery-wrapper, so we are taking only the
    // first one which will be of main PDP to update main PDP gallery only.
    jQuery(product).find('.gallery-wrapper').first().replaceWith(gallery);
  }
  else {
    jQuery(product).find('#product-zoom-container').replaceWith(gallery);
  }

  // COS classic gallery for magazine layout.
  if (layout === 'pdp-magazine' && drupalSettings.pdp_gallery_type == 'classic') {
    layout = 'pdp';
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
    jQuery('#product-zoom-container', product).addClass('whiteout');
    setTimeout(function () {
      Drupal.behaviors.alshaya_product_zoom.attach(document);
      Drupal.behaviors.alshaya_product_mobile_zoom.attach(document);

      // Show thumbnails again.
      jQuery('#product-zoom-container', product).removeClass('whiteout');
    }, 1);
  }
};

/**
 * Gets the configurable color details.
 *
 * @param {string} sku
 *   The sku value.
 *
 * @returns {object}
 *   The configurable color details.
 */
window.commerceBackend.getConfigurableColorDetails = function (sku) {
  var data = {};
  if (drupalSettings.sku_configurable_color_attribute) {
    data.sku_configurable_color_attribute = drupalSettings.sku_configurable_color_attribute;
  }
  if (drupalSettings.sku_configurable_options_color) {
    data.sku_configurable_options_color = drupalSettings.sku_configurable_options_color;
  }
  return data;
}

/**
 * Updates CS/US/Related products on PDP.
 *
 * @param {string} type
 *   Values - crosssel/upsell/related
 * @param {string} sku
 *   SKU value.
 * @param {string} device
 *   Device - mobile/desktop.
 */
window.commerceBackend.updateRelatedProducts = function updateRelatedProducts (type, sku, device) {
  var url = Drupal.url('related-products/' + btoa(sku) + '/' + type + '/' + device + '?cacheable=1');

  Drupal.ajax({
    url,
    progress: {type: 'throbber'},
    type: 'GET',
  }).execute();
};
