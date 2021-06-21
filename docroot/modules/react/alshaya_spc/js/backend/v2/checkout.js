import _ from 'lodash';
import {
  isAnonymousUserWithoutCart,
  getFormattedError,
  checkoutComUpapiVaultMethod,
  checkoutComVaultMethod,
  callDrupalApi,
  callMagentoApi,
} from './common';
import { getCart, updateCart, getProcessedCartData } from './cart';
import { getDefaultErrorMessage } from './error';
import { logger } from './utility';
import { cartActions } from './cart_actions';

window.commerceBackend = window.commerceBackend || {};

/**
 * Check if user is anonymous and without cart.
 *
 * @returns bool
 */
window.commerceBackend.isAnonymousUserWithoutCart = () => isAnonymousUserWithoutCart();

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
  return callDrupalApi(`/rest/v1/product-status/${btoa(sku)}`, 'GET', { _cf_cache_bypass: '1' });
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
 * @todo implement this
 *
 * @param {object} shipping
 *   The shipping data.
 * @return {object}.
 *   The data.
 */
const getHomeDeliveryShippingMethods = (shipping) => {
  formatShippingEstimatesAddress(shipping.address);
};

/**
 * Get default address from customer addresses.
 * @todo implement this
 *
 * @param {object} data
 *   Cart data.
 *
 * @return {object|null}
 *   Address if found.
 */
const getDefaultAddress = (data) => {
  logger.info(`${data}`);
};

/**
 * Format the address array.
 *
 * Format the address array so that it can be used to update billing or
 * shipping address in the cart.
 *
 * @param {object} address
 *   Address array.
 * @return {object}.
 *   Formatted address object.
 */
const formatAddressForShippingBilling = (address) => {
  const data = { ...address };

  const staticFields = {};
  if (typeof data.static !== 'undefined') {
    Object.keys(data.static).forEach((key) => {
      staticFields[key] = data.static[key];
    });
    delete data.static;
  }

  if (typeof data.carrier_info !== 'undefined') {
    delete data.carrier_info;
  }

  data.customAttributes = [];
  Object.keys(data).forEach((key) => {
    let value = data[key];
    if (!Array.isArray(value) && value === null) {
      value = '';
    }
    data.customAttributes.push(
      {
        attributeCode: key,
        value,
      },
    );
  });

  if (typeof data.street === 'string') {
    data.street = [data.street];
  }

  return {
    ...staticFields,
    ...data,
  };
};

/**
 * Adding shipping on the cart.
 * @todo implement this
 *
 * @param {object} shippingData
 *   Shipping address info.
 * @param {string} action
 *   Action to perform.
 * @param {bool} updateBilling
 *   Whether billing needs to be updated or not.
 *
 * @return {object}
 *   Cart data.
 */
const addShippingInfo = (shippingData, action, updateBilling) => {
  logger.info(`${shippingData}${action}${updateBilling}`);
  formatAddressForShippingBilling({});
};

/**
 * Update billing info on cart.
 *
 * @param {object} billing
 *   Billing data.
 *
 * @return {object}
 *   Response data.
 */
const updateBilling = (billing) => {
  const data = {
    extension: {
      action: cartActions.cartBillingUpdate,
    },
    billing,
  };
  delete data.billing.id;

  return updateCart(data);
};

/**
 * Validate area/city of address.
 *
 * @param {object} address
 *   Address object.
 *
 * @return {promise}
 *   Address validation response.
 */
const validateAddressAreaCity = async (address) => callDrupalApi('/spc/validate-info', 'POST', address);

/**
 * Select HD address and method from possible defaults.
 * @todo implement this
 *
 * @param {object} address
 *   Address object.
 * @param {object} method
 *   Payment method.
 * @param {objecty} billing
 *   Billing address.
 * @param {object} shippingMethods
 *   Shipping methods.
 *
 * @return {object|bool}
 *   FALSE if something went wrong, updated cart data otherwise.
 */
const selectHd = (address, method, billing, shippingMethods) => {
  addShippingInfo({}, '', false);
  updateBilling({});
  validateAddressAreaCity({});
  logger.info(`${address}${method}${billing}${shippingMethods}`);
};

/**
 * Get customer's addresses.
 *
 * @return {promise}
 *   Address ids of customer or empty array.
 */
