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

    var productData = {
      event: 'addToWishlist',
      ecommerce: {
        currencyCode: drupalSettings.gtm.currency,
        wishlist: {
          products: [
            product
          ]
        }
      }
    };

    dataLayer.push(productData);
  }

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

    var productData = {
      event: 'removeFromWishlist',
      ecommerce: {
        currencyCode: drupalSettings.gtm.currency,
        remove: {
          products: [
            product
          ]
        }
      }
    };

    dataLayer.push(productData);
  }
})(jQuery, Drupal, dataLayer);
