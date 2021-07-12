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
  matchStockQuantity,
  isCartHasOosItem,
} from './common';
import {
  cartErrorCodes,
  getDefaultErrorMessage,
} from './error';
import {
  getApiEndpoint,
  isUserAuthenticated,
  logger,
  getIp,
} from './utility';
import cartActions from '../../utilities/cart_actions';

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
 * Static cache for getProductStatus().
 *
 * @type {null}
 */
const staticProductStatus = [];

/**
 * Get data related to product status.
 *
 * @param {Promise<string|null>} sku
 *  The sku for which the status is required.
 */
const getProductStatus = async (sku) => {
  if (typeof sku === 'undefined' || !sku) {
    return null;
  }

  // Return from static, if available.
  if (!_.isUndefined(staticProductStatus[sku])) {
    return staticProductStatus[sku];
  }

  // Bypass CloudFlare to get fresh stock data.
  // Rules are added in CF to disable caching for urls having the following
  // query string.
  // The query string is added since same APIs are used by MAPP also.
  const response = await callDrupalApi(`/rest/v1/product-status/${btoa(sku)}/`, 'GET', { params: { _cf_cache_bypass: '1' } });
  if (!_.isUndefined(response.data)) {
    staticProductStatus[sku] = response.data;
  }

  return staticProductStatus[sku];
};

/**
 * Get CnC status for cart based on skus in cart.
 *
 * @param {object} data
 *    The cart data.
 *
 * @returns {Promise<boolean>}.
 *    The CNC status.
 */