const getCustomerAddressIds = () => callMagentoApi('/rest/V1/customers/me', 'GET', {});

/**
 * Select Click and Collect store and method from possible defaults.
 *
 * @param {object} store
 *   Store info.
 * @param {object} address
 *   Shipping address from last order.
 * @param {object} billing
 *   Billing address.
 *
 * @return {promise}
 *   Updated cart.
 */
const selectCnc = async (store, address, billing) => {
  const data = {
    extension: {
      action: cartActions.cartShippingUpdate,
    },
    shipping: {
      shipping_address: address,
      shipping_carrier_code: 'click_and_collect',
      shipping_method_code: 'click_and_collect',
      custom_attributes: [],
      extension_attributes: {
        click_and_collect_type: (!_.isEmpty(store.rnc_available)) ? 'reserve_and_collect' : 'ship_to_store',
        store_code: store.code,
      },
    },
  };

  const extensionAttributes = data.shipping.shipping_address.extension_attributes;
  Object.keys(extensionAttributes).forEach((key) => {
    data.shipping.shipping_address.custom_attributes.push(
      {
        attributeCode: key,
        value: extensionAttributes[key],
      },
    );
  });

  // Validate address.
  const valid = await validateAddressAreaCity(billing);
  if (!valid || valid.address === false) {
    return new Promise((resolve) => resolve(false));
  }

  const logData = JSON.stringify(data);
  const logAddress = JSON.stringify(address);
  const logStore = JSON.stringify(store);
  const logCartId = window.commerceBackend.getCartId();
  logger.notice(`Shipping update default for CNC. Data: ${logData} Address: ${logAddress} Store: ${logStore} Cart: ${logCartId}`);

  // If shipping address not contains proper data (extension info).
  if (_.isEmpty(data.shipping.shipping_address.extension_attributes)) {
    return new Promise((resolve) => resolve(false));
  }

  const updated = updateCart(data);
  if (_.has(updated, 'error') && updated.error) {
    return new Promise((resolve) => resolve(false));
  }

  // Not use/assign default billing address if customer_address_id
  // is not available.
  if (_.isEmpty(billing.customer_address_id)) {
    return new Promise((resolve) => resolve(updated));
  }

  // Add log for billing data we pass to magento update cart.
  const logBilling = JSON.stringify(billing);
  logger.notice(`Billing update default for CNC. Address: ${logBilling} Cart: ${logCartId}`);

  // If billing address not contains proper data (extension info).
  if (typeof billing.extension_attributes === 'undefined') {
    return new Promise((resolve) => resolve(false));
  }

  const customerAddressIds = getCustomerAddressIds(billing.customer_id);

  // Return if address id from last order doesn't
  // exist in customer's address id list.
  if (_.findIndex(customerAddressIds, { id: billing.customer_address_id }) !== -1) {
    return new Promise((resolve) => resolve(updated));
  }

  const updatedBilling = updateBilling(billing);

  // If billing update has error.
  if (_.has(updated, 'error') && updated.error) {
    return new Promise((resolve) => resolve(false));
  }
  return new Promise((resolve) => resolve(updatedBilling));
};

/**
 * Apply shipping from last order.
 * @todo implement this
 *
 * @param {object} order
 *   Last Order details.
 *
 * @return {object|bool}
 *   FALSE if something went wrong, updated cart data otherwise.
 */
const applyDefaultShipping = (order) => {
  logger.info(`${order}`);
  selectCnc({}, {}, {});
};

