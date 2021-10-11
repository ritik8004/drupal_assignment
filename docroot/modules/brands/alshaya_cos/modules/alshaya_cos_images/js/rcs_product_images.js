/**
 * Listens to the 'alshayaRcsUpdateResults' event and updated the result object.
 */
(function () {
  document.addEventListener('alshayaRcsUpdateResults', (e) => {
    // Return if result is empty.
    if (typeof e.detail.pageType === 'undefined' || e.detail.pageType !== 'product' || typeof e.detail.result === 'undefined') {
      return;
    }

    let product = e.detail.result;

    // Prepare Assets.
    try {
      product.media_gallery = JSON.parse(product.assets_pdp);
    }
    catch (e) {
      product.media_gallery = [];
    }

    product.variants.forEach(function (variant) {
      try {
        variant.product.media_gallery = JSON.parse(variant.product.assets_pdp)
      }
      catch (e) {
        variant.product.media_gallery = [];
      }
    });
  });
})();
