import {
  isAnonymousUserWithoutCart,
  updateCart,
  getProcessedCartData,
  checkoutComUpapiVaultMethod,
  checkoutComVaultMethod,
  callDrupalApi,
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
 * Get data related to product status.
 *
 * @param {string} sku
 *  The sku for which the status is required.
 */
const getProductStatus = async (sku) => {
  if (typeof sku === 'undefined' || !sku) {
    return new Promise((resolve) => resolve(null));
  }

  // Bypass CloudFlare to get fresh stock data.
  // Rules are added in CF to disable caching for urls having the following
  // query string.
  // The query string is added since same APIs are used by MAPP also.
  return callDrupalApi(`/rest/v1/product-status/${btoa(sku)}`, 'GET', null, { query: { _cf_cache_bypass: '1' } });
};

/**
 * Get CnC status for cart based on skus in cart.
 */
const getCncStatusForCart = async () => {
  const cart = window.commerceBackend.getCartDataFromStorage();
  if (!cart || typeof cart === 'undefined') {
    return null;
  }

  for (let i = 0; i < cart.cart.items.length; i++) {
    const item = cart.cart.items[i];
    // We should ideally have ony one call to an endpoint and pass
    // The list of items. This look could happen in the backend.
    // Suppressing the lint error for now.
    // eslint-disable-next-line no-await-in-loop
    const productStatus = await getProductStatus(item.sku);
    if (typeof productStatus.cnc_enabled !== 'undefined' && !productStatus.cnc_enabled) {
      return false;
    }
  }
  return true;
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
 * Format address structure for shipping estimates api.
 *
 * @param {object} $address
 *   Address object.
 * @return {object}.
 *   Formatted address object.
 */
const formatShippingEstimatesAddress = (address) => {
  const data = {};
  data.firstname = (typeof address.firstname !== 'undefined') ? address.firstname : '';
  data.lastname = (typeof address.lastname !== 'undefined') ? address.lastname : '';
  data.email = (typeof address.email !== 'undefined') ? address.email : '';
  data.country_id = (typeof address.country_id !== 'undefined') ? address.country_id : '';
  data.city = (typeof address.city !== 'undefined') ? address.city : '';
  data.telephone = (typeof address.telephone !== 'undefined') ? address.telephone : '';

  data.custom_attributes = [];
  if (typeof address.custom_attributes !== 'undefined' && address.custom_attributes.length > 0) {
    data.custom_attributes = address.custom_attributes.map((item) => {
      if (typeof item.value !== 'undefined' && item.value !== '') {
        return {
          attribute_code: item.attribute_code,
          value: item.value,
        };
      }
      return null;
    }).filter((item) => (item !== null));
  }

  // If custom attributes not available, we check for extension attributes.
  if (data.custom_attributes.length === 0 && typeof address.extension_attributes !== 'undefined' && Object.keys(address.extension_attributes).length > 0) {
    Object.keys(address.extension_attributes).forEach((key) => {
      data.custom_attributes.push(
        {
          attribute_code: key,
          value: address.extension_attributes[key],
        },
      );
    });
  }
  return data;
};

/**
 * Gets shipping methods.
 *
 * @param {object} shipping
 *   The shipping data.
 * @return {object}.
 *   The data.
 */
const getHomeDeliveryShippingMethods = (shipping) => {
  // @todo implement this
  // Call formatShippingEstimatesAddress() to be able to perform unit tests.
  // This function will be continued on ticket #30724.
  formatShippingEstimatesAddress(shipping.address);
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
const getStoreInfo = (storeCode) => callDrupalApi(`/cnc/store/${storeCode}`, 'GET', {});

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
  logger.info(`Address ${address}`);
  return address;
};

/**
 * Get Method Code.
 *
 * @param {array} code
 *   Payment Method code.
 * @return {string}.
 *   Payment Method code used.
 */
const getMethodCodeForFrontend = (code) => {
  let method = code;
  switch (method) {
    case checkoutComUpapiVaultMethod():
      method = 'checkout_com_upapi';
      break;

    case checkoutComVaultMethod():
      method = 'checkout_com';
      break;

    default:
      logger.info(`Invalid Method code: ${method}`);
      break;
  }

  return method;
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
 * @returns {Promise}
 *   A promise object.
 */
const getProcessedCheckoutData = async (cartData) => {
  let data = cartData;
  if (typeof data.error !== 'undefined' && data.error === true) {
    return data;
  }

  // Check whether CnC enabled or not.
  const cncStatus = await getCncStatusForCart();

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
    response.shipping.storeInfo = await getStoreInfo(response.shipping.storeCode);
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
        data: getProcessedCheckoutData(response),
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

export default getProcessedCheckoutData;