const applyDefaults = (data, uid) => {
  /**
   * Apply defaults to cart for customer.
   * @todo implement this
   *
   * @param {object} cartData
   *   The cart data object.
   * @param {integer} uid
   *   Drupal User ID.
   * @return {object}.
   *   The data.
   */
  logger.info(`${data}${uid}`);
  getHomeDeliveryShippingMethods({});
  getDefaultAddress({});
  applyDefaultShipping({});
  selectHd({}, {}, {}, {});
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
 * Gets the data for a particular store.
 *
 * @param {string} store
 *   The store ID.
 *
 * @returns {Promise}
 *   Returns a promise which resolves to an array of data for the given store or
 * an empty array in case of any issue.
 */
const getStoreInfo = async (storeData) => {
  let store = { ...storeData };

  if (typeof store.code === 'undefined' || !store.code) {
    return null;
  }

  // Fetch store info from Drupal.
  let storeInfo = await callDrupalApi(`/cnc/store/${store.code}`, 'GET', {});
  storeInfo = storeInfo.data;

  if (!storeInfo || (!Array.isArray(storeInfo) && storeInfo.length === 0)) {
    return null;
  }

  // Get the complete data about the store by combining the received data from
  // Magento with the processed store data stored in Drupal.
  store = Object.assign(store, storeInfo);
  store.formatted_distance = store.distance
    .toLocaleString('us', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    .replace(/,/g, '');
  store.formatted_distance = parseFloat(store.formatted_distance);

  store.delivery_time = store.sts_delivery_time_label;
  if (typeof store.rnc_available !== 'undefined'
    && store.rnc_available
    && typeof store.rnc_config !== 'undefined') {
    store.delivery_time = store.rnc_config;
  }
  // If rnc is available the the value of rnc_config is already fetched above.
  // Or rnc is not available at all. So in both cases, we do not need the value
  // of rnc_config anymore and so we remove it.
  if (typeof store.rnc_config !== 'undefined') {
    delete store.rnc_config;
  }
  return store;
};

/**
 * Get the list of stores for the cart.
 *
 * @param {string} lat
 *   The latitude value.
 * @param {string} lon
 *   The longitude value.
 */
const getCartStores = async (lat, lon) => {
  const cartId = window.commerceBackend.getCartId();
  let stores = [];

  stores = await callMagentoApi(`/rest/V1/click-and-collect/stores/guest-cart/${cartId}/lat/${lat}/lon/${lon}`);

  if (typeof stores.data.error !== 'undefined' && stores.data.error) {
    logger.notice(`Error occurred while fetching stores for cart id ${cartId}, API Response: ${stores.data.error_message}`);
    return [];
  }

  stores = stores.data;

  if (!stores || (Array.isArray(stores) && stores.length === 0)) {
    return [];
  }

  const storeInfoPromises = [];
  stores.forEach((store) => {
    storeInfoPromises.push(getStoreInfo(store));
  });

  try {
    stores = await Promise.all(storeInfoPromises);
    // Sort the stores first by distance and then by name.
    stores = stores
      .sort((store1, store2) => store2.rnc_available - store1.rnc_available)
      .sort((store1, store2) => store1.distance - store2.distance);

    return stores;
  } catch (error) {
    logger.notice(`Error occurred while fetching stores for cart id ${cartId}, API Response: ${error.message}`);
  }

  return [];
};

/**
 *  Get the CnC stores for the cart.
 *
 * @param {string} lat
 *   The latitude value.
 * @param {string} lon
 *   The longiture value.
 */
const getCncStores = async (lat, lon) => {
  const cartId = window.commerceBackend.getCartId();
  if (!cartId) {
    logger.error('Error while fetching click and collect stores. No cart available in session');
    return getFormattedError(404, 'No cart in session');
  }

  if (!lat || !lon) {
    logger.error(`Error while fetching CnC store for cart ${cartId}. One of lat/lon is not provided. Lat = ${lat}, Lon = ${lon}.`);
    return [];
  }

  const stores = await getCartStores(lat, lon);

  return stores;
};

/**
 * Helper function to format address as required by frontend.
 *
 * @param {object} address
 *   Address array.
 * @return {object|null}.
 *   Formatted address if available.
 */
const formatAddressForFrontend = (address) => {
  // Do not consider addresses without custom attributes as they are required
  // for Delivery Matrix.
  if (Object.keys(address).length === 0 || typeof address.country_id === 'undefined' || address.country_id === '') {
    return null;
  }

  const result = { ...address };
  if (typeof address.custom_attributes !== 'undefined' && Object.keys(address.custom_attributes).length > 0) {
    Object.keys(address.custom_attributes).forEach((item) => {
      const key = address.custom_attributes[item].attribute_code;
      const val = address.custom_attributes[item].value;
      result[key] = val;
    });
  }
  delete result.custom_attributes;
  return result;
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
  const response = getProcessedCartData(data);
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

  getCart()
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

      const processedData = getProcessedCheckoutData(response);

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

export {
  getProcessedCheckoutData,
  getCncStores,
};
