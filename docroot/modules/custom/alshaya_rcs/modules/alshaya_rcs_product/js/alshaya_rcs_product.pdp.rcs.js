/**
 * Global variable which will contain acq_product related data/methods among
 * other things.
 */
window.commerceBackend = window.commerceBackend || {};

(function ($, Drupal, drupalSettings, RcsEventManager){

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
    var productData = window.commerceBackend.getProductData(pageMainSku, null, false);
    var skuForGallery = Drupal.hasValue(selectedSku) ? selectedSku : productData.sku;
    if (product.attr('data-gallery-sku') === skuForGallery) {
      return;
    }
    var viewMode = product.parents('.entity--type-node').attr('data-vmode');

    // Maps gallery value from backend to the appropriate filter.
    var galleryType = 'classic-gallery';
    if (drupalSettings.alshayaRcs.pdpLayout === 'pdp-magazine'
      && drupalSettings.alshayaRcs.pdpGalleryType !== 'classic') {
      galleryType = 'magazine-gallery';
    }

    var context = window.commerceBackend.getProductContext(productData);
    // For product modal, we always use classic gallery.
    if (context === 'modal' || context === 'free_gift') {
      galleryType = 'classic-gallery';
    }

    var gallery = globalThis.rcsPhRenderingEngine
      .render(
        drupalSettings,
        galleryType,
        {
          galleryLimit: viewMode === 'modal' ? 'modal' : 'others',
          skuForGallery,
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
    window.commerceBackend.renderProductLabels(product, selectedSku, pageMainSku);

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

    // Dispatch event for components like express-delivery label or postpay
    // widget to load.
    const event = new CustomEvent('productGalleryLoaded');
    document.dispatchEvent(event);
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
      var variantSku = Drupal.hasValue(productData.firstChild) ? productData.firstChild : productData.variants[0].product.sku;
      window.commerceBackend.updateGallery(node, productData.layout, '', sku, variantSku);
    }
    else {
      window.commerceBackend.updateGallery(node, productData.layout, '', sku);
    }
  }

  /**
   * Renders add to cart form for the given product.
   *
   * @param {object} product
   *   The raw product entity.
   */
  window.commerceBackend.renderAddToCartForm = function renderAddToCartForm(product) {
    var addToCartForm = jQuery('.add_to_cart_form[data-rcs-sku="' + product.sku + '"]');
    var addToCartFormHtml = globalThis.rcsPhRenderingEngine.computePhFilters(product, 'add_to_cart');
    // Render the HTML to the div.
    addToCartForm.html(addToCartFormHtml);
    addToCartForm.addClass('rcs-loaded');
    globalThis.rcsPhApplyDrupalJs(document);
  };

  RcsEventManager.addListener('alshayaPageEntityLoaded', async function pageEntityLoaded(e) {
    var mainProduct = e.detail.entity;
    if (Drupal.hasValue(window.commerceBackend.getProductsInStyle)) {
      mainProduct = await window.commerceBackend.getProductsInStyle(mainProduct);
    }
    // Exclude Free Gift variants in add to cart form in PDP.
    if (mainProduct.type_id === 'configurable' && Drupal.hasValue(window.commerceBackend.isFreeGiftSku)) {
      var freeGiftExcludedVariantList = [];
      mainProduct.variants.forEach(function excludeFreeGifts(variant) {
        if (Drupal.hasValue(window.commerceBackend.isFreeGiftSku) && !window.commerceBackend.isFreeGiftSku(variant.product)) {
          freeGiftExcludedVariantList.push(variant);
        }
      });
      if (freeGiftExcludedVariantList.length) {
        mainProduct.variants = freeGiftExcludedVariantList;
      }
      else {
        // If all variants are free gifts, redirect to 404.
        var rcs404 = `${drupalSettings.rcs['404Page']}?referer=${globalThis.rcsWindowLocation().pathname}`;
        document.body.classList.add('hidden');
        return globalThis.rcsRedirectToPage(rcs404);
      }
    }

    window.commerceBackend.renderAddToCartForm(mainProduct);
  });

  Drupal.behaviors.rcsProductPdpBehavior = {
    attach: function rcsProductPdpBehavior(context) {
      // Once the main product node is ready, we will immediately display the
      // gallery from the first child. This is so that the user gets a quicker
      // glimpse of the gallery and does not have to wait for the styled
      // products data to load. Re-rendering will again happen when the
      // skubaseform is loaded and on variant-selected event.
      if (context === document) {
        var node = $('.entity--type-node[data-vmode="full"]').not('[data-sku *= "#"]');
        node && node.once('rcs-render-gallery-on-load').each(function rcsRenderGalleryOnLoad() {
          if (node.length) {
            renderGallery(node);
          }
        });
      }

      // Keep the Click and Collect section closed by default since the markup
      // will not be ready by this stage and the form will be displayed
      // momentarily.
      $('#pdp-stores-container .c-accordion_content').once('rcs-hide-delivery-options').css({display: 'none'});

      // Check if behavior has been triggered for product modal. If yes, then
      // immediately render the gallery with the available product data.
      if (context instanceof jQuery && context.hasClass('pdp-modal-box')) {
        var node = context.find('.entity--type-node');
        renderGallery(node);
      }
    }
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
    var productType = type + '-products';
    globalThis.rcsPhCommerceBackend.getData(productType, {sku}).then(function productList(response) {
      var mainProduct = (Drupal.hasValue(response) && Drupal.hasValue(response[0])) ? response[0] : null;
      if (mainProduct) {
        var html = globalThis.renderRcsProduct.render(drupalSettings, productType, {}, {}, mainProduct);
        var $selector = $('#rcs-' + productType);
        $selector.html(html);
        globalThis.rcsPhApplyDrupalJs($selector[0]);
      }
    });
  };

})(jQuery, Drupal, drupalSettings, RcsEventManager);
