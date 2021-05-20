import { callMiddlewareApi, isAnonymousUserWithoutCart } from './common';

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
