import _ from 'lodash';
import md5 from 'md5';
import {
  isAnonymousUserWithoutCart,
  getCart,
  updateCart,
  getFormattedError,
  getProcessedCartData,
  checkoutComUpapiVaultMethod,
  checkoutComVaultMethod,
  callDrupalApi,
  callMagentoApi,
  getCartCustomerEmail,
  getCartCustomerId,
  associateCartToCustomer,
} from './common';
import { getDefaultErrorMessage } from './error';
import {
  getApiEndpoint,
  isUserAuthenticated,
  logger,
} from './utility';
import cartActions from './cart_actions';

window.commerceBackend = window.commerceBackend || {};

/**
 * This variable is used to check if field values have invisible characters.
 *
 * @type {string}
 */
const invisibleCharacter = '&#8203;';

/**
 * Check if user is anonymous and without cart.
 *
 * @returns bool
 */
window.commerceBackend.isAnonymousUserWithoutCart = () => isAnonymousUserWithoutCart();

/**
 * Get data related to product status.
 *
 * @param {Promise<string|null>}
 *  The sku for which the status is required.
 */
const getProductStatus = async (sku) => {
  if (typeof sku === 'undefined' || !sku) {
    return null;
  }

  // Bypass CloudFlare to get fresh stock data.
  // Rules are added in CF to disable caching for urls having the following
  // query string.
  // The query string is added since same APIs are used by MAPP also.
  const response = await callDrupalApi(`/rest/v1/product-status/${btoa(sku)}/`, 'GET', { params: { _cf_cache_bypass: '1' } });
  if (!_.isUndefined(response.data)) {
    return response.data;
  }
  return null;
};

/**
 * Get CnC status for cart based on skus in cart.
 *
 * @returns {Promise<boolean>}.
 *    The CNC status.
 */
