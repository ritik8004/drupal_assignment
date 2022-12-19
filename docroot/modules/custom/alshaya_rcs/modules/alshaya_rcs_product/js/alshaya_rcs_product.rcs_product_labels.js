/**
 * Global variable which will contain acq_product related data/methods among
 * other things.
 */
window.commerceBackend = window.commerceBackend || {};

(function (Drupal, drupalSettings) {

  /**
   * Local static data store.
   */
  let staticDataStore = {
    labels: {},
    parent: {},
  };

  /**
   * Gets the siblings and parent of the given sku.
   *
   * @param {string} sku
   *   The given sku.
   *
   * @returns
   *   An object containing the product skus in the keys and the product entities
   * in the values.
   * If sku is simple and is the main product, then sku is returned.
   * If sku is simple a child product, then all the siblings and parent are
   * returned.
   * If sku is configurable, then the sku and its children are returned.
   */
  function getSkuSiblingsAndParent(sku) {
    const allProducts = window.commerceBackend.getProductData(null, null, false);
    const data = {};

    Object.keys(allProducts).forEach(function eachMainProduct(mainProductSku) {
      const mainProduct = allProducts[mainProductSku];

      if (mainProduct.sku === sku) {
        data[sku] = mainProduct;
        if (mainProduct.type_id === 'configurable') {
          mainProduct.variants.forEach(function eachVariantProduct(variant) {
            data[variant.product.sku] = variant.product;
          });
        }
      }
      else {
        if (mainProduct.type_id === 'configurable') {
          mainProduct.variants.forEach(function eachVariantProduct(variant) {
            if (variant.product.sku === sku) {
              data[mainProduct.sku] = mainProduct;
              mainProduct.variants.forEach(function eachVariantProduct(variant) {
                data[variant.product.sku] = variant.product;
              });
            }
          });
        }
      }
    });

    return data;
  }

  /**
   * Gets the parent SKU for the given SKU.
   *
   * @param {string} mainSku
   *   The main sku. This will be used to fetch the product data from static
   *   storage.
   * @param {string} sku
   *   SKU value.
   *
   * @returns {string}
   *   The parent SKU value.
   */
  function getParentSkuBySku(mainSku, sku) {
    if (Drupal.hasValue(staticDataStore.parent[sku])) {
      return staticDataStore.parent[sku];
    }
    staticDataStore.parent[sku] = null;
    var productData = window.commerceBackend.getProductData(mainSku);
    if (Drupal.hasValue(productData.variants)
      && Drupal.hasValue(productData.variants[sku])
      && Drupal.hasValue(productData.variants[sku].parent_sku)
    ) {
      staticDataStore.parent[sku] = productData.variants[sku].parent_sku;
    }

    return staticDataStore.parent[sku];
  }

  /**
   * Fetches product labels from backend, processes and stores them in storage.
   *
   * @param {string} mainSku
   *   Main sku value.
   */
  async function processAllLabels(mainSku) {
    // If labels have already been fetched for mainSku, they will be available
    // in static storage. Hence no need to process them again.
    if (Drupal.hasValue(staticDataStore.labels[mainSku])) {
      return;
    }

    // Fetch the parent and siblings of the product.
    const products = getSkuSiblingsAndParent(mainSku);
    const productIds = {};
    Object.keys(products).forEach(function (sku) {
      staticDataStore.labels[sku] = [];
      productIds[products[sku].id] = sku;
    });

    // Fetch all sku values, both for the main product and the styled products.
    var allProductsData = window.commerceBackend.getProductData();
    Object.keys(allProductsData).forEach(function eachProduct(productSku) {
      staticDataStore.labels[productSku] = [];
      productIds[allProductsData[productSku].id] = productSku;
    });

    const labels = await globalThis.rcsPhCommerceBackend.getData(
      'labels',
      { productIds: Object.keys(productIds) },
      null,
      drupalSettings.path.currentLanguage,
      ''
    );

    if (Array.isArray(labels) && labels.length) {
      labels.forEach(function (productLabels) {
        if (!productLabels.items.length) {
          return;
        }
        const productId = productLabels.items[0].product_id;
        const sku = productIds[productId];
        staticDataStore.labels[sku] = productLabels.items;
      });
    }
  }

  /**
   * Gets the labels data for the given product ID.
   *
   * @param {string} mainSku
   *   The main sku for the PDP.
   * @param {string} skuForLabel
   *   The sku for which labels is to be retreived.
   *
   * @returns object
   *   The labels data for the given product ID.
   */
  window.commerceBackend.getProductLabelsData = async function getProductLabelsData(mainSku, skuForLabel) {
    await processAllLabels(mainSku);
    // Check if its simple product.
    if (!Drupal.hasValue(skuForLabel)) {
      return staticDataStore.labels[mainSku];
    }
    // If it is a child product not having any label, we display the labels
    // from the parent.
    var parentSku = getParentSkuBySku(mainSku, skuForLabel);
    if (Drupal.hasValue(parentSku)) {
      Object.assign(staticDataStore.labels[skuForLabel], staticDataStore.labels[parentSku]);
    }

    return staticDataStore.labels[skuForLabel];
  }

  /**
   * Get the processed labels in the required format.
   *
   * @param {object} labels
   *   Object containing the list of product labels.
   *
   * @returns {object}
   *   Processed object containing the list of product labels.
   */
  function getProcessedLabels(labels) {
    // Process the labels in the required format.
    for (let [key, value] of Object.entries(labels)) {
      if (value.length > 0) {
        value.forEach((item, index) => {
          value[index] = {
            image: {
              url: item.image,
              alt: item.name,
              title: item.name,
            },
            position: item.position,
          };
        });
      }
      labels[key] = value;
    }

    return labels;
  }

  /**
   * Gets the product labels for all the available variants.
   *
   * @returns
   */
  window.commerceBackend.getProductLabels = async function getProductLabels(mainSku) {
    if (Drupal.hasValue(staticDataStore.labels)) {
      return staticDataStore.labels;
    }

    await processAllLabels(mainSku);

    var processedLabels = getProcessedLabels(staticDataStore.labels);

    // Fetch the parent and siblings of the product.
    const products = getSkuSiblingsAndParent(mainSku);
    // Check if the main sku variants are having the labels, if not then use the
    // parent product labels.
    Object.keys(products).forEach((item) => {
      if (Drupal.hasValue(item)
        && item != mainSku
        && !Drupal.hasValue(processedLabels[item])) {
        // If child items are not having any labels then use the parent labels.
        processedLabels[item] = processedLabels[mainSku];
      }
    });

    return processedLabels;
  }

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
  window.commerceBackend.renderProductLabels = function (product, sku, mainSku) {
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
    }).catch(function (e) {
      Drupal.alshayaLogger('error', 'Failed to fetch Product Labels for sku @sku. Message @message.', {
        '@sku': sku,
        '@message': e.message,
      });
    });
  }

})(Drupal, drupalSettings);
