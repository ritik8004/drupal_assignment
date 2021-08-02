import {
  callMiddlewareApi,
  isAnonymousUserWithoutCart,
  updateCart,
  returnExistingCartWithError,
} from './common';
import getStringMessage from '../../utilities/strings';

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
window.commerceBackend.placeOrder = (data) => callMiddlewareApi('cart/place-order', 'POST', JSON.stringify(data)).then(
  (response) => {
    // Check if we have tried to call middleware when the commerce backend is set to V2.
    if (
      typeof response.data.error !== 'undefined'
      && response.data.error_code === 612
    ) {
      if (!sessionStorage.getItem('reloadOnBackendSwitch')) {
        // Reload the page only once. The caches are expected to be cleared till then.
        sessionStorage.setItem('reloadOnBackendSwitch', 1);
        window.location.reload();
      }
      return returnExistingCartWithError(
        response.status,
        getStringMessage('backend_obsolete_version'),
      );
    }

    return response;
  },
);

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

/**
 * Places an order.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.saveApplePayPayment = (data) => callMiddlewareApi('payment/checkout-com-apple-pay/save', 'POST', JSON.stringify(data));
