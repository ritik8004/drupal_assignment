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
window.commerceBackend.addToCart = (data) => updateCart(data);

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
 * Adds/Removes/Changes quantity of items in the cart and returns the cart.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.updateCartItemData = (data) => updateCart(data);
