import { callMiddlewareApi, isAnonymousUserWithoutCart } from './common';

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
 */
window.commerceBackend.getCart = () => callMiddlewareApi('cart/get', 'GET');

/**
 * Calls the cart restore API.
 */
window.commerceBackend.restoreCart = () => callMiddlewareApi('cart/restore', 'GET');

/**
 * Calls the cart update API.
 *
 * @param {object} data
 *   The data object to send in the API call.
 */
window.commerceBackend.updateCart = (data) => callMiddlewareApi('cart/update', 'POST', JSON.stringify(data));
