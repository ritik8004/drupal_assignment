import {
  isAnonymousUserWithoutCart,
  updateCart,
  getProcessedCartData,
} from './common';
import { getDefaultErrorMessage } from './error';
import { logger } from './utility';

window.commerceBackend = window.commerceBackend || {};

/**
 * Check if user is anonymous and without cart.
 *
 * @returns bool
 */
window.commerceBackend.isAnonymousUserWithoutCart = () => isAnonymousUserWithoutCart();

/**
 * Transforms cart data to match the data structure from middleware.
 *
 * @param {object} cartData
 *   The cart data object.
 */
window.commerceBackend.getProcessedCartData = (data) => getProcessedCartData(data);

/**
 * Checks whether cnc enabled or not on cart.
 *
 * @param {object} cartData
 *   The cart data object.
 * @return {bool}.
 *   CnC enabled or not for cart.
 */
const getCncStatusForCart = (data) => {
  // @todo implement this
  logger.info(`${data}`);
};

/**
 * Apply defaults to cart for customer.
 *
 * @param {object} cartData
 *   The cart data object.
 * @param {integer} uid
 *   Drupal User ID.
 * @return {object}.
 *   The data.
 */
const applyDefaults = (data, uid) => {
  // @todo implement this
  logger.info(`${data}${uid}`);
};

/**
 * Gets shipping methods.
 *
 * @param {object} cartData
 *   The cart data object.
 * @return {object}.
 *   The data.
 */
const getHomeDeliveryShippingMethods = (data) => {
  // @todo implement this
  logger.info(`${data}`);
};

/**
 * Gets payment methods.
 *
 * @return {array}.
 *   The method list.
 */
const getPaymentMethods = () => {
  // @todo implement this
};

/**
 * Get the payment method set on cart.
 *
 * @return {string}.
 *   Payment method set on cart.
 */
const getPaymentMethodSetOnCart = () => {
  // @todo implement this
};

/**
 * Helper function to get clean customer data.
 *
 * @param {array} data
 *   Customer data.
 * @return {array}.
 *   Customer data.
 */
const getCustomerPublicData = (data) => {
  // @todo implement this
  logger.info(`${data}`);
};

/**
 * Get store info for given store code.
 *
 * @param {string} storeCode
 *   The store code.
 * @return {array}.
 *   Return store info.
 */
const getStoreInfo = (storeCode) => {
  // @todo implement this
  logger.info(`${storeCode}`);
};

/**
 * Get store info for given store code.
 *
 * @param {array} address
 *   Address array.
 * @return {array|null}.
 *   Formatted address if available.
 */
const formatAddressForFrontend = (address) => {
  // @todo implement this
  logger.info(`${address}`);
};

/**
 * Get Method Code.
 *
 * @param {array} method
 *   Payment Method code.
 * @return {string}.
 *   Payment Method code used.
 */
const getMethodCodeForFrontend = (method) => {
  // @todo implement this
  logger.info(`${method}`);
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

/**
 * Process cart data for checkout.
 *
 * @param {object} cartData
 *   The cart data object.
 * @return {object}.
 *   The data.
 */
window.commerceBackend.getProcessedCheckoutData = (cartData) => {
  let data = cartData;
  if (typeof data.error !== 'undefined' && data.error === true) {
    return data;
  }

  // Check whether CnC enabled or not.
  const cncStatus = getCncStatusForCart();

  // Here we will do the processing of cart to make it in required format.
  const updated = applyDefaults(data, window.drupalSettings.user.uid);
  if (updated !== false) {
    data = updated;
  }

  if (typeof data.shipping.methods === 'undefined' && typeof data.shipping.address !== 'undefined' && data.shipping.type !== 'click_and_collect') {
    const shippingMethods = getHomeDeliveryShippingMethods(data.shipping);
    if (typeof shippingMethods.error !== 'undefined') {
      return shippingMethods;
    }
    data.shipping.methods = shippingMethods;
  }

  if (typeof data.payment.methods === 'undefined' && typeof data.payment.method !== 'undefined') {
    const paymentMethods = getPaymentMethods();
    if (typeof paymentMethods !== 'undefined') {
      data.payment.methods = paymentMethods;
      data.payment.method = getPaymentMethodSetOnCart();
    }
  }

  // Re-use the processing done for cart page.
  const response = window.commerceBackend.getProcessedCartData(data);
  response.cnc_enabled = cncStatus;
  response.customer = getCustomerPublicData(data.customer);
  response.shipping = (typeof data.shipping !== 'undefined')
    ? data.shipping
    : [];

  if (typeof response.shipping.storeCode !== 'undefined') {
    response.shipping.storeInfo = getStoreInfo(response.shipping.storeCode);
    // Set the CnC type (rnc or sts) if not already set.
    if (typeof response.shipping.storeInfo.rnc_available === 'undefined' && typeof response.shipping.clickCollectType !== 'undefined') {
      response.shipping.storeInfo.rnc_available = (response.shipping.clickCollectType === 'reserve_and_collect');
    }
  }

  response.payment = (typeof data.payment !== 'undefined')
    ? data.payment
    : [];

  // Set method to null if empty to reduce the number of conditions in JS.
  response.shipping.method = (typeof data.shipping.method !== 'undefined')
    ? data.shipping.method
    : null;

  // Format addresses.
  response.shipping.address = formatAddressForFrontend(response.shipping.address);
  response.billing_address = formatAddressForFrontend(data.cart.billing_address);

  // If payment method is not available in the list, we set the first
  // available payment method.
  if (typeof response.payment !== 'undefined') {
    const codes = response.payment.methods.map((el) => el.code);
    if (typeof response.payment.method !== 'undefined' && typeof codes[response.payment.method] === 'undefined') {
      delete (response.payment.method);
    }

    // If default also has invalid payment method, we remove it
    // so that first available payment method will be selected.
    if (typeof response.payment.default !== 'undefined' && typeof codes[response.payment.default] === 'undefined') {
      delete (response.payment.default);
    }

    if (typeof response.payment.method !== 'undefined') {
      response.payment.method = getMethodCodeForFrontend(response.payment.method);
    }

    if (typeof response.payment.default !== 'undefined') {
      response.payment.default = getMethodCodeForFrontend(response.payment.default);
    }
  }
  return response;
};

/**
 * Get cart data for checkout.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.getCartForCheckout = () => {
  const cartId = window.commerceBackend.getCartId();
  if (cartId === null) {
    return new Promise((resolve) => resolve({ error: true }));
  }

  window.commerceBackend.getCart()
    .then((response) => {
      if (typeof response.data === 'undefined' || response.data.length === 0) {
        if (typeof response.data.error_message !== 'undefined') {
          logger.error(`Error while getting cart:${cartId} Error:${response.data.error_message}`);
        }
      }

      if (typeof response.data.items === 'undefined' || response.data.items.length === 0) {
        logger.error(`Checkout accessed without items in cart for id:${cartId}`);

        const error = {
          data: {
            error: true,
            error_code: 500,
            error_message: 'Checkout accessed without items in cart',
          },
        };

        return new Promise((resolve) => resolve(error));
      }

      const processedData = {
        data: window.commerceBackend.getProcessedCheckoutData(response),
      };
      return new Promise((resolve) => resolve(processedData));
    })
    .catch((response) => {
      logger.error(`Error while getCartForCheckout controller. Error: ${response.message}. Code: ${response.status}`);

      const error = {
        data: {
          error: true,
          error_code: response.status,
          error_message: getDefaultErrorMessage(),
        },
      };
      return new Promise((resolve) => resolve(error));
    });
  return null;
};
