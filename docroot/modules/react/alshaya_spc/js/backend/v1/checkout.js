import {
  callMiddlewareApi,
  isAnonymousUserWithoutCart,
  updateCart,
} from './common';

window.commerceBackend = window.commerceBackend || {};

/**
 * Check if user is anonymous and without cart.
 *
 * @returns bool
 */
window.commerceBackend.isAnonymousUserWithoutCart = () => isAnonymousUserWithoutCart();

/**
 * Get cart data for checkout.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.getCartForCheckout = () => callMiddlewareApi('cart/checkout', 'GET');

/**
 * Adds payment method in the cart and returns the cart.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.addPaymentMethod = (data) => updateCart(data);

/**
 * Fetches the list of click and collect stores.
 *
 * @param {object} coords
 *   The co-ordinates data.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.fetchClickNCollectStores = (coords) => callMiddlewareApi(`cart/stores/${coords.lat}/${coords.lng}`, 'GET');

/**
 * Places an order.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.placeOrder = (data) => callMiddlewareApi('cart/place-order', 'POST', JSON.stringify(data));

/**
 * Adds shipping method to the cart and returns the cart.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.addShippingMethod = (data) => updateCart(data);

/**
 * Adds billing method to the cart and returns the cart.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.addBillingMethod = (data) => updateCart(data);
