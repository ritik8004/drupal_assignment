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
    cartItemsStock: {},
  };

  /**
   * Checks if current user is authenticated or not.
   *
   * @returns {bool}
   *   True if user is authenticated, else false.
   */
  function isUserAuthenticated() {
    return Boolean(window.drupalSettings.userDetails.customerId);
  }

  /**
   * Gets the stock data for cart items.
   *
   * @param {string} sku
   *   SKU value for which stock is to be returned.
   *
   * @returns {Promise}
   *   Returns a promise so that await executes on the calling function.
   */
  window.commerceBackend.loadProductStockDataFromCart = async function loadProductStockDataFromCart(sku) {
    // Load the stock data.
    var cartId = window.commerceBackend.getCartId();
    if (Drupal.hasValue(staticDataStore.cartItemsStock[sku])) {
      return staticDataStore.cartItemsStock[sku];
    }
    var isAuthUser = isUserAuthenticated();
    var authenticationToken = isAuthUser
      ? 'Bearer ' + window.drupalSettings.userDetails.customerToken
      : null;

    return rcsPhCommerceBackend.getData('cart_items_stock', { cartId }, null, null, null, false, authenticationToken).then(function processStock(response) {
      var cartKey = isAuthUser ? 'customerCart' : 'cart';
      // Do not proceed if for some reason there are no cart items.
      if (!response[cartKey].items.length) {
        return;
      }
      response[cartKey].items.forEach(function eachCartItem(cartItem) {
        if (!Drupal.hasValue(cartItem)) {
          return;
        }
        var stockData = null;
        if (cartItem.product.type_id === 'configurable') {
          stockData = cartItem.configured_variant.stock_data;
          stockData.status = cartItem.configured_variant.stock_status;
          staticDataStore.cartItemsStock[cartItem.configured_variant.sku] = stockData;
        }
        else if (cartItem.product.type_id === 'simple') {
          stockData = cartItem.product.stock_data;
          stockData.status = cartItem.product.stock_status;
          staticDataStore.cartItemsStock[cartItem.product.sku] = stockData;
        }
      });

      return staticDataStore.cartItemsStock[sku];
    });
  }

})(Drupal, drupalSettings);