const getCncStatusForCart = async (data) => {
  // Validate data.
  if (_.isEmpty(data) || _.isEmpty(data.cart)) {
    return true;
  }

  // Process items.
  for (let i = 0; i < data.cart.items.length; i++) {
    const item = data.cart.items[i];
    // We should ideally have ony one call to an endpoint and pass
    // The list of items. This look could happen in the backend.
    // Suppressing the lint error for now.
    // eslint-disable-next-line no-await-in-loop
    const productStatus = await getProductStatus(item.sku);
    if (!_.isEmpty(productStatus)
      && _.isBoolean(productStatus.cnc_enabled) && !productStatus.cnc_enabled
    ) {
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

  data.street = _.isString(address.street)
    ? [address.street]
    : address.street;

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
 *
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
    staticShippingMethods[key] = Object.values(methods);
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
  // Return as is if custom_attributes already set.
  if (typeof address.custom_attributes !== 'undefined') {
    return address;
  }

  const data = {};

  if (!_.isEmpty(address.static)) {
    Object.keys(address.static).forEach((key) => {
      data[key] = address.static[key];
    });
  }

  data.street = _.isString(address.street)
    ? [address.street]
    : address.street;

  const customAttributes = [];
  Object.keys(address).forEach((key) => {
    if (typeof data[key] !== 'undefined' || key === 'carrier_info' || key === 'static') {
      return;
    }

    if (_.isEmpty(address[key])) {
      return;
    }

    customAttributes.push({
      attribute_code: key,
      value: address[key],
    });
  });

  data.custom_attributes = customAttributes;
  return data;
};

/**
 * Validate area/city of address.
 *
 * @param {object} address
 *   Address object.
 *
 * @return  {Promise<object|boolean>}
 *   Address validation response or false in case of errors.
 */
const validateAddressAreaCity = async (address) => {
  // @TODO check response on browser.
  const response = await callDrupalApi('/spc/validate-info', 'POST', address);
  if (!_.isUndefined(response.data) && !_.isUndefined(response.data.address)) {
    return response.data.address;
  }
  return false;
};

/**
 * Get last order of the customer.
 * @todo implement this.
 */
const getLastOrder = () => [];

/**
 * Get customer's addresses.
 *
 * @returns {Promise<object>}
 *   Customer address ids.
 */
const getCustomerAddressIds = () => callMagentoApi(getApiEndpoint('getCustomerAddressIds'), 'GET', {});

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
const getPaymentMethods = async () => getCart()
  .then((response) => {
    const cartId = window.commerceBackend.getCartId();

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
      .then((paymentMethods) => {
        if (!_.isEmpty(response.data)) {
          return paymentMethods.data;
        }
        return null;
      });
  });

/**
 * Get the payment method set on cart.
 *
 * @return {Promise<string|null>}.
 *   Payment method set on cart.
 */
const getPaymentMethodSetOnCart = async () => {
  const params = {
    cartId: window.commerceBackend.getCartId(),
  };
  const response = await callMagentoApi(getApiEndpoint('selectedPaymentMethod', params), 'GET', {});
  if (!_.isEmpty(response) && !_.isEmpty(response.data) && !_.isEmpty(response.data.method)) {
    return response.data.method;
  }

  // Log if there is an error.
  if (!_.isEmpty(response.data.error)) {
    logger.error('Error while getting payment set on cart. Response: @response', {
      '@response': JSON.stringify(response.data),
    });
  }

  return null;
};

/**
 * Gets the data for a particular store.
 *
 * @param {string} store
 *   The store ID.
 *
 * @returns {Promise<object|null>}
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
 * @returns {Promise<array>}
 *   The list of stores.
 */
const getCartStores = async (lat, lon) => {
  const cartId = window.commerceBackend.getCartId();
  let stores = [];

  const url = getApiEndpoint('getCartStores', { cartId, lat, lon });
  const response = await callMagentoApi(url, 'GET', {});
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
    stores = stores.filter((value) => value != null);

    // Sort the stores first by distance and then by name.
    if (stores.length > 1) {
      stores = stores
        .sort((store1, store2) => store2.rnc_available - store1.rnc_available)
        .sort((store1, store2) => store1.distance - store2.distance);
    }
  } catch (error) {
    logger.notice(`Error occurred while fetching stores for cart id ${cartId}, API Response: ${error.message}`);
  }

  return stores;
};

/**
 *  Get the CnC stores for the cart.
 *
 * @param {string} lat
 *   The latitude value.
 * @param {string} lon
 *   The longiture value.
 *
 * @returns {Promise<array>}
 *   The list of stores.
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

  const response = await getCartStores(lat, lon);
  if (!_.isUndefined(response.data) && !_.isUndefined(response.data.error)) {
    // In case of errors, return the response with error.
    return response;
  }

  return { data: response };
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

  if (_.isArray(result.street)) {
    [result.street] = result.street;
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
      break;
  }

  return method;
};

/**
 * Update billing info on cart.
 *
 * @param {object} data
 *   Billing data.
 *
 * @returns {Promise<AxiosPromise<object>>}
 *   Response data.
 */
const updateBilling = async (data) => {
  const params = {
    extension: {
      action: cartActions.cartBillingUpdate,
    },
    billing: { ...data },
  };

  if (!_.isUndefined(params.billing.id)) {
    delete params.billing.id;
  }

  return updateCart(params);
};

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
 * @returns {Promise<AxiosPromise<object>|null>}
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
    params.shipping.shipping_address = formatAddressForShippingBilling(shippingData.address);
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

  if (_.isUndefined(data.shipping.shipping_address.custom_attributes)
    && !_.isUndefined(data.shipping.shipping_address.extension_attributes)
    && !_.isEmpty(data.shipping.shipping_address.extension_attributes)
  ) {
    const extensionAttributes = data.shipping.shipping_address.extension_attributes;
    data.shipping.shipping_address.custom_attributes = [];
    Object.keys(extensionAttributes).forEach((key) => {
      data.shipping.shipping_address.custom_attributes.push(
        {
          attributeCode: key,
          value: extensionAttributes[key],
        },
      );
    });
  }

  // Validate address.
  const valid = await validateAddressAreaCity(billing);
  if (!valid) {
    return false;
  }

  logger.notice('Shipping update default for CNC. Data: @data Address: @address Store: @store Cart: @cartId', {
    '@data': JSON.stringify(data),
    '@address': JSON.stringify(address),
    '@store': JSON.stringify(store),
    '@cartId': JSON.stringify(window.commerceBackend.getCartId()),
  });

  // If shipping address not contains proper data (extension info).
  if (_.isEmpty(data.shipping.shipping_address.extension_attributes)) {
    return false;
  }

  let cart = await updateCart(data);
  if (!_.isUndefined(cart.data.error) && cart.data.error) {
    return false;
  }

  // Not use/assign default billing address if customer_address_id
  // is not available.
  if (_.isUndefined(billing.customer_address_id)) {
    return cart;
  }

  // Add log for billing data we pass to magento update cart.
  logger.notice('Billing update default for CNC. Address: @address Cart: @cartId', {
    '@address': JSON.stringify(billing),
    '@cartId': JSON.stringify(window.commerceBackend.getCartId()),
  });

  // If billing address not contains proper data (extension info).
  if (_.isUndefined(billing.extension_attributes) || _.isEmpty(billing.extension_attributes)) {
    return false;
  }

  const customerAddressIds = await getCustomerAddressIds(billing.customer_id);

  // Return if address id from last order doesn't
  // exist in customer's address id list.
  // @TODO test this on the browser.
  if (_.findIndex(customerAddressIds, { id: billing.customer_address_id }) !== -1) {
    return cart;
  }

  cart = await updateBilling(billing);
  // If billing update has error.
  if (!_.isUndefined(cart.data.error) && cart.data.error) {
    return false;
  }

  return cart;
};

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
const applyDefaultShipping = async (order) => {
  const address = order.shipping.commerce_address;
  // @todo finish the implementation of applyDefaultShipping() on CORE-30722
  selectCnc(0, address, order.billing_commerce_address);
  return false;
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
    customer_address_id: 0,
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
  const valid = await validateAddressAreaCity(shippingData.address);
  if (!valid) {
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
  logger.notice('Billing update default for HD. Address: @address Cart: @cartId', {
    '@address': JSON.stringify(billing),
    '@cartId': cartId,
  });

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
    if (_.includes(order.shipping.method, 'click_and_collect') && await getCncStatusForCart(data) !== true) {
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
    if (!_.isEmpty(methods) && _.isArray(methods) && _.isUndefined(methods.error)) {
      logger.notice(`Setting shipping/billing address from user address book. Address: ${address} Cart: ${window.commerceBackend.getCartId()}`);
      return selectHd(address, methods[0], address, methods);
    }
  }

  // If address already available in cart, use it.
  if (!_.isEmpty(data.shipping.address) && !_.isEmpty(data.shipping.address.country_id)) {
    const methods = await getHomeDeliveryShippingMethods(data.shipping.address);
    if (!_.isEmpty(methods) && _.isArray(methods) && _.isUndefined(methods.error)) {
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
 * @returns {Promise<AxiosPromise<object>|null>}
 *   A promise object.
 */
const getProcessedCheckoutData = async (cartData) => {
  if (!_.isUndefined(cartData.error)) {
    return cartData;
  }

  if (_.isEmpty(cartData)) {
    return null;
  }

  let data = _.cloneDeep(cartData);
  if (typeof data.error !== 'undefined' && data.error === true) {
    return data;
  }

  // Check whether CnC enabled or not.
  const cncStatus = await getCncStatusForCart(data);

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
    data.payment.method = await getPaymentMethodSetOnCart();
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
    if (typeof response.payment.method !== 'undefined' && !_.isEmpty(codes) && !codes.includes(response.payment.method)) {
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
 * Adds billing method to the cart and returns the cart.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise<object>}
 *   A promise object.
 */
window.commerceBackend.addBillingMethod = async (data) => {
  const billingInfo = data.billing_info;
  const billingData = formatAddressForShippingBilling(billingInfo);

  const logAddress = JSON.stringify(billingData);
  const logData = JSON.stringify(billingInfo);
  const cartId = window.commerceBackend.getCartId();
  logger.notice(`Billing update manual. Address: ${logAddress} Data: ${logData} Cart: ${cartId}`);

  const cart = await updateBilling(billingData);

  // Process cart data.
  cart.data = await getProcessedCheckoutData(cart.data);

  return cart;
};

/**
 * Checks if upapi payment method (payment method via checkout.com).
 *
 * @param {string} paymentMethod
 *   Payment method code.
 *
 * @return {bool}
 *   TRUE if payment methods from checkout.com
 */
const isUpapiPaymentMethod = (paymentMethod) => paymentMethod.indexOf('checkout_com_upapi', 0) !== -1;

/**
 * Checks if postpay payment method.
 *
 * @param {string} paymentMethod
 *   Payment method code.
 *
 * @return {bool}
 *   TRUE if payment methods from postpay
 */
const isPostpayPaymentMethod = (paymentMethod) => paymentMethod.indexOf('postpay', 0) !== -1;

/**
 * Prepare message to log when API fail after payment successful.
 * @todo implement this
 *
 * @param {array} cart
 *   Cart Data.
 * @param {array} data
 *   Payment data.
 * @param {string} exceptionMessage
 *   Exception message.
 * @param {string} api
 *   API identifier which failed.
 * @param {string} doubleCheckDone
 *   Flag to say if double check was done or not.
 *
 * @return {string}
 *   Prepared error message.
 */
const prepareOrderFailedMessage = (cart, data, exceptionMessage, api, doubleCheckDone) => {
  logger.log(`${cart}, ${data}, ${exceptionMessage}, ${api}, ${doubleCheckDone}`);
};

/**
 * Fetches the list of click and collect stores.
 *
 * @param {object} coords
 *   The co-ordinates data.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.fetchClickNCollectStores = (coords) => getCncStores(coords.lat, coords.lng);

/**
 * Adds payment method in the cart and returns the cart.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise<object>}
 *   A promise object.
 */
const paymentUpdate = async (data) => {
  const paymentData = data.payment_info.payment;
  const params = {
    extension: {
      action: cartActions.cartPaymentUpdate,
    },
    payment: {
      method: paymentData.method,
      additional_data: (!_.isUndefined(paymentData.additional_data))
        ? paymentData.additional_data
        : [],
    },
  };

  if (!_.isUndefined(data.payment_info)
    && !_.isUndefined(data.payment_info.payment)
    && !_.isUndefined(data.payment_info.payment.analytics)
  ) {
    const analyticsData = data.payment_info.payment.analytics;

    params.extension.ga_client_id = '';
    if (!_.isUndefined(analyticsData.clientID) && !_.isNull(analyticsData.clientID)) {
      params.extension.ga_client_id = analyticsData.clientID;
    }

    params.extension.tracking_id = '';
    if (!_.isUndefined(analyticsData.trackingId) && !_.isNull(analyticsData.trackingId)) {
      params.extension.tracking_id = analyticsData.trackingId;
    }

    params.extension.user_id = (window.drupalSettings.userDetails.customerId > 0)
      ? window.drupalSettings.userDetails.customerId
      : 0;

    params.extension.user_type = (window.drupalSettings.userDetails.customerId > 0)
      ? 'Logged in User'
      : 'Guest User';

    params.extension.user_agent = navigator.userAgent;

    // @todo maybe we could use some geolocation service.
    params.extension.client_ip = await getIp();

    params.extension.attempted_payment = 1;
  }

  // If upapi payment method (payment method via checkout.com).
  if (isUpapiPaymentMethod(paymentData.method) || isPostpayPaymentMethod(paymentData.method)) {
    // Add success and fail redirect url to additional data.
    params.payment.additional_data.successUrl = `${window.location.origin}${Drupal.url('spc/payment-callback/success')}`;
    params.payment.additional_data.failUrl = `${window.location.origin}${Drupal.url(`spc/payment-callback/${paymentData.method}/error`)}`;
  }

  // @todo implement the processing for checkout_com_upapi for Cart::processPaymentData().
  // @todo update payment method to checkout_com_upapi_vault if using a saved card.
  // Both the points above are for authenticated users so still pending.

  const logData = JSON.stringify(paymentData);
  const cartId = window.commerceBackend.getCartId();
  logger.notice(`Calling update payment for payment_update. Cart id: ${cartId} Method: ${paymentData.method} Data: ${logData}`);

  const oldCart = await getCart();
  const cart = await updateCart(params);
  if (_.isEmpty(cart.data) || (!_.isUndefined(cart.data.error) && cart.data.error)) {
    const errorMessage = (cart.data.error_code > 600) ? 'Back-end system is down' : cart.data.error.error_message;
    const message = prepareOrderFailedMessage(oldCart, data, errorMessage, 'update cart', 'NA');
    logger.error(`Error occurred while placing order. ${message}`);
  }

  cart.data = await getProcessedCheckoutData(cart.data);
  return cart;
};

/**
 * Get address fields to validate from drupal settings.
 *
 * @return {object}
 *   Fields to validate.
 */
const cartAddressFieldsToValidate = () => {
  let addressFieldsToValidate = [];

  // Get the address fields based on site/country code
  // from the drupal settings.
  const siteCountryCode = window.drupalSettings.cart.site_country_code;
  const addressFields = window.drupalSettings.cart.address_fields;

  // Use default value first if available.
  if (!_.isUndefined(siteCountryCode.country_code)) {
    const countryCode = siteCountryCode.country_code;
    if (!_.isUndefined(addressFields.default[countryCode])) {
      addressFieldsToValidate = addressFields.default[countryCode];
    }
    if (!_.isUndefined(siteCountryCode.site_code)) {
      const siteCode = siteCountryCode.site_code;
      // If brand specific value available/override.
      if (!_.isUndefined(addressFields[siteCode])
        && !_.isUndefined(addressFields[siteCode][countryCode])
      ) {
        addressFieldsToValidate = addressFields[siteCode][countryCode];
      }
    }
  }
  return addressFieldsToValidate;
};

/**
 * Validates the extension attributes of the address of the cart.
 *
 * @param {object} data
 *   Cart data.
 *
 * @return {bool}
 *   FALSE if empty field value.
 */
const isAddressExtensionAttributesValid = (data) => {
  let isValid = true;
  // If there are address fields available for validation
  // in drupal settings.
  const addressFieldsToValidate = cartAddressFieldsToValidate();
  if (!_.isEmpty(addressFieldsToValidate)) {
    const cartAddressCustom = [];
    // Prepare cart address field data.
    data.shipping.address.custom_attributes.forEach((item) => {
      cartAddressCustom[item.attribute_code] = item.value;
    });

    // Check each required field in custom attributes available in cart
    // shipping address or not.
    addressFieldsToValidate.forEach((field) => {
      // If field not exists or empty.
      if (_.isEmpty(cartAddressCustom[field])) {
        const cartId = window.commerceBackend.getCartId();
        logger.error(`Field: ${field} not available in cart shipping address. Cart id: ${cartId}`);
      }
      isValid = false;
      return isValid;
    });
  }
  return isValid;
};

/**
 * Finalises the payment on the cart.
 *
 * @returns {Promise<boolean|object>}
 *   A promise object with true or the error object.
 */
const validateBeforePaymentFinalise = async () => {
  // Fetch fresh cart from magento.
  const cart = await getCart(true);
  const cartData = cart.data;

  let isError = false;
  let errorMessage = 'Delivery Information is incomplete. Please update and try again.';
  let errorCode = cartErrorCodes.cartOrderPlacementError;

  if (_.isObject(cartData) && isCartHasOosItem(cartData)) {
    isError = true;
    logger.error(`Error while finalizing payment. Cart has an OOS item. Cart: ${JSON.stringify(cartData)}.`);

    Object.keys(cartData.cart.items).forEach((key) => {
      matchStockQuantity(cartData.cart.items[key].sku);
    });

    errorMessage = 'Cart contains some items which are not in stock.';
    errorCode = cartErrorCodes.cartHasOOSItem;
  } else if (_.isUndefined(cartData.shipping.method)
    || _.isEmpty(cartData.shipping.method)
  ) {
    // Check if shipping method is present else throw error.
    isError = true;
    const logData = JSON.stringify(cartData);
    logger.error(`Error while finalizing payment. No shipping method available. Cart: ${logData}.`);
    //
  } else if (_.isUndefined(cartData.shipping.address)
    || _.isUndefined(cartData.shipping.address.custom_attributes)
    || _.isEmpty(cartData.shipping.address.custom_attributes)
  ) {
    // If shipping address not have custom attributes.
    isError = true;
    const logData = JSON.stringify(cartData);
    logger.error(`Error while finalizing payment. Shipping address not contains all info. Cart: ${logData}.`);
    //
  } else if (!isAddressExtensionAttributesValid(cartData)) {
    // If address extension attributes doesn't contain all the required
    // fields or required field value is empty, not process/place order.
    isError = true;
    const logData = JSON.stringify(cartData);
    logger.error(`Error while finalizing payment. Shipping address not contains all required extension attributes. Cart: ${logData}.`);
  } else if (_.isUndefined(cartData.shipping.address.firstname)
    || _.isUndefined(cartData.shipping.address.lastname)
  ) {
    // If first/last name not available in shipping address.
    isError = true;
    const logData = JSON.stringify(cartData);
    logger.error(`Error while finalizing payment. First name or Last name not available in cart for shipping address. Cart: ${logData}.`);
  } else if (_.isUndefined(cartData.cart.billing_address.firstname)
    || _.isUndefined(cartData.cart.billing_address.lastname)
  ) {
    // If first/last name not available in billing address.
    isError = true;
    const logData = JSON.stringify(cartData);
    logger.error(`Error while finalizing payment. First name or Last name not available in cart for billing address. Cart: ${logData}.`);
  }

  if (isError) {
    return {
      data: {
        error: true,
        error_code: errorCode,
        error_message: errorMessage,
      },
    };
  }

  return true;
};

/**
 * Used to add payment methods to the cart and to finalise payment.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise<object>}
 *   A promise object.
 */
window.commerceBackend.addPaymentMethod = async (data) => {
  // Validate cart.
  if (data.action === cartActions.cartPaymentFinalise) {
    const response = await validateBeforePaymentFinalise();
    if (!_.isUndefined(response.data)
      && !_.isUndefined(response.data.error) && response.data.error
    ) {
      return response;
    }
  }

  return paymentUpdate(data);
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
  if (window.drupalSettings.userDetails.customerId === 0) {
    const customerId = await getCartCustomerId();
    if (customerId > 0) {
      // @todo handle exception when user is guest but cart is of customer.
      throw new Error('Cart is associated to customer.');
    }
  } else {
    const customerEmail = await getCartCustomerEmail();
    if (customerEmail !== shippingEmail) {
      // @todo handle exception when cart is of different customer than the one logged in.
      throw new Error('Cart is associated to different customer.');
    }
  }

  const cartId = window.commerceBackend.getCartId();

  const type = (!_.isUndefined(shippingInfo.shipping_type))
    ? shippingInfo.shipping_type
    : 'home_delivery';

  if (type === 'click_and_collect') {
    // Unset as not needed in further processing.
    delete (shippingInfo.shipping_type);

    logger.notice('Shipping update manual for CNC. Data: @data Address: @address Cart: @cart_id', {
      '@data': JSON.stringify(data),
      '@address': JSON.stringify(shippingInfo),
      '@cart_id': cartId,
    });

    cart = await addCncShippingInfo(shippingInfo, data.action, updateBillingInfo);

    // Process cart data.
    cart.data = await getProcessedCheckoutData(cart.data);

    return cart;
  }

  const shippingAddress = formatAddressForShippingBilling(shippingInfo);
  const shippingMethods = await getHomeDeliveryShippingMethods(shippingAddress);

  // If no shipping method.
  if (_.isEmpty(shippingMethods)
    || (!_.isUndefined(shippingMethods.error) && shippingMethods.error)) {
    logger.notice('Error while shipping update manual for HD. Data: @data Cart: @cart_id Error message: @error_message', {
      '@data': JSON.stringify(data),
      '@cart_id': cartId,
      '@error_message': shippingMethods.error_message,
    });

    return shippingMethods;
  }

  let carrierInfo = {};
  if (!_.isEmpty(shippingInfo.carrier_info)) {
    carrierInfo = shippingInfo.carrier_info;
  }

  if (_.isEmpty(carrierInfo)) {
    carrierInfo = {
      code: shippingMethods[0].carrier_code,
      method: shippingMethods[0].method_code,
    };
  }

  const params = {
    address: shippingAddress,
    carrier_info: carrierInfo,
  };

  logger.notice('Shipping update manual for HD. Data: @params. Cart: @cartId', {
    '@params': JSON.stringify(params),
    '@cartId': window.commerceBackend.getCartId(),
  });

  cart = await addShippingInfo(params, data.action, updateBillingInfo);

  if (!_.isEmpty(cart.data) && !_.isEmpty(cart.data.shipping) && !_.isEmpty(shippingMethods)) {
    cart.data.shipping.methods = shippingMethods;
  }

  // Process cart data.
  cart.data = await getProcessedCheckoutData(cart.data);

  return cart;
};

/**
 * Triggers the checkout event post order placed.
 *
 * @param {string} event
 *   The action.
 * @param {object} data
 *   The params for checkout event.
 *
 * @returns {Promise<AxiosPromise<Object>>}
 */
const triggerCheckoutEvent = (event, data) => callDrupalApi(
  '/spc/checkout-event',
  'POST',
  {
    form_params: {
      ...data,
      action: event,
    },
  },
).catch((error) => {
  logger.error('Error occurred while triggering checkout event @event. Message: @message', {
    '@event': event,
    '@message': error.message,
  });
});

/**
 * Process operations post order placed.
 *
 * @param {object} cart
 *   Cart details.
 * @param {int} orderId
 *   Order id.
 * @param {string} paymentMethod
 *   Payment Method.
 */
const processPostOrderPlaced = (cart, orderId, paymentMethod) => {
  let customerId = '';
  if (!_.isEmpty(cart.data.cart.customer)
    && !_.isEmpty(cart.data.cart.customer.id)) {
    customerId = cart.data.cart.customer.id;
  }

  // Remove cart id and other caches from session.
  window.commerceBackend.removeCartDataFromStorage();
  localStorage.removeItem('cart_id');

  // Post order id and cart data to Drupal.
  const data = {
    order_id: orderId,
    cart: cart.data.cart,
    payment_method: paymentMethod,
    customer_id: customerId,
  };

  triggerCheckoutEvent('place order success', data);
};

/**
 * Places an order.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.placeOrder = async (data) => {
  const cart = await getCart(true);

  if (_.isObject(cart) && isCartHasOosItem(cart.data)) {
    logger.error('Error while finalizing payment. Cart has an OOS item. Cart: @cart', {
      '@cart': JSON.stringify(cart),
    });

    Object.keys(cart.data.cart.items).forEach((key) => {
      matchStockQuantity(cart.data.cart.items[key].sku);
    });

    return {
      data: {
        error: true,
        error_code: cartErrorCodes.cartHasOOSItem,
        error_message: 'Cart contains some items which are not in stock.',
      },
    };
  }

  // Check if shipping method is present else throw error.
  if (_.isEmpty(cart.data.shipping.method)) {
    logger.error('Error while placing order. No shipping method available. Cart: @cart', {
      '@cart': JSON.stringify(cart),
    });
    return {
      data: {
        error: true,
        error_code: cartErrorCodes.cartOrderPlacementError,
        error_message: 'Delivery Information is incomplete. Please update and try again.',
      },
    };
  }

  // Check if shipping address not have custom attributes.
  if (_.isEmpty(cart.data.shipping.address.custom_attributes)) {
    logger.error('Error while placing order. Shipping address not contains all info. Cart: @cart', {
      '@cart': JSON.stringify(cart),
    });
    return {
      data: {
        error: true,
        error_code: cartErrorCodes.cartOrderPlacementError,
        error_message: 'Delivery Information is incomplete. Please update and try again.',
      },
    };
  }

  if (!isAddressExtensionAttributesValid(cart.data)) {
    // If address extension attributes doesn't contain all the required
    // fields or required field value is empty, not process/place order.
    logger.error('Error while placing order. Shipping address not contains all required extension attributes. Cart: @cart', {
      '@cart': JSON.stringify(cart),
    });
    return {
      data: {
        error: true,
        error_code: cartErrorCodes.cartOrderPlacementError,
        error_message: 'Delivery Information is incomplete. Please update and try again.',
      },
    };
  }

  // If first/last name not available in shipping address.
  if (_.isEmpty(cart.data.shipping.address.firstname)
    || _.isEmpty(cart.data.shipping.address.lastname)) {
    logger.error('Error while placing order. First name or Last name not available in cart for shipping address. Cart: @cart.', {
      '@cart': JSON.stringify(cart),
    });
    return {
      data: {
        error: true,
        error_code: cartErrorCodes.cartOrderPlacementError,
        error_message: 'Delivery Information is incomplete. Please update and try again.',
      },
    };
  }

  // Check If first/last name not available in billing address.
  if (_.isEmpty(cart.data.cart.billing_address.firstname)
    || _.isEmpty(cart.data.cart.billing_address.lastname)) {
    logger.error('Error while placing order. First name or Last name not available in cart for billing address. Cart: @cart.', {
      '@cart': JSON.stringify(cart),
    });
    return {
      data: {
        error: true,
        error_code: cartErrorCodes.cartOrderPlacementError,
        error_message: 'Delivery Information is incomplete. Please update and try again.',
      },
    };
  }

  const params = {
    cartId: window.commerceBackend.getCartId(),
  };

  return callMagentoApi(getApiEndpoint('placeOrder', params), 'PUT')
    .then((response) => {
      const result = {
        success: true,
        isAbsoluteUrl: false,
      };

      if (typeof response.data.redirect_url !== 'undefined') {
        result.error = true;
        result.success = false;
        result.redirectUrl = response.data.redirect_url;
        result.isAbsoluteUrl = true;

        // This is postpay specific. In future if any other payment gateway sends
        // token, we will have to add a condition here.
        if (typeof response.data.token !== 'undefined') {
          result.token = response.data.token;
        }

        logger.notice('Place order returned redirect url. Cart: @cart Response: @response.', {
          '@cart': JSON.stringify(cart),
          '@response': JSON.stringify(response.data),
        });

        return { data: result };
      }

      const orderId = parseInt(response.data, 10);
      const secureOrderId = btoa(JSON.stringify({
        order_id: orderId,
        email: cart.data.cart.billing_address.email,
      }));

      // Operations post order placed.
      processPostOrderPlaced(cart, orderId, data.data.paymentMethod.method);

      result.redirectUrl = `checkout/confirmation?oid=${secureOrderId}}`;

      logger.notice('Order placed successfully. Cart: @cart OrderId: @order_id, Payment Method: @method.', {
        '@cart': JSON.stringify(cart),
        '@order_id': orderId,
        '@method': data.data.paymentMethod.method,
      });

      return { data: result };
    })
    .catch((response) => {
      logger.error('Error while placing order. Error message: @message, Code: @code.', {
        '@message': !_.isEmpty(response.error) ? response.error.message : response,
        '@code': !_.isEmpty(response.error) ? response.error.error_code : '',
      });

      // @todo all the error handling.
      // @todo cancel reservation.

      return response;
    });
};

export {
  getProcessedCheckoutData,
  getCncStores,
};
