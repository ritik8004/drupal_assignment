import {
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
 * @todo implement getCartForCheckout()
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.getCartForCheckout = () => {
  throw new Error('getCartForCheckout Not implemented!');
};

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
