/**
 * Global variable which will contain acq_product related data/methods among
 * other things.
 */
window.commerceBackend = window.commerceBackend || {};

(function ($, Drupal, drupalSettings){
  // Stores information about initial rendering of gallery.
  var pdpGalleryRendered = false;

  /**
   * Get the labels data for the selected SKU.
   *
   * @param {object} product
   *   The product wrapper jQuery object.
   * @param {string} sku
   *   The sku for which labels is to be retreived.
   * @param {string} mainSku
   *   The main sku for the PDP.
   */
  function renderProductLabels(product, sku, mainSku) {
    window.commerceBackend.getProductLabelsData(mainSku, sku).then(function (labelsData) {
      globalThis.rcsPhRenderingEngine.render(
        drupalSettings,
        'product-labels',
        {
          sku,
          mainSku,
          type: 'pdp',
          labelsData,
          product,
        },
      );
    }).catch(function(e) {
      Drupal.alshayaLogger('error', 'Failed to fetch Product Labels for sku @sku. Message @message.', {
        '@sku': sku,
        '@message': e.message,
      });
    });
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
  window.commerceBackend.updateGallery = async function (product, layout, productGallery, pageMainSku, selectedSku) {
    const productData = window.commerceBackend.getProductData(pageMainSku, null, false);
    const viewMode = product.parents('.entity--type-node').attr('data-vmode');

    // Maps gallery value from backend to the appropriate filter.
    var galleryType = 'classic-gallery';
    if (drupalSettings.alshayaRcs.pdpLayout === 'pdp-magazine'
      && drupalSettings.alshayaRcs.pdpGalleryType !== 'classic') {
      galleryType = 'magazine-gallery';
    }

    var context = window.commerceBackend.getProductContext(productData);
    // For product modal, we always use classic gallery.
    if (context === 'modal') {
      galleryType = 'classic-gallery';
    }

    var gallery = globalThis.rcsPhRenderingEngine
      .render(
        drupalSettings,
        galleryType,
        {
          galleryLimit: viewMode === 'modal' ? 'modal' : 'others',
          sku: selectedSku,
        },
        { },
        productData,
        drupalSettings.path.currentLanguage,
        null,
      );

    if (gallery === '' || gallery === null) {
      return;
    }

    // Here we render the product labels asynchronously.
    // If we try to do it synchronously, then javascript moves on to other tasks
    // while the labels are fetched from the API.
    // This causes discrepancy in the flow, since in V1 the updateGallery()
    // executes completely in one flow.
    renderProductLabels(product, selectedSku, pageMainSku);

    if ($(product).find('.gallery-wrapper').length > 0) {
      // Since matchback products are also inside main PDP, when we change the variant
      // of the main PDP we'll get multiple .gallery-wrapper, so we are taking only the
      // first one which will be of main PDP to update main PDP gallery only.
      $(product).find('.gallery-wrapper').first().replaceWith(gallery);
    }
    else {
      $(product).find('#product-zoom-container').replaceWith(gallery);
    }

    // COS classic gallery for magazine layout.
    if (layout === 'pdp-magazine' && drupalSettings.alshayaRcs.pdpGalleryType == 'classic') {
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
      $('#product-zoom-container', product).addClass('whiteout');
      setTimeout(function () {
        Drupal.behaviors.alshaya_product_zoom.attach(document);
        Drupal.behaviors.alshaya_product_mobile_zoom.attach(document);

        // Show thumbnails again.
        $('#product-zoom-container', product).removeClass('whiteout');
      }, 1);
    }
  };

  /**
   * Helper function to render gallery for product.
   *
   * @param {object} node
   *   The HTML product element.
   */
   function renderGallery(node) {
    var sku = node.attr('data-sku');
    const productData = window.commerceBackend.getProductData(sku, null, false);
    if (productData.type_id === 'configurable') {
      window.commerceBackend.updateGallery(node, productData.layout, '', sku, productData.variants[0].product.sku);
    }
    else {
      window.commerceBackend.updateGallery(node, productData.layout, '', sku);
    }
  }

  // Event Listener to perform action post the results are updated.
  RcsEventManager.addListener('postUpdateResultsAction', async function loadAddToCartForm(e) {
    // Return if result is empty and page type is not product.
    if (e.detail.pageType !== 'product'
      || !Drupal.hasValue(e.detail.result)
      || (Drupal.hasValue(e.detail.placeholder) && e.detail.placeholder !== 'product-recommendation')) {
      return null;
    }

    var mainProduct = e.detail.result;
    // If color split is enabled, we process the styled products.
    if (Drupal.hasValue(window.commerceBackend.getProductsInStyle)) {
      mainProduct = await window.commerceBackend.getProductsInStyle(mainProduct);
    }

    var addToCartForm = null;
    if (Drupal.hasValue(e.detail.placeholder) && e.detail.placeholder === 'product-recommendation') {
      addToCartForm = jQuery('#drupal-modal #add_to_cart_form');
    }
    else {
      addToCartForm = jQuery('#add_to_cart_form');
    }

    var addToCartFormHtml = globalThis.rcsPhRenderingEngine.computePhFilters(mainProduct, 'add_to_cart');
    // Render the HTML to the div.
    addToCartForm.html(addToCartFormHtml);
    globalThis.rcsPhApplyDrupalJs(document);
  });

  Drupal.behaviors.rcsProductPdpBehavior = {
    attach: function rcsProductPdpBehavior(context) {
      // Once the main product node is ready, we will immediately display the
      // gallery from the first child. This is so that the user gets a quicker
      // glimpse of the gallery and does not have to wait for the styled
      // products data to load. Re-rendering will again happen when the
      // skubaseform is loaded and on variant-selected event.
      if (!pdpGalleryRendered) {
        var node = $('.entity--type-node[data-vmode="full"]').not('[data-sku *= "#"]');
        if (node.length) {
          pdpGalleryRendered = true;
          renderGallery(node);
        }
      }

      // Check if behavior has been triggered for product modal. If yes, then
      // immediately render the gallery with the available product data.
      if (context instanceof jQuery && context.hasClass('pdp-modal-box')) {
        var node = context.find('.entity--type-node');
        renderGallery(node);
      }

      // Keep the Click and Collect section closed by default since the markup
      // will not be ready by this stage and the form will be displayed
      // momentarily.
      $('#pdp-stores-container .c-accordion_content', node).css({display: 'none'});
    }
  }
})(jQuery, Drupal, drupalSettings);