const getCncStatusForCart = async () => {
  const cart = window.commerceBackend.getRawCartDataFromStorage();
  if (typeof cart === 'undefined' || !cart) {
    return false;
  }

  for (let i = 0; i < cart.cart.items.length; i++) {
    const item = cart.cart.items[i];
    // We should ideally have ony one call to an endpoint and pass
    // The list of items. This look could happen in the backend.
    // Suppressing the lint error for now.
    // eslint-disable-next-line no-await-in-loop
    const productStatus = await getProductStatus(item.sku);
    if (!_.isEmpty(productStatus)
      && _.isBoolean(productStatus.cnc_enabled) && !productStatus.cnc_enabled) {
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

const staticShippingMethods = [];

/**
 * Gets shipping methods.
 *
 * @param data
 *   The shipping address.
 * @returns {Promise<array>}
 *   HD Shipping methods.
 */
const getHomeDeliveryShippingMethods = async (data) => {
  if (_.isEmpty(data.country_id)) {
    logger.error(`Error in getting shipping methods for HD as country id not available. Data: ${JSON.stringify(data)}`);
    return [];
  }

  // Prepare address data for api call.
  const formattedAddress = formatShippingEstimatesAddress(data);

  // Create a key for static store;
  const key = md5(JSON.stringify(formattedAddress));

  // Get shipping methods from static.
  if (!_.isEmpty(staticShippingMethods[key])) {
    return staticShippingMethods[key];
  }

  staticShippingMethods[key] = [];
  const url = getApiEndpoint('estimateShippingMethods', { cartId: window.commerceBackend.getCartId() });
  const response = await callMagentoApi(url, 'POST', { address: formattedAddress });
  if (!_.isEmpty(response.data)) {
    const methods = response.data;

    // Check for errors.
    if (!_.isUndefined(methods.error) && methods.error) {
      logger.error(`Error in getting shipping methods for HD. Data: ${methods.error_message}`);
      return methods;
    }

    // Delete CNC from methods.
    for (let i = 0; i < methods.length; i++) {
      if (methods[i].carrier_code === 'click_and_collect') {
        delete methods[i];
      }
    }

    // Set shipping methods in static.
    staticShippingMethods[key] = methods;
  }

  // Return methods.
  return staticShippingMethods[key];
};

/**
 * Get default address from customer addresses.
 *
 * @param {object} data
 *   Cart data.
 *
 * @return {object|null}
 *   Address if found.
 */
const getDefaultAddress = (data) => {
  if (_.isEmpty(data.customer) || _.isEmpty(data.customer.addresses)) {
    return null;
  }

  // If address is set as default for shipping.
  const key = _.findIndex(data.customer.addresses, (address) => address.default_shipping === '1');
  if (key >= 0) {
    return data.customer.addresses[key];
  }

  // Return first address.
  return _.first(data.customer.addresses);
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
  const data = _.cloneDeep(address);

  const staticFields = {};
  if (!_.isEmpty(data.static)) {
    Object.keys(data.static).forEach((key) => {
      staticFields[key] = data.static[key];
    });
    delete data.static;
  }

  if (!_.isEmpty(data.carrier_info)) {
    delete data.carrier_info;
  }

  const customAttributes = [];
  Object.keys(data).forEach((key) => {
    customAttributes.push(
      {
        attributeCode: key,
        value: (!Array.isArray(data[key]) && _.isNull(data[key])) ? '' : data[key],
      },
    );
  });
  data.customAttributes = customAttributes;

  if (_.isString(data.street)) {
    data.street = [data.street];
  }

  return {
    ...staticFields,
    ...data,
  };
};

/**
 * Update billing info on cart.
 *
 * @param {object} billingData
 *   Billing data.
 *
 * @returns {Promise<object>}
 *   Response data.
 */
const updateBilling = async (billingData) => {
  const params = {
    extension: {
      action: cartActions.cartBillingUpdate,
    },
    billing: formatAddressForShippingBilling(billingData),
  };

  if (!_.isUndefined(params.billing.id)) {
    delete params.billing.id;
  }

  const logAddress = JSON.stringify(params.billing);
  const logData = JSON.stringify(billingData);
  const cartId = window.commerceBackend.getCartId();
  logger.notice(`Billing update manual. Address: ${logAddress} Data: ${logData} Cart: ${cartId}`);
  logger.notice(`Billing update manual. Address: ${JSON.stringify(params.billing)} Data: ${JSON.stringify(billingData)} Cart: ${window.commerceBackend.getCartId()}`);

  return updateCart(params);
};

/**
 * Validate area/city of address.
 *
 * @param {object} address
 *   Address object.
 *
 * @return  {Promise<AxiosPromise<object>>}
 *   Address validation response.
 */
const validateAddressAreaCity = (address) => callDrupalApi('/spc/validate-info', 'POST', address);

/**
 * Get last order of the customer.
 * @todo implement this.
 */
const getLastOrder = () => [];

/**
 * Apply shipping from last order.
 * @todo implement this.
 *
 * @param {object} order
 *   Last Order details.
 *
 * @returns {Promise<object|boolean>}
 *   FALSE if something went wrong, updated cart data otherwise.
 */
const applyDefaultShipping = async () => false;

/**
 * Get payment method from last order.
 * @todo implement this
 *
 * @param {object} order
 *   Last Order details.
 *
 * @returns {Promise<object|boolean>}
 *   FALSE if something went wrong, payment method name otherwise.
 */
const getDefaultPaymentFromOrder = async () => null;

/**
 * Gets payment methods.
 *
 * @returns {Promise<object|null>}.
 *   The method list if available.
 */
const getPaymentMethods = async () => {
  const cartId = window.commerceBackend.getCartId();
  return getCart()
    .then(async (response) => {
      if (_.isEmpty(response.data)
        || _.isEmpty(response.data.shipping)
        || _.isEmpty(response.data.shipping.method)
        || (!_.isUndefined(response.data.error) && response.data.error)
      ) {
        logger.error(`Error while getting payment methods from MDC. Shipping method not available in cart with id: ${cartId}`);
        return null;
      }

      // Get payment methods from MDC.
      return callMagentoApi(getApiEndpoint('getPaymentMethods', { cartId }), 'GET', {})
        .then(async (paymentMethods) => {
          if (!_.isEmpty(response.data)) {
            return paymentMethods.data;
          }
          return null;
        });
    });
};

/**
 * Get the payment method set on cart.
 * @todo implement this
 *
 * @return {string}.
 *   Payment method set on cart.
 */
const getPaymentMethodSetOnCart = () => 'todo';

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
  const response = await callDrupalApi(`/cnc/store/${store.code}`, 'GET', {});
  if (_.isEmpty(response.data)
    || (!_.isUndefined(response.data.error) && response.data.error)
  ) {
    return null;
  }
  const storeInfo = response.data;

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
 *
 * @returns {array}
 *   The list of stores.
 */
const getCartStores = async (lat, lon) => {
  const cartId = window.commerceBackend.getCartId();
  let stores = [];

  const response = await callMagentoApi(`/rest/V1/click-and-collect/stores/guest-cart/${cartId}/lat/${lat}/lon/${lon}`);
  if (_.isEmpty(response.data)
    || (!_.isUndefined(response.data.error) && response.data.error)
  ) {
    logger.notice(`Error occurred while fetching stores for cart id ${cartId}, API Response: ${response.data.error_message}`);
    return response;
  }
  stores = response.data;
  if (!stores || (Array.isArray(stores) && stores.length === 0)) {
    return [];
  }

  const storeInfoPromises = [];
  stores.forEach((store) => {
    storeInfoPromises.push(getStoreInfo(store));
  });

  try {
    stores = await Promise.all(storeInfoPromises);

    // Remove null values.
    // Sort the stores first by distance and then by name.
    return stores
      .filter((value) => value != null)
      .sort((store1, store2) => store2.rnc_available - store1.rnc_available)
      .sort((store1, store2) => store1.distance - store2.distance);
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

  return getCartStores(lat, lon);
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
  if (_.isEmpty(address) || _.isEmpty(address.country_id)) {
    return null;
  }

  const result = { ...address };
  if (!_.isEmpty(address.custom_attributes)) {
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
 * Helper function to get clean customer data.
 *
 * @param {array} customer
 *   Customer data.
 * @return {object}.
 *   Cleared customer data.
 */
const getCustomerPublicData = (customer) => {
  if (_.isEmpty(customer)) {
    return {};
  }

  const data = {
    id: 0,
    firstname: '',
    lastname: '',
    email: '',
    addresses: [],
  };

  if (!_.isUndefined(customer.id)) {
    data.id = customer.id;
  }

  if (!_.isUndefined(customer.firstname) && customer.firstname !== invisibleCharacter) {
    data.firstname = customer.firstname;
  }

  if (!_.isUndefined(customer.lastname) && customer.lastname !== invisibleCharacter) {
    data.lastname = customer.lastname;
  }

  if (!_.isUndefined(customer.email)) {
    data.email = customer.email;
  }

  if (!_.isEmpty(customer.addresses)) {
    customer.addresses.forEach((item) => {
      data.addresses.push(formatAddressForFrontend(item));
    });
  }

  return data;
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
 * Adding shipping on the cart.
 *
 * @param {object} shippingData
 *   Shipping address info.
 * @param {string} action
 *   Action to perform.
 * @param {bool} updateBillingDetails
 *   Whether billing needs to be updated or not.
 *
 * @returns {Promise<object|null>}
 *   Cart data or null.
 */
const addShippingInfo = async (shippingData, action, updateBillingDetails) => {
  const params = {
    shipping: {},
    extension: {
      action,
    },
  };

  if (_.isEmpty(shippingData)) {
    return null;
  }

  // Add carrier info.
  if (!_.isEmpty(shippingData.carrier_info)) {
    params.shipping.shipping_carrier_code = shippingData.carrier_info.code;
    params.shipping.shipping_method_code = shippingData.carrier_info.method;
  }

  // Add customer address info.
  if (!_.isEmpty(shippingData.customer_address_id)) {
    params.shipping.shipping_address = shippingData.address;
  } else {
    params.shipping.shipping_address = formatAddressForShippingBilling(shippingData);
  }

  let cart = await updateCart(params);
  // If cart update has error.
  if (_.isEmpty(cart.data) || (!_.isUndefined(cart.data.error) && cart.data.error)) {
    return cart;
  }
  const cartData = cart.data;

  // If billing needs to updated or billing is not available added at all
  // in the cart. Assuming if name is not set in billing means billing is
  // not set. City with value 'NONE' means, that this was added in CnC
  // by default and not changed by user.
  if (updateBillingDetails
    || _.isEmpty(cartData.billing_address) || _.isEmpty(cartData.billing_address.firstname)
    || cartData.billing_address.city === 'NONE') {
    cart = await updateBilling(params.shipping.shipping_address);
  }

  return cart;
};

/**
 * Select HD address and method from possible defaults.
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
 * @returns {Promise<object|boolean>}
 *   FALSE if something went wrong, updated cart data otherwise.
 */
const selectHd = async (address, method, billing, shippingMethods) => {
  const cartId = window.commerceBackend.getCartId();
  const shippingData = {
    customer_address_id: null,
    address,
    carrier_info: {
      code: method.carrier_code,
      method: method.method_code,
    },
  };

  // Set customer address id.
  if (!_.isEmpty(address.id)) {
    shippingData.customer_address_id = address.id;
  } else if (!_.isEmpty(address.customer_address_id)) {
    shippingData.customer_address_id = address.customer_address_id;
  }

  // Validate address.
  const response = await validateAddressAreaCity(shippingData.address);
  if (_.isEmpty(response.data)
    || _.isEmpty(response.data.address)
    || (!_.isUndefined(response.data.error) && response.data.error)
  ) {
    return false;
  }

  // Add log for shipping data we pass to magento update cart.
  const logData = JSON.stringify(shippingData);
  logger.notice(`Shipping update default for HD. Data: ${logData} Cart: ${cartId}`);

  // If shipping address not contains proper address, don't process further.
  if (_.isEmpty(shippingData.address.extension_attributes)
    && _.isEmpty(shippingData.address.custom_attributes)
  ) {
    return false;
  }

  let updated = await addShippingInfo(shippingData, cartActions.cartShippingUpdate, false);
  if (_.isEmpty(updated.data) || (!_.isUndefined(updated.data.error) && updated.data.error)) {
    return false;
  }

  // Set shipping methods.
  if (!_.isEmpty(updated.data) && !_.isEmpty(updated.data.shipping)
    && !_.isEmpty(shippingMethods)) {
    updated.data.shipping.methods = shippingMethods;
  }

  // Not use/assign default billing address if customer_address_id
  // is not available.
  if (_.isEmpty(billing.customer_address_id)) {
    return updated;
  }

  // Add log for billing data we pass to magento update cart.
  const logAddress = JSON.stringify(billing);
  logger.notice(`Billing update default for HD. Address: ${logAddress} Cart: ${cartId}`);

  // If billing address not contains proper address, don't process further.
  if (_.isEmpty(billing.extension_attributes)
    && _.isEmpty(billing.custom_attributes)
  ) {
    return updated;
  }

  updated = await updateBilling(billing);
  if (_.isEmpty(updated.data) || (!_.isUndefined(updated.data.error) && updated.data.error)) {
    return false;
  }

  // Set shipping methods.
  if (!_.isEmpty(updated.data) && !_.isEmpty(updated.data.shipping)
    && !_.isEmpty(shippingMethods)) {
    updated.data.shipping.methods = shippingMethods;
  }

  return updated;
};

/**
 * Apply defaults to cart for customer.
 *
 * @param {object} cartData
 *   The cart data object.
 * @param {integer} uid
 *   Drupal User ID.
 * @returns {Promise<object>}.
 *   The data.
 */
const applyDefaults = async (data, uid) => {
  // @todo Update this function to return data after processing with user inputs.
  if (!_.isEmpty(data.shipping.method)) {
    return data;
  }

  // Get last order only for Drupal Customers.
  const order = isUserAuthenticated()
    ? getLastOrder(uid)
    : [];

  // Try to apply defaults from last order.
  if (!_.isEmpty(order)) {
    // If cnc order but cnc is disabled.
    if (_.includes(order.shipping.method, 'click_and_collect') && await getCncStatusForCart() !== true) {
      return data;
    }

    const response = await applyDefaultShipping(order);
    if (response) {
      // @todo Check if returns empty string for anonymous (CORE-31245).
      response.payment.default = await getDefaultPaymentFromOrder(order);
      return response;
    }
  }

  // Select default address from address book if available.
  const address = getDefaultAddress(data);
  if (address) {
    const methods = await getHomeDeliveryShippingMethods(address);
    if (!_.isEmpty(methods) && _.isArray(methods)) {
      logger.notice(`Setting shipping/billing address from user address book. Address: ${address} Cart: ${window.commerceBackend.getCartId()}`);
      return selectHd(address, methods[0], address, methods);
    }
  }

  // If address already available in cart, use it.
  if (!_.isEmpty(data.shipping.address) && !_.isEmpty(data.shipping.address.country_id)) {
    const methods = await getHomeDeliveryShippingMethods(data.shipping.address);
    if (!_.isEmpty(methods) && _.isArray(methods)) {
      logger.notice(`Setting shipping/billing address from user address book. Address: ${data.shipping.address} Cart: ${window.commerceBackend.getCartId()}`);
      return selectHd(data.shipping.address, methods[0], data.shipping.address, methods);
    }
  }

  return data;
};

/**
 * Process cart data for checkout.
 *
 * @param {object} cartData
 *   The cart data object.
 * @returns {Promise<object|null>}
 *   A promise object.
 */
const getProcessedCheckoutData = async (cartData) => {
  if (_.isEmpty(cartData)) {
    return null;
  }

  let data = _.cloneDeep(cartData);
  if (typeof data.error !== 'undefined' && data.error === true) {
    return data;
  }

  // Check whether CnC enabled or not.
  const cncStatus = await getCncStatusForCart();

  // Here we will do the processing of cart to make it in required format.
  // @todo check if we need to use user.uid or userDetails.customerId.
  const updated = await applyDefaults(data, window.drupalSettings.user.uid);
  if (updated !== false && !_.isEmpty(updated.cart)) {
    data = updated;
  }

  if (_.isUndefined(data.shipping.methods)
    && !_.isUndefined(data.shipping.address)
    && !_.isUndefined(data.shipping.type) && data.shipping.type !== 'click_and_collect'
  ) {
    const methods = await getHomeDeliveryShippingMethods(data.shipping.address);
    if (_.isEmpty(methods) || (!_.isUndefined(methods.error) && methods.error)) {
      return methods;
    }
    data.shipping.methods = methods;
  }

  if (_.isUndefined(data.payment.methods)
    && !_.isUndefined(data.shipping.method)
  ) {
    const paymentMethods = await getPaymentMethods();
    if (!_.isEmpty(paymentMethods)) {
      data.payment.methods = paymentMethods;
    }
    data.payment.method = getPaymentMethodSetOnCart();
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
  if (typeof response.payment !== 'undefined' && typeof response.payment.methods !== 'undefined') {
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
 * @returns {Promise<object>}
 *   A promise object.
 */
window.commerceBackend.getCartForCheckout = () => {
  const cartId = window.commerceBackend.getCartId();
  if (cartId === null) {
    return new Promise((resolve) => resolve({ error: true }));
  }

  return getCart()
    .then(async (response) => {
      if (_.isEmpty(response.data) || !_.isEmpty(response.data.error_message)) {
        logger.error(`Error while getting cart:${cartId} Error:${response.data.error_message}`);
        return new Promise((resolve) => resolve(response.data));
      }

      if (_.isEmpty(response.data.cart) || _.isEmpty(response.data.cart.items)) {
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

      response.data = await getProcessedCheckoutData(response.data);
      return response;
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
      return error;
    });
};

/**
 * Get customer by email.
 * @todo implement this
 *
 * @param {string} email
 *   Email address.
 *
 * @returns {Promise<object>}
 *   Customer data if API call is successful else and array containing the
 *   error message.
 */
const getCustomerByMail = async (email) => {
  logger.info(`${email}`);
  // Temporary return.
  return { data: {} };
};

/**
 * Create customer in magento.
 * @todo implement this
 *
 * @param {string} email
 *   E-Mail address.
 * @param {string} firstname
 *   First name.
 * @param {string} lastname
 *   Last name.
 *
 * @returns {Promise<object>}
 *   Customer data if API call is successful else an array containing the
 *   error message.
 */
const createCustomer = async (email, firstname, lastname) => {
  logger.info(`${email}${firstname}${lastname}`);
  // Temporary return.
  return {
    data: {
      id: 492,
      group_id: 1,
      created_at: '2021-06-28 10:04:46',
      updated_at: '2021-06-28 10:04:46',
      created_in: 'AE English',
      email: 'test@example.com',
      firstname: 'test',
      lastname: 'test',
      store_id: 4,
      website_id: 3,
      addresses: [],
      disable_auto_group_change: 0,
      extension_attributes: {
        is_subscribed: false,
      },
      custom_attributes: [
        {
          attribute_code: 'channel',
          value: 'web',
          name: 'channel',
        },
      ],
    },
  };
};

/**
 * Add click n collect shipping on the cart.
 * @todo implement this
 *
 * @param {object} shippingData
 *   Shipping address info.
 * @param {string} action
 *   Action to perform.
 * @param {bool} updateBillingDetails
 *   Whether billing needs to update or not.
 *
 * @returns {Promise<object>}
 *   Cart data.
 * */
const addCncShippingInfo = async (shippingData, action, updateBillingDetails) => {
  logger.info(`${shippingData}${action}${updateBillingDetails}`);
};

/**
 * Format shipping info for api call.
 *
 * @param {object} $shipping_info
 *   Shipping info.
 *
 * @return {object}
 *   Formatted shipping info for api.
 */
const prepareShippingData = (shippingInfo) => {
  let result = {};

  if (_.isEmpty(shippingInfo)) {
    return result;
  }

  // If address id available.
  if (!_.isEmpty(shippingInfo.address_id)) {
    result.address_id = shippingInfo.address_id;
  } else {
    const data = _.cloneDeep(shippingInfo);
    const staticFields = data.static;
    delete data.static;
    let customAttributes = [];
    Object.keys(data).forEach((key) => {
      customAttributes.push(
        {
          attributeCode: key,
          value: data[key],
        },
      );
    });
    customAttributes = { customAttributes };
    result = {
      address: {
        ...staticFields,
        ...customAttributes,
      },
    };
  }

  return result;
};

/**
 * Adds shipping method to the cart and returns the cart.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise<object>}
 *   A promise object.
 */
window.commerceBackend.addShippingMethod = async (data) => {
  let cart = null;
  const shippingInfo = data.shipping_info;
  const updateBillingInfo = data.update_billing;
  const shippingEmail = shippingInfo.static.email;

  // Cart customer validations.
  if (window.drupalSettings.userDetails.customerId === 0
    && (_.isNull(await getCartCustomerId()) || (await getCartCustomerEmail() !== shippingEmail))
  ) {
    // Get customer by email.
    let customer = await getCustomerByMail(shippingEmail);
    if (!_.isUndefined(customer.data)
      && (!_.isUndefined(customer.data.error) && customer.data.error)
    ) {
      return customer;
    }
    customer = customer.data;

    // Create new customer.
    if (_.isEmpty(customer)) {
      customer = await createCustomer(
        shippingEmail,
        shippingInfo.static.firstname,
        shippingInfo.static.lastname,
      );
      if (!_.isUndefined(customer.data)
      && (!_.isUndefined(customer.data.error) && customer.data.error)
      ) {
        return customer;
      }
      customer = customer.data;
    }

    // Associate cart to customer.
    if (!_.isEmpty(customer) && !_.isUndefined(customer.id)) {
      const response = await associateCartToCustomer(customer.id);
      if (!_.isUndefined(response.data)
        && (!_.isUndefined(response.data.error) && response.data.error)
      ) {
        return response;
      }
    }
  }

  const type = (!_.isUndefined(shippingInfo.shipping_type))
    ? shippingInfo.shipping_type
    : 'home_delivery';

  if (type === 'click_and_collect') {
    // Unset as not needed in further processing.
    delete (shippingInfo.shipping_type);

    const logAddress = JSON.stringify(shippingInfo);
    const logData = JSON.stringify(data);
    const cartId = window.commerceBackend.getCartId();
    logger.notice(`Shipping update manual for CNC. Data: ${logData} Address: ${logAddress} Cart: ${cartId}.`);

    cart = await addCncShippingInfo(shippingInfo, data.action, updateBillingInfo);
  } else {
    let shippingMethods = [];
    let carrierInfo = [];
    if (!_.isEmpty(shippingInfo.carrier_info)) {
      carrierInfo = shippingInfo.carrier_info;
      delete shippingInfo.carrier_info;
    }

    const shippingData = prepareShippingData(shippingInfo);

    // If carrier info available in request, use that
    // instead getting shipping methods.
    let hdshippingMethods = [];
    if (!_.isEmpty(carrierInfo)) {
      shippingMethods.push({
        carrier_code: carrierInfo.code,
        method_code: carrierInfo.method,
      });
    } else {
      const methods = await getHomeDeliveryShippingMethods(shippingData.address);
      // If no shipping method.
      if (_.isEmpty(methods) || (!_.isUndefined(methods.error) && methods.error)) {
        const logData = JSON.stringify(data);
        const cartId = window.commerceBackend.getCartId();
        logger.notice(`Error while shipping update manual for HD. Data: ${logData} Cart: ${cartId} Error message: ${methods.error_message}`);
        return methods;
      }
      shippingMethods = methods;
      hdshippingMethods = methods;
    }

    if (!_.isEmpty(shippingMethods)) {
      shippingInfo.carrier_info = {
        code: shippingMethods[0].carrier_code,
        method: shippingMethods[0].method_code,
      };
    }

    const logAddress = JSON.stringify(shippingInfo);
    const logData = JSON.stringify(data);
    const cartId = window.commerceBackend.getCartId();
    logger.notice(`Shipping update manual for HD. Data: ${logData} Address: ${logAddress} Cart: ${cartId}`);

    cart = await addShippingInfo(shippingInfo, data.action, updateBillingInfo);

    if (!_.isEmpty(cart.data) && !_.isEmpty(cart.data.shipping) && !_.isEmpty(hdshippingMethods)) {
      cart.data.shipping.methods = hdshippingMethods;
    }
  }

  // Process cart data.
  cart.data = await getProcessedCheckoutData(cart.data);

  return cart;
};

/**
 * Adds billing method to the cart and returns the cart.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise<object>}
 *   A promise object.
 */
window.commerceBackend.addBillingMethod = (data) => updateBilling(data);

export {
  getProcessedCheckoutData,
  getCncStores,
};
