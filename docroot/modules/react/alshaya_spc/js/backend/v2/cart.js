import {
  callMagentoApi,
  isAnonymousUserWithoutCart,
  updateCart,
  getProcessedCartData,
} from './common';
import { logger } from './utility';
import { getDefaultErrorMessage } from './error';
import { removeStorageInfo } from '../../utilities/storage';

window.commerceBackend = window.commerceBackend || {};

/**
 * Object to serve as static cache for cart data over the course of a request.
 */
let staticCartData = null;

/**
 * Gets the cart data.
 *
 * @returns {object|null}
 *   Processed cart data else null.
 */
window.commerceBackend.getCartDataFromStorage = () => staticCartData;

/**
 * Gets the cart data.
 *
 * @returns {object|null}
 *   Processed cart data else null.
 */
window.commerceBackend.getProcessedCartData = (data) => getProcessedCartData(data);

/**
 * Sets the cart data to storage.
 *
 * @param data
 *   The cart data.
 */
window.commerceBackend.setCartDataInStorage = (data) => {
  const cartInfo = { ...data };
  cartInfo.last_update = new Date().getTime();
  staticCartData = cartInfo;
};

/**
 * Unsets the stored cart data.
 */
window.commerceBackend.removeCartDataFromStorage = () => {
  staticCartData = null;
};

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
window.commerceBackend.getCart = async () => {
  const cartId = window.commerceBackend.getCartId();
  if (cartId === null) {
    return new Promise((resolve) => resolve(cartId));
  }

  const response = await callMagentoApi(`/rest/V1/guest-carts/${cartId}/getCart`, 'GET', {});

  if (typeof response.data.error !== 'undefined' && response.data.error === true) {
    if (response.data.error_code === 404 || (typeof response.data.message !== 'undefined' && response.data.error_message.indexOf('No such entity with cartId') > -1)) {
      // Remove the cart from storage.
      removeStorageInfo('cart_id');
      logger.critical(`getCart() returned error ${response.data.error_code}. Removed cart from local storage`);
      // Get new cart.
      window.commerceBackend.getCartId();
    }

    const error = {
      data: {
        error: response.data.error,
        error_code: response.data.error_code,
        error_message: getDefaultErrorMessage(),
      },
    };
    return new Promise((resolve) => resolve(error));
  }

  // Process data.
  response.data = window.commerceBackend.getProcessedCartData(response.data);
  return new Promise((resolve) => resolve(response));
};

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
 * Calls the cart restore API.
 * @todo Implement restoreCart()
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.restoreCart = () => window.commerceBackend.getCart();

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

/**
 * Gets the cart ID for existing cart.
 *
 * @returns {string}
 *   The cart id.
 */
window.commerceBackend.getCartId = () => {
  const cartId = localStorage.getItem('cart_id');
  if (typeof cartId === 'string' || typeof cartId === 'number') {
    return cartId;
  }
  return null;
};

/**
 * Creates a new cart and stores cart Id in the local storage.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.createCart = async () => {
  const response = await callMagentoApi('/rest/V1/guest-carts', 'POST', {});
  localStorage.setItem('cart_id', response.data);
  return response.data;
};
