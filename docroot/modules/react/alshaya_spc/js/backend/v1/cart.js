import {
  callMiddlewareApi,
  isAnonymousUserWithoutCart,
  updateCart,
} from './common';

drupalSettings = window.drupalSettings;
window.commerceBackend = window.commerceBackend || {};

/**
 * Check if user is anonymous and without cart.
 *
 * @returns bool
 */
window.commerceBackend.isAnonymousUserWithoutCart = () => isAnonymousUserWithoutCart();

/**
 * Calls the cart get API.
 *
 * @todo check why getCart in V1 and V2 are different
 * In V1 it does API call all the time.
 * In V2 it loads from static cache if available.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.getCart = () => callMiddlewareApi('cart/get', 'GET');

/**
 * Calls the cart restore API.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.restoreCart = () => callMiddlewareApi('cart/restore', 'GET');

/**
 * Adds item to the cart and returns the cart.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.addUpdateRemoveCartItem = (data) => updateCart(data);

/**
 * Applies/Removes promo code to the cart and returns the cart.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.applyRemovePromo = (data) => updateCart(data);

/**
 * Refreshes cart data and returns the cart.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.refreshCart = (data) => updateCart(data);

/**
 * Associates cart to the user.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.associateCart = () => callMiddlewareApi('cart/associate', 'GET');

/**
 * Adds free gift to the cart.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.addFreeGift = (data) => callMiddlewareApi('select-free-gift', 'POST', JSON.stringify(data));
