/**
 * @file
 * Contains utility function for wishlist seo gtm.
 */

(function ($, Drupal, dataLayer) {
  // Trigger events when Algolia finishes loading wishlist results.
  Drupal.algoliaReactWishlist = Drupal.algoliaReactWishlist || {};
  Drupal.algoliaReactWishlist.triggerResultsUpdatedEvent = function (results) {
    $('#my-wishlist').trigger('wishlist-results-updated', [results]);
  };

  /**
   * Function to push product addToWishlist event to data layer.
   *
   * @param {object} product
   *   The jQuery HTML object containing GTM attributes for the product.
   */
  Drupal.alshayaSeoGtmPushAddToWishlist = function (product) {
    // Remove product position: Not needed while adding to cart.
    delete product.position;

    // Calculate metric 1 value.
    product.metric2 = product.price * product.quantity;

    const cart = (typeof Drupal.alshayaSpc !== 'undefined') ? Drupal.alshayaSpc.getCartData() : null;
    const sku = product.id;
    let productSelector = document.querySelectorAll(`[data-sku="${sku}"]`);
    if ((typeof productSelector[0] === 'undefined')) {
      productSelector = document.querySelectorAll(`[data-sku="${product.variant}"]`);
    }
    const isProductDataAvailable = (typeof productSelector[0] !== 'undefined');
    const gtmCategory = product.category;
    const categoryArray = gtmCategory ? gtmCategory.split('/') : [];

    const data = {
      event: 'add_to_wishlist',
      cart_id: (cart !== null) ? cart.cart_id : null,
      cart_items_count: (cart !== null) ? cart.items_qty : null,
      country: drupalSettings.gtm.country,
      customer_type: drupalSettings.userDetails.customerType,
      currency: drupalSettings.gtm.currency,
      department_id: drupalSettings.dataLayerContent.departmentId ||
        isProductDataAvailable ? productSelector[0].getAttribute('gtm-department-id') : null,
      department_name: drupalSettings.dataLayerContent.departmentName ||
        isProductDataAvailable ? productSelector[0].getAttribute('gtm-department-name') : null,
      magento_product_id: isProductDataAvailable ? productSelector[0].getAttribute('gtm-magento-product-id') : null,
      language: drupalSettings.gtm.language,
      page_type: drupalSettings.dataLayerContent.pageType,
      product_images_count: product.dimension4,
      product_old_price: isProductDataAvailable ? productSelector[0].getAttribute('gtm-old-price') : null,
      product_price: product.price,
      product_style_code: sku,
      stock_status: drupalSettings.dataLayerContent.stockStatus ||
        isProductDataAvailable ? productSelector[0].getAttribute('gtm-stock') : null,
      user_id: drupalSettings.userDetails.customerId,
      user_type: drupalSettings.userDetails.customerType,
      dimension2: product.dimension2 || null,
      dimension3: product.dimension3 || null,
      ecommerce: {
        items: {
          currency: drupalSettings.gtm.currency,
          index: isProductDataAvailable ? productSelector[0].getAttribute('data-insights-position') : 0,
          item_brand: product.brand,
          item_cateogory: gtmCategory,
          item_id: sku,
          item_name: product.name,
          item_variant: product.variant,
          price: product.price,
        },
      },
    };

    // Item Category
    for (let i = 0; i < categoryArray.length; i++) {
      data.ecommerce.items[`item_cateogory${i+2}`] = categoryArray[i];
    }
    dataLayer.push(data);
  };

  /**
   * Function to push product removeFromWishlist event to data layer.
   *
   * @param {object} product
   *   The jQuery HTML object containing GTM attributes for the product.
   */
  Drupal.alshayaSeoGtmPushRemoveFromWishlist = function (product) {
    // Remove product position: Not needed while removing from cart.
    delete product.position;

    // Calculate metric 1 value.
    product.metric2 = -1 * product.quantity * product.price;

    const productData = {
      event: 'removeFromWishlist',
      ecommerce: {
        currencyCode: drupalSettings.gtm.currency,
        wishlist: {
          products: [
            product,
          ],
        },
      },
    };

    dataLayer.push(productData);
  };
}(jQuery, Drupal, dataLayer));
