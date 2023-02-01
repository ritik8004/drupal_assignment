import {
  isAnonymousUserWithoutCart,
  getCart,
  updateCart,
  getFormattedError,
  getProcessedCartData,
  checkoutComUpapiVaultMethod,
  checkoutComVaultMethod,
  getCartCustomerEmail,
  getCartCustomerId,
  matchStockQuantity,
  isCartHasOosItem,
  getProductStatus,
} from './common';
import {
  getApiEndpoint,
  isUserAuthenticated,
  getIp,
  isRequestFromSocialAuthPopup,
} from './utility';
import logger from '../../../../js/utilities/logger';
import cartActions from '../../utilities/cart_actions';
import {
  getPaymentMethods,
  getPaymentMethodSetOnCart,
} from './checkout.payment';
import {
  formatAddressForShippingBilling,
  getHomeDeliveryShippingMethods,
} from './checkout.shipping';
import {
  hasValue,
  isBoolean,
  isObject,
  isArray,
} from '../../../../js/utilities/conditionsUtility';
import { cartErrorCodes, getDefaultErrorMessage } from '../../../../js/utilities/error';
import {
  callDrupalApi,
  callMagentoApi,
  getCartSettings,
} from '../../../../js/utilities/requestHelper';
import collectionPointsEnabled from '../../../../js/utilities/pudoAramaxCollection';
import { isCollectionPoint } from '../../utilities/cnc_util';
import {
  cartContainsOnlyVirtualProduct,
  cartItemIsVirtual,
  isFullPaymentDoneByEgift,
} from '../../utilities/egift_util';
import { isEgiftCardEnabled } from '../../../../js/utilities/util';
import { getTopUpQuote } from '../../../../js/utilities/egiftCardHelper';
import dispatchCustomEvent from '../../../../js/utilities/events';

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
 * Get CnC status for cart based on skus in cart.
 *
 * @param {object} data
 *    The cart data.
 *
 * @returns {Promise<boolean>}.
 *    The CNC status.
 */
const getCncStatusForCart = async (data) => {
  const staticStatus = Drupal.alshayaSpc.staticStorage.get('cnc_status');

  if (staticStatus !== null) {
    return staticStatus;
  }

  // Validate data.
  if (!hasValue(data) || !hasValue(data.cart)) {
    return true;
  }

  // Process items.
  for (let i = 0; i < data.cart.items.length; i++) {
    const item = data.cart.items[i];

    // Skip product status check if egift is enabled and is virtual product.
    if (isEgiftCardEnabled() && cartItemIsVirtual(item)) {
      return true;
    }

    // We should ideally have ony one call to an endpoint and pass
    // The list of items. This look could happen in the backend.
    // Suppressing the lint error for now.
    const hasParentSku = hasValue(item.extension_attributes)
      && hasValue(item.extension_attributes.parent_product_sku);
    const parentSKU = (item.product_type === 'configurable' && hasParentSku)
      ? item.extension_attributes.parent_product_sku
      : null;

    // eslint-disable-next-line no-await-in-loop
    const productStatus = await getProductStatus(item.sku, parentSKU);
    if (hasValue(productStatus)
      && isBoolean(productStatus.cnc_enabled) && !productStatus.cnc_enabled
    ) {
      Drupal.alshayaSpc.staticStorage.set('cnc_status', false);
      return false;
    }
  }

  Drupal.alshayaSpc.staticStorage.set('cnc_status', true);
  return true;
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
  if (!hasValue(data.customer) || !hasValue(data.customer.addresses)) {
    return null;
  }

  // If address is set as default for shipping.
  const key = _.findIndex(data.customer.addresses, (address) => address.default_shipping === '1');
  if (key >= 0) {
    return data.customer.addresses[key];
  }

  // Return first address.
  return [].concat(data.customer.addresses).shift();
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
  const response = await callDrupalApi('/spc/validate-info', 'POST', { address });
  if (hasValue(response)
    && hasValue(response.data)
    && hasValue(response.data.address)
  ) {
    return response.data.address;
  }
  return false;
};

/**
 * Performs some data transformations.
 *
 * @param {object} orderData
 *   The order data.
 *
 * @return {object}
 *   The transformed data.
 */
const processLastOrder = (orderData) => {
  const order = { ...orderData };
  if (!hasValue(order.entity_id)) {
    return order;
  }

  const data = {};
  data.order_id = order.entity_id;

  // Customer info.
  data.firstname = order.customer_firstname;
  data.lastname = order.customer_lastname;
  data.email = order.customer_email;

  // Items.
  data.items = order.items;

  // Coupon code.
  data.coupon = hasValue(order.coupon_code) ? order.coupon_code : '';

  // Extension.
  data.extension = order.extension_attributes;
  delete order.extension_attributes;

  // Shipping.
  data.shipping = data.extension.shipping_assignments[0].shipping;
  data.shipping.address.customer_id = order.customer_id;
  delete data.shipping.address.entity_id;
  delete data.shipping.address.parent_id;

  data.shipping.commerce_address = data.shipping.address;
  data.shipping.extension = data.shipping.address.extension_attributes;

  // Billing.
  data.billing = order.billing_address;
  data.billing.customer_id = order.customer_id;
  delete order.billing_address.entity_id;
  delete order.billing_address.parent_id;

  data.billing_commerce_address = data.billing;
  delete order.billing_address;
  data.billing.extension = data.billing.extension_attributes;

  Object.assign(order, data);

  return order;
};

/**
 * Get last order of the customer.
 *
 * @param {string} customerId
 *   The customer id.
 * @param {boolean} force
 *   Bypass static cache.
 * @returns {Promise<AxiosPromise<Object>>}
 *   Customer last order or null.
 */
const getLastOrder = async (customerId, force = false) => {
  // If request is from SocialAuth Popup, restrict further processing.
  // we don't want magento API calls happen on popup, As this is causing issues
  // in processing parent pages.
  if (isRequestFromSocialAuthPopup()) {
    return {};
  }

  const staticOrder = Drupal.alshayaSpc.staticStorage.get('last_order');
  if (!force && staticOrder !== null) {
    return staticOrder;
  }

  try {
    const order = await callMagentoApi(getApiEndpoint('getLastOrder'), 'GET', {});
    if (!hasValue(order.data) || hasValue(order.data.error)) {
      logger.warning('Error while fetching last order of customer. CustomerId: @customerId, Response: @response.', {
        '@response': JSON.stringify(order),
        '@customerId': customerId,
      });

      Drupal.alshayaSpc.staticStorage.set('last_order', {});
      return {};
    }

    const processedOrder = processLastOrder(order.data);
    Drupal.alshayaSpc.staticStorage.set('last_order', processedOrder);
    return processedOrder;
  } catch (error) {
    logger.error('Error while fetching last order of customer. CustomerId: @customerId, Message: @message.', {
      '@message': error.message,
      '@customerId': customerId,
    });
  }

  Drupal.alshayaSpc.staticStorage.set('last_order', {});
  return {};
};

/**
 * Validate if the order exists with the given cart id.
 *
 * @param {string} cartId
 *   Masked cart id.
 *
 * @returns {object}
 *   Object containing order response.
 */
const validateOrder = async (cartId) => {
  // Return if the cart id is not available.
  if (!cartId) {
    return {};
  }

  try {
    const order = await callMagentoApi(getApiEndpoint('validateOrder', { cartId }), 'GET', {});
    if (!hasValue(order.data) || hasValue(order.data.error)) {
      logger.warning('Error while validating the order. CartId: @cartId, Response: @response.', {
        '@response': JSON.stringify(order),
        '@cartId': cartId,
      });

      return {};
    }

    const processedOrder = processLastOrder(order.data);

    return processedOrder;
  } catch (error) {
    logger.error('Error while validating the order. CartId: @cartId, Message: @message.', {
      '@message': error.message,
      '@cartId': cartId,
    });
  }

  return {};
};

/**
 * Get payment method from last order.
 *
 * @param {object} order
 *   Last Order details.
 *
 * @returns {Promise<object|null>}
 *   Payment method name or null.
 */
const getDefaultPaymentFromOrder = async (order) => {
  if (!hasValue(order.payment) || !hasValue(order.payment.method)) {
    return order;
  }

  const orderPaymentMethod = order.payment.method;

  const methods = await getPaymentMethods();
  if (!hasValue(methods)
    || (typeof methods.error !== 'undefined' && methods.error)
  ) {
    return null;
  }

  const methodNames = methods.map((value) => value.code);

  if (!_.contains(methodNames, orderPaymentMethod)) {
    return null;
  }

  return orderPaymentMethod;
};

/**
 * Gets the store data saved in local storage.
 *
 * @param {string} key
 *   The identifier key in the localStorage for the store data.
 *
 * @returns {object|null}
 *   Returns the store data object if found else null.
 */
const getSavedDrupalStoreData = (key) => Drupal.getItemFromLocalStorage(key);

/**
 * Sets the store data in the local storage.
 *
 * @param {string} key
 *   The identifier key in the localStorage for the store data.
 * @param {object} data
 *   The store data object.
 */
const setDrupalStoreData = (key, data) => Drupal.addItemInLocalStorage(
  key,
  data,
  (drupalSettings.cncStoreInfoCacheTime * 60),
);

/**
 * Gets the data for a particular store.
 *
 * @param {string} storeInformation
 *   The store ID.
 *
 * @returns {Promise<object|null>}
 *   Returns a promise which resolves to an array of data for the given store or
 * an empty array in case of any issue.
 */
const getStoreInfo = async (storeInformation) => {
  let store = { ...storeInformation };

  if (typeof store.code === 'undefined' || !store.code) {
    return null;
  }

  const storageKey = `storeInfo:${drupalSettings.path.currentLanguage}:${store.code}`;
  let storeData = getSavedDrupalStoreData(storageKey);
  let storeInfo = null;

  if (hasValue(storeData)) {
    storeInfo = storeData.data;
  } else {
    storeData = {};

    // Fetch store info from Drupal.
    const response = await callDrupalApi(`/cnc/store/${store.code}`, 'GET', {});

    // If some error occurred, return empty object.
    if (!hasValue(response)
      || !hasValue(response.data)
      || hasValue(response.data.error)) {
      setDrupalStoreData(storageKey, storeData);
      return storeData;
    }

    storeInfo = response.data;

    // Set the Drupal store data into storage.
    storeData.data = storeInfo;
    setDrupalStoreData(storageKey, storeData);
  }

  // Get the complete data about the store by combining the received data from
  // Magento with the processed store data stored in Drupal.
  store = Object.assign(store, storeInfo);

  if (hasValue(store.distance)) {
    store.formatted_distance = store.distance
      .toLocaleString('us', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
      .replace(/,/g, '');
    store.formatted_distance = parseFloat(store.formatted_distance);
  }

  if (hasValue(store.sts_delivery_time_label)) {
    store.delivery_time = store.sts_delivery_time_label;
  }

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
 * @param {int} cncStoresLimit
 *   The default number of stores to display.
 *
 * @returns {Promise<array>}
 *   The list of stores.
 */
const getCartStores = async (lat, lon, cncStoresLimit = 0) => {
  // If request is from SocialAuth Popup, restrict further processing.
  // we don't want magento API calls happen on popup, As this is causing issues
  // in processing parent pages.
  if (isRequestFromSocialAuthPopup()) {
    return [];
  }

  const cartId = window.commerceBackend.getCartId();

  // If cart not available in session, log the error and return empty array.
  if (!cartId) {
    logger.warning('Error while fetching click and collect stores. No cart available in session');
    return [];
  }

  let staticStoresData = Drupal.alshayaSpc.staticStorage.get('cartStores');
  if (staticStoresData === null) {
    staticStoresData = {};
  }

  const staticStorageKey = `${lat}_${lon}`;
  if (typeof staticStoresData[staticStorageKey] !== 'undefined') {
    return staticStoresData[staticStorageKey];
  }

  staticStoresData[staticStorageKey] = [];

  const url = getApiEndpoint('getCartStores', { cartId, lat, lon });
  const response = await callMagentoApi(url, 'GET', {});

  // If no stores available, return empty.
  if (!hasValue(response.data)) {
    Drupal.alshayaSpc.staticStorage.set('cartStores', staticStoresData);
    return [];
  }

  // If API returned error, log the error and return empty.
  if (hasValue(response.data.error) && response.data.error) {
    logger.warning('Error occurred while fetching stores for cart id @cartId, API Response: @response.', {
      '@cartId': cartId,
      '@response': JSON.stringify(response.data),
    });

    Drupal.alshayaSpc.staticStorage.set('cartStores', staticStoresData);
    return [];
  }

  // If not array return empty.
  if (!Array.isArray(response.data)) {
    logger.warning('Error occurred while fetching stores for cart id @cartId, API Response: @response.', {
      '@cartId': cartId,
      '@response': JSON.stringify(response.data),
    });

    Drupal.alshayaSpc.staticStorage.set('cartStores', staticStoresData);
    return [];
  }

  const storeInfoPromises = [];
  let stores = response.data;

  // If cncStoresLimit is greater than 0, then only display that many stores else display all.
  if (cncStoresLimit > 0) {
    stores = stores.slice(0, cncStoresLimit);
  }

  const isCollectionPointEnabled = collectionPointsEnabled();
  stores.forEach((store) => {
    // Do not fetch the Store data from Drupal if PUDO is disabled and the store
    // is a collection point.
    if (!isCollectionPointEnabled && isCollectionPoint(store)) {
      return;
    }
    storeInfoPromises.push(getStoreInfo(store));
  });

  try {
    stores = await Promise.all(storeInfoPromises);

    // Remove null/empty stores and stores without address.
    stores = stores.filter((value) => hasValue(value) && hasValue(value.address));

    // Sort the stores first by distance and then by rnc.
    if (stores.length > 1) {
      stores = stores
        .sort((store1, store2) => store1.distance - store2.distance)
        .sort((store1, store2) => store2.rnc_available - store1.rnc_available);
    }

    staticStoresData[staticStorageKey] = stores;
    Drupal.alshayaSpc.staticStorage.set('cartStores', staticStoresData);
    return staticStoresData[staticStorageKey];
  } catch (error) {
    logger.warning('Error occurred while fetching stores for cart id @cartId, API Response: @message.', {
      '@cartId': cartId,
      '@message': error.message,
    });
  }

  Drupal.alshayaSpc.staticStorage.set('cartStores', staticStoresData);
  return [];
};

/**
 *  Get the CnC stores for the cart.
 *
 * @param {string} lat
 *   The latitude value.
 * @param {string} lon
 *   The longitude value.
 * @param {int} cncStoresLimit
 *   The default number of stores to display.
 *
 * @returns {Promise<array>}
 *   The list of stores.
 */
const getCncStores = async (lat, lon, cncStoresLimit = 0) => {
  const cartId = window.commerceBackend.getCartId();
  if (!cartId) {
    logger.warning('Error while fetching click and collect stores. No cart available in session.');
    return getFormattedError(404, 'No cart in session');
  }

  if (!lat || !lon) {
    logger.warning('Error while fetching CnC store for cart @cartId. One of lat/lon is not provided. Lat: @lat, Lon: @lon.', {
      '@cartId': cartId,
      '@lat': lat || '',
      '@lon': lon || '',
    });

    return [];
  }

  const response = await getCartStores(lat, lon, cncStoresLimit);

  // Data added below is to keep the response consistent with V1.
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
  if (!hasValue(address) || !hasValue(address.country_id)) {
    return null;
  }

  const result = { ...address };
  if (hasValue(address.custom_attributes)) {
    Object.keys(address.custom_attributes).forEach((item) => {
      const key = address.custom_attributes[item].attribute_code;
      const val = address.custom_attributes[item].value;
      result[key] = val;
    });
  }

  if (isArray(result.street)) {
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
  if (!hasValue(customer)) {
    return {};
  }

  const data = {
    id: 0,
    firstname: '',
    lastname: '',
    email: '',
    addresses: [],
  };

  if (hasValue(customer.id)) {
    data.id = customer.id;
  }

  if (hasValue(customer.firstname) && customer.firstname !== invisibleCharacter) {
    data.firstname = customer.firstname;
  }

  if (hasValue(customer.lastname) && customer.lastname !== invisibleCharacter) {
    data.lastname = customer.lastname;
  }

  if (hasValue(customer.email)) {
    data.email = customer.email;
  }

  if (hasValue(customer.addresses)) {
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
 * Gets customer's address ids.
 *
 * @returns {Promise<AxiosPromise<Object> | *[]>}
 *   Address ids array or empty.
 */
const getCustomerAddressIds = () => getCart()
  .then((response) => {
    if (hasValue(response)
      && hasValue(response.data)
      && hasValue(response.data.customer)
      && hasValue(response.data.customer.addresses)
    ) {
      const addresses = [];
      response.data.customer.addresses.forEach((item) => {
        addresses.push(item.customer_address_id);
      });
      return addresses;
    }
    return [];
  });

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

  if (hasValue(params.billing.id)) {
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

  if (!hasValue(shippingData)) {
    return null;
  }

  // Add carrier info.
  if (hasValue(shippingData.carrier_info)) {
    // @todo make the parameter consistent for all the cases.
    params.shipping.shipping_carrier_code = shippingData.carrier_info.code
      || shippingData.carrier_info.carrier;
    params.shipping.shipping_method_code = shippingData.carrier_info.method;
  }

  // Add customer address info.
  if (hasValue(shippingData.customer_address_id)) {
    params.shipping.shipping_address = shippingData.address;
  } else {
    params.shipping.shipping_address = formatAddressForShippingBilling(shippingData.address);
  }

  let cart = await updateCart(params);
  // If cart update has error.
  if (!hasValue(cart.data) || (hasValue(cart.data.error) && cart.data.error)) {
    return cart;
  }
  const cartData = cart.data;

  // If billing needs to updated or billing is not available added at all
  // in the cart. Assuming if name is not set in billing means billing is
  // not set. City with value 'NONE' means, that this was added in CnC
  // by default and not changed by user.
  if (updateBillingDetails
    && (!hasValue(cartData.billing_address)
      || !hasValue(cartData.billing_address.firstname)
      || cartData.billing_address.city === 'NONE')) {
    cart = await updateBilling(params.shipping.shipping_address);
  }

  // Trigger event on every shipping address update,
  // as this is the final place where shipping is updated.
  // Components can use this event to do action when shipping address is updated in cart.
  dispatchCustomEvent('onAddShippingInfoUpdate', cart);

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
        click_and_collect_type: hasValue(store.rnc_available) ? 'reserve_and_collect' : 'ship_to_store',
        store_code: store.code,
      },
    },
  };

  if (!hasValue(data.shipping.shipping_address.custom_attributes)
    && hasValue(data.shipping.shipping_address.extension_attributes)
  ) {
    const extensionAttributes = data.shipping.shipping_address.extension_attributes;
    data.shipping.shipping_address.custom_attributes = [];
    Object.keys(extensionAttributes).forEach((key) => {
      data.shipping.shipping_address.custom_attributes.push(
        {
          attribute_code: key,
          value: extensionAttributes[key],
        },
      );
    });
  }

  // Validate address.
  // @todo check if this is valid, it's same as middleware
  // but it doesn't make sense to check before updating shipping.
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
  if (!hasValue(data.shipping.shipping_address.extension_attributes)) {
    return false;
  }

  let cart = await updateCart(data);
  if (!hasValue(cart.data) || hasValue(cart.data.error)) {
    return false;
  }

  // If billing address not contains proper data (extension info).
  if (!hasValue(billing.extension_attributes)) {
    logger.warning('Billing address does not have extension attributes. Address: @address Cart: @cartId', {
      '@address': JSON.stringify(billing),
      '@cartId': JSON.stringify(window.commerceBackend.getCartId()),
    });

    return false;
  }

  // Not use/assign default billing address if customer_address_id
  // is not available.
  if (!hasValue(billing.customer_address_id)) {
    logger.warning('Billing address does not have customer_address_id. Address: @address Cart: @cartId', {
      '@address': JSON.stringify(billing),
      '@cartId': JSON.stringify(window.commerceBackend.getCartId()),
    });

    return cart;
  }

  // Return if address id from last order doesn't
  // exist in customer's address id list.
  const customerAddressIds = await getCustomerAddressIds();
  if (!_.contains(customerAddressIds, billing.customer_address_id)) {
    logger.warning('Billing address not available in customer address book now. Address: @address Cart: @cartId', {
      '@address': JSON.stringify(billing),
      '@cartId': JSON.stringify(window.commerceBackend.getCartId()),
    });

    return cart;
  }

  // Add log for billing data we pass to magento update cart.
  logger.debug('Billing update default for CNC. Address: @address Cart: @cartId', {
    '@address': JSON.stringify(billing),
    '@cartId': JSON.stringify(window.commerceBackend.getCartId()),
  });

  cart = await updateBilling(billing);
  // If billing update has error.
  if (hasValue(cart.data.error) && cart.data.error) {
    return false;
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
    customer_address_id: 0,
    address,
    carrier_info: {
      code: method.carrier_code,
      method: method.method_code,
    },
  };

  // Set customer address id.
  if (hasValue(address.id)) {
    shippingData.customer_address_id = address.id;
  } else if (hasValue(address.customer_address_id)) {
    shippingData.customer_address_id = address.customer_address_id;
  }

  // Validate address.
  const valid = await validateAddressAreaCity(shippingData.address);
  if (!valid) {
    return false;
  }

  // Add log for shipping data we pass to magento update cart.
  logger.debug('Shipping update default for HD. Cart: @cartId, Data: @data.', {
    '@cartId': cartId,
    '@data': JSON.stringify(shippingData),
  });

  // If shipping address not contains proper address, don't process further.
  if (!hasValue(shippingData.address.extension_attributes)
    && !hasValue(shippingData.address.custom_attributes)
  ) {
    return false;
  }

  let updated = await addShippingInfo(shippingData, cartActions.cartShippingUpdate, false);
  if (!hasValue(updated.data) || hasValue(updated.data.error)) {
    return false;
  }

  // Set shipping methods.
  if (hasValue(updated.data) && hasValue(updated.data.shipping)
    && hasValue(shippingMethods)) {
    updated.data.shipping.methods = shippingMethods;
  }

  // Not use/assign default billing address if customer_address_id
  // is not available.
  if (!hasValue(billing.customer_address_id)) {
    return updated;
  }

  // Add log for billing data we pass to magento update cart.
  logger.notice('Billing update default for HD. Address: @address Cart: @cartId', {
    '@address': JSON.stringify(billing),
    '@cartId': cartId,
  });

  // If billing address not contains proper address, don't process further.
  if (!hasValue(billing.extension_attributes) && !hasValue(billing.custom_attributes)) {
    return updated;
  }

  updated = await updateBilling(billing);
  if (!hasValue(updated.data) || hasValue(updated.data.error)) {
    return false;
  }

  // Set shipping methods.
  if (hasValue(updated.data) && hasValue(updated.data.shipping)
    && hasValue(shippingMethods)) {
    updated.data.shipping.methods = shippingMethods;
  }

  return updated;
};

/**
 * Apply shipping from last order.
 *
 * @param {object} order
 *   Last Order details.
 *
 * @returns {Promise<object|boolean>}
 *   FALSE if something went wrong, updated cart data otherwise.
 */
const applyDefaultShipping = async (order) => {
  if (!hasValue(order.shipping) || !hasValue(order.shipping.commerce_address)) {
    return false;
  }

  const address = order.shipping.commerce_address;

  if (_.contains(order.shipping.method, 'click_and_collect')) {
    const store = await getStoreInfo({ code: order.shipping.extension_attributes.store_code });

    // We get a string value if store node is not present in Drupal. So in
    // that case we do not proceed.
    if (typeof store.lat === 'undefined' || typeof store.lng === 'undefined') {
      return false;
    }

    const availableStores = await getCartStores(store.lat, store.lng);

    const availableStoreCodes = availableStores.map((value) => value.code);

    if (_.contains(availableStoreCodes, store.code)) {
      let storeKey = '';
      availableStores.forEach((value, key) => {
        if (value.code === store.code) {
          storeKey = key;
        }
      });
      return selectCnc(availableStores[storeKey], address, order.billing_commerce_address);
    }

    return false;
  }

  if (!hasValue(address.customer_address_id)) {
    return false;
  }

  // Return false if address id from last order doesn't
  // exist in customer's address id list.
  const customerAddressIds = await getCustomerAddressIds();
  if (!_.contains(customerAddressIds, address.customer_address_id)) {
    return false;
  }

  const response = await getHomeDeliveryShippingMethods(address);
  if (!response.error) {
    const { methods } = response;
    for (let i = 0; i < methods.length; i++) {
      const method = methods[i];
      // Check if last order's method is available for the order.
      if (typeof method.carrier_code !== 'undefined'
        && order.shipping.method.indexOf(method.carrier_code, 0) === 0
        && order.shipping.method.indexOf(method.method_code, 0) !== -1
        && method.available
      ) {
        logger.debug('Setting shipping/billing address from user last HD order. Cart: @cartId, Address: @address, Billing: @billing.', {
          '@cartId': window.commerceBackend.getCartId(),
          '@address': JSON.stringify(address),
          '@billing': JSON.stringify(order.billing_commerce_address),
        });
        return selectHd(address, method, order.billing_commerce_address, methods);
      }
    }
  }

  return false;
};

/**
 * Returns first available method.
 *
 * @param {array}
 *   Methods array.
 *
 * @return {array}
 *   First available method.
 */
const firstAvailableDeliveryMethod = (methods) => methods.find(
  (element) => element.available === true,
);
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
const applyDefaults = async (data, customerId) => {
  if (hasValue(data.shipping)
    && hasValue(data.shipping.method)) {
    return data;
  }

  // Don't apply default shipping if egift card is enabled and cart contains only
  // virtual items.
  if (isEgiftCardEnabled() && cartContainsOnlyVirtualProduct(data.cart)) {
    return data;
  }

  // Get last order only for Drupal Customers.
  const order = isUserAuthenticated()
    ? await getLastOrder(customerId)
    : null;

  // Try to apply defaults from last order.
  if (hasValue(order)) {
    // If cnc order but cnc is disabled.
    if (_.contains(order.shipping.method, 'click_and_collect') && await getCncStatusForCart(data) !== true) {
      // Do nothing, we will let the address from address book used for default flow.
    } else {
      logger.debug('Applying defaults from last order. Cart: @cartId.', {
        '@cartId': window.commerceBackend.getCartId(),
      });

      const response = await applyDefaultShipping(order);
      if (hasValue(response)) {
        // If we were able to apply shipping, let's add the last payment method too
        // as default.
        response.data.payment = response.data.payment || {};
        response.data.payment.default = await getDefaultPaymentFromOrder(order);
        return response;
      }
    }
  }

  // Select default address from address book if available.
  const address = getDefaultAddress(data);
  if (address) {
    const response = await getHomeDeliveryShippingMethods(address);
    if (!response.error) {
      const { methods } = response;

      logger.debug('Setting shipping/billing address from user address book. Cart: @cartId, Address: @address.', {
        '@cartId': window.commerceBackend.getCartId(),
        '@address': JSON.stringify(address),
      });
      return selectHd(address, firstAvailableDeliveryMethod(methods), address, methods);
    }
  }

  // If address already available in cart, use it.
  if (hasValue(data.shipping.address) && hasValue(data.shipping.address.country_id)) {
    const response = await getHomeDeliveryShippingMethods(data.shipping.address);
    if (!response.error) {
      const { methods } = response;

      logger.debug('Setting shipping/billing address from user address book. Cart: @cartId, Address: @address.', {
        '@cartId': window.commerceBackend.getCartId(),
        '@address': JSON.stringify(data.shipping.address),
      });
      return selectHd(
        data.shipping.address,
        firstAvailableDeliveryMethod(methods),
        data.shipping.address,
        methods,
      );
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
  // In case of errors, return the error object.
  if (hasValue(cartData) && hasValue(cartData.error) && cartData.error) {
    return cartData;
  }

  // If the cart object is empty, return null.
  if (!hasValue(cartData)) {
    return null;
  }

  // As of now we don't need deep clone of the passed object.
  // As Method calls are storing the results on the same object.
  // For ex - cart.data = await getProcessedCheckoutData(cart.data);
  // if in future, method call is storing result on any other object.
  // Clone of the argument passed will be needed which can be achieved using.
  // let data = JSON.parse(JSON.stringify(cartData));
  let data = cartData;

  // Check whether CnC enabled or not.
  const cncStatus = await getCncStatusForCart(data);

  // Here we will do the processing of cart to make it in required format.
  const updated = await applyDefaults(data, window.drupalSettings.userDetails.customerId);
  if (updated !== false && hasValue(updated.data)) {
    data = updated.data;
  }

  if (!hasValue(data.shipping.methods)
    && hasValue(data.shipping.address)
    && hasValue(data.shipping.type) && data.shipping.type !== 'click_and_collect'
  ) {
    const response = await getHomeDeliveryShippingMethods(data.shipping.address);
    if (response.error) {
      return response;
    }
    data.shipping.methods = response.methods;
  }
  // Shipping method will not be available when egift is enabled and cart
  // contain only virtual items.
  if (!hasValue(data.payment.methods)
    && (hasValue(data.shipping.method)
    || cartContainsOnlyVirtualProduct(data.cart))
    // Don't call the select payment and get payment methods APIs when
    // full payment is done by egift card.
    // Here we are passing processed card data as we are checking the result in
    // isFullPaymentDoneByEgift on processed cart.
    && !(isFullPaymentDoneByEgift(await getProcessedCartData(data)))
  ) {
    const paymentMethods = await getPaymentMethods();
    if (hasValue(paymentMethods)) {
      data.payment.methods = paymentMethods;
    }
    data.payment.method = await getPaymentMethodSetOnCart();
  }

  // Re-use the processing done for cart page.
  const response = await getProcessedCartData(data);
  response.cnc_enabled = cncStatus;
  response.customer = getCustomerPublicData(data.customer);
  response.shipping = (typeof data.shipping !== 'undefined')
    ? data.shipping
    : [];

  if (typeof response.shipping.storeCode !== 'undefined') {
    response.shipping.storeInfo = await getStoreInfo({ code: response.shipping.storeCode });
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

  response.payment = response.payment || {};
  if (typeof response.payment.methods !== 'undefined' && response.payment.methods.length > 0) {
    const codes = response.payment.methods.map((el) => el.code);

    // If payment method is not available in the list, we set the first
    // available payment method in React, here we remove it from response.
    // `aura_payment` is pseudo payment method, it won't be in
    // the list of payment methods so do not remove payment method
    // if it's `aura_payment`.
    if (typeof response.payment.method !== 'undefined'
      && !codes.includes(response.payment.method)
      && response.payment.method !== 'aura_payment') {
      delete (response.payment.method);
    }

    // If default also has invalid payment method, we remove it
    // so that first available payment method will be selected.
    if (typeof response.payment.default !== 'undefined' && !codes.includes(response.payment.default)) {
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

  const cartId = window.commerceBackend.getCartId();
  logger.debug('Billing update manual. Cart: @cartId, Address: @address, Data: @data.', {
    '@cartId': cartId,
    '@data': JSON.stringify(billingInfo),
    '@address': JSON.stringify(billingData),
  });

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
const isUpapiPaymentMethod = (paymentMethod) => paymentMethod.indexOf('upapi', 0) !== -1;

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
 * Checks if tabby payment method.
 *
 * @param {string} paymentMethod
 *   Payment method code.
 *
 * @return {bool}
 *   TRUE if payment methods from tabby
 */
const isTabbyPaymentMethod = (paymentMethod) => paymentMethod.indexOf('tabby', 0) !== -1;

/**
 * Checks if tamara payment method.
 *
 * @param {string} paymentMethod
 *   Payment method code.
 *
 * @return {bool}
 *   TRUE if payment methods from tamara
 */
const isTamaraPaymentMethod = (paymentMethod) => paymentMethod.indexOf('tamara', 0) !== -1;

/**
 * Prepare message to log when API fail after payment successful.
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
  let orderId = '';

  if (hasValue(cart.cart)
    && hasValue(cart.cart.extension_attributes)
    && hasValue(cart.cart.extension_attributes.real_reserved_order_id)) {
    orderId = cart.cart.extension_attributes.real_reserved_order_id;
  }

  const message = [];
  message.push(`exception:${exceptionMessage}`);
  message.push(`api:${api}`);
  message.push(`double_check_done:${doubleCheckDone}`);
  message.push(`order_id:${orderId}`);

  if (hasValue(cart.cart)) {
    message.push(`cart_id:${cart.cart.id}`);
    message.push(`amount_paid:${cart.totals.base_grand_total}`);
  }

  let paymentMethod = '';
  if (hasValue(data.method)) {
    paymentMethod = data.method;
  } else if (hasValue(data.paymentMethod.method)) {
    paymentMethod = data.paymentMethod.method;
  }
  message.push(`payment_method:.${paymentMethod}`);

  let additionalInfo = '';
  if (hasValue(data.paymentMethod) && hasValue(data.paymentMethod.additional_data)) {
    additionalInfo = JSON.stringify(data.paymentMethod.additional_data);
  } else if (hasValue(data.additional_data)) {
    additionalInfo = JSON.stringify(data.additional_data);
  }
  message.push(`additional_information:${additionalInfo}`);

  if (hasValue(cart.shipping) && hasValue(cart.shipping.method)) {
    message.push(`shipping_method:${cart.shipping.method}`);
    _.each(cart.shipping.custom_attributes, (value) => {
      message.push(`${value.attribute_code}:${value.value}`);
    });
  }

  return hasValue(message) ? message.join('||') : '';
};

/**
 * Fetches the list of click and collect stores.
 *
 * @param {object} coords
 *   The co-ordinates data.
 * @param {int} cncStoresLimit
 *   The default number of stores to display.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.fetchClickNCollectStores = (coords, cncStoresLimit = 0) => getCncStores(
  coords.lat, coords.lng, cncStoresLimit,
);

/**
 * Process payment data before placing order.
 *
 * @param paymentData
 * @param data
 * @returns {{cvv: string, public_hash: string}}
 */
const processPaymentData = (paymentData, data) => {
  const additionalInfo = data;

  // Method specific code.
  switch (paymentData.method) {
    case 'checkout_com_upapi':
      switch (additionalInfo.card_type) {
        case 'new':
          additionalInfo.is_active_payment_token_enabler = parseInt(additionalInfo.save_card, 10);
          break;

        case 'existing': {
          const { cvvCheck } = drupalSettings.checkoutComUpapi;
          const { cvv, id } = additionalInfo;

          if (cvvCheck && !hasValue(cvv)) {
            return {
              data: {
                error: true,
                error_code: cartErrorCodes.cartOrderPlacementError,
                message: 'CVV missing for credit/debit card.',
              },
            };
          }

          if (!hasValue(id)) {
            return {
              data: {
                error: true,
                error_code: cartErrorCodes.cartOrderPlacementError,
                message: 'Invalid card token.',
              },
            };
          }

          additionalInfo.public_hash = atob(id);

          if (cvvCheck) {
            additionalInfo.cvv = atob(decodeURIComponent(cvv));
          }

          break;
        }

        // no default
      }
      break;

    // no default
  }

  return additionalInfo;
};

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
  Drupal.alshayaSpc.staticStorage.remove('payment_method');

  const paymentData = data.payment_info.payment;
  const params = {
    extension: {
      action: cartActions.cartPaymentUpdate,
    },
    payment: {
      method: paymentData.method,
      additional_data: (hasValue(paymentData.additional_data))
        ? paymentData.additional_data
        : {},
    },
  };

  if (hasValue(data.payment_info)
    && hasValue(data.payment_info.payment)
    && hasValue(data.payment_info.payment.analytics)
  ) {
    const analyticsData = data.payment_info.payment.analytics;

    params.extension.ga_client_id = '';
    if (hasValue(analyticsData.clientID) && hasValue(analyticsData.clientID)) {
      params.extension.ga_client_id = analyticsData.clientID;
    }

    params.extension.tracking_id = '';
    if (hasValue(analyticsData.trackingId) && hasValue(analyticsData.trackingId)) {
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
  if (isUpapiPaymentMethod(paymentData.method)
    || isPostpayPaymentMethod(paymentData.method)
    || isTabbyPaymentMethod(paymentData.method)
    || isTamaraPaymentMethod(paymentData.method)) {
    // Add success and fail redirect url to additional data.
    params.payment.additional_data.successUrl = `${window.location.origin}${Drupal.url('spc/payment-callback/success')}`;
    params.payment.additional_data.failUrl = `${window.location.origin}${Drupal.url(`spc/payment-callback/${paymentData.method}/error`)}`;
  }

  // Process payment data by paymentMethod.
  const processedData = processPaymentData(paymentData, params.payment.additional_data);
  if (typeof processedData.data !== 'undefined' && processedData.data.error) {
    logger.warning('Error while processing payment data. Error message: @message cart: @cart payment method: @paymentMethod', {
      '@message': processedData.data.message,
      '@cart': JSON.stringify(await window.commerceBackend.getCart()),
      '@paymentMethod': paymentData.method,
    });
    return processedData;
  }
  params.payment.additional_data = processedData;

  // Additional changes for VAULT.
  switch (paymentData.method) {
    case 'checkout_com_upapi':
      if (hasValue(params.payment.additional_data.public_hash)) {
        params.payment.method = checkoutComUpapiVaultMethod();
      }
      break;

    case 'checkout_com':
      if (hasValue(params.payment.additional_data.public_hash)) {
        params.payment.method = checkoutComVaultMethod();
      }
      break;

    default:
  }

  const cartId = window.commerceBackend.getCartId();
  logger.notice('Calling update payment for payment_update. Cart id: @cartId Method: @paymentMethod Data: @data.', {
    '@cartId': cartId,
    '@paymentMethod': paymentData.method,
    '@data': JSON.stringify(paymentData),
  });

  const oldCart = await getCart();
  const cart = await updateCart(params);
  if (!hasValue(cart.data) || (hasValue(cart.data.error) && cart.data.error)) {
    const errorMessage = (cart.data.error_code > 600) ? 'Back-end system is down' : cart.data.error_message;
    cart.data.message = errorMessage;
    const message = prepareOrderFailedMessage(oldCart.data, paymentData, errorMessage, 'update cart', 'NA');
    logger.warning('Error occurred while placing order. Error: @message', {
      '@message': message,
    });

    return cart;
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
  const { country_code: countryCode, site_code: siteCode } = getCartSettings('siteInfo');
  const addressFields = getCartSettings('addressFields');

  // Use default value first if available.
  if (hasValue(countryCode)) {
    if (hasValue(addressFields.default[countryCode])) {
      addressFieldsToValidate = addressFields.default[countryCode];
    }

    if (hasValue(siteCode)) {
      // If brand specific value available/override.
      if (hasValue(addressFields[siteCode])
        && hasValue(addressFields[siteCode][countryCode])
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
 * @return {boolean}
 *   FALSE if empty field value.
 */
const isAddressExtensionAttributesValid = (data) => {
  let isValid = true;

  // If there are address fields available for validation
  // in drupal settings.
  const addressFieldsToValidate = cartAddressFieldsToValidate();

  if (!hasValue(addressFieldsToValidate)) {
    return isValid;
  }

  const cartAddressCustom = [];

  if (hasValue(data.shipping.address)
    && hasValue(data.shipping.address.custom_attributes)) {
    // Prepare cart address field data.
    data.shipping.address.custom_attributes.forEach((item) => {
      cartAddressCustom[item.attribute_code] = item.value;
    });
  }

  // Check each required field in custom attributes available in cart
  // shipping address or not.
  addressFieldsToValidate.forEach((field) => {
    // If field not exists or empty.
    if (!hasValue(cartAddressCustom[field])) {
      logger.error('Field: @field not available in cart shipping address. Cart id: @cartId', {
        '@field': field,
        '@cartId': window.commerceBackend.getCartId(),
      });

      isValid = false;
    }
  });

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
  if (!hasValue(cart) || !hasValue(cart.data)) {
    return false;
  }
  const cartData = cart.data;

  let isError = false;
  let errorMessage = 'Delivery Information is incomplete. Please update and try again.';
  let errorCode = cartErrorCodes.cartOrderPlacementError;

  if (isObject(cartData) && isCartHasOosItem(cartData)) {
    isError = true;
    logger.warning('Error while finalizing payment. Cart has an OOS item. Cart: @cart.', {
      '@cart': JSON.stringify(cartData),
    });

    Object.keys(cartData.cart.items).forEach((key) => {
      matchStockQuantity(cartData.cart.items[key].sku);
    });

    errorMessage = 'Cart contains some items which are not in stock.';
    errorCode = cartErrorCodes.cartHasOOSItem;
  } else if (!hasValue(cartData.shipping.method)
    && !cartContainsOnlyVirtualProduct(cartData.cart)
  ) {
    // Check if shipping method is present else throw error.
    isError = true;
    logger.error('Error while finalizing payment. No shipping method available. Cart: @cart.', {
      '@cart': JSON.stringify(cartData),
    });
  } else if ((!hasValue(cartData.shipping.address)
    || !hasValue(cartData.shipping.address.custom_attributes))
    && !cartContainsOnlyVirtualProduct(cartData.cart)
  ) {
    // If shipping address not have custom attributes.
    isError = true;
    logger.error('Error while finalizing payment. Shipping address not contains all info. Cart: @cart.', {
      '@cart': JSON.stringify(cartData),
    });
  } else if (!isAddressExtensionAttributesValid(cartData)
    && !cartContainsOnlyVirtualProduct(cartData.cart)) {
    // If address extension attributes doesn't contain all the required
    // fields or required field value is empty, not process/place order.
    isError = true;
    logger.error('Error while finalizing payment. Shipping address not contains all required extension attributes. Cart: @cart.', {
      '@cart': JSON.stringify(cartData),
    });
  } else if ((!hasValue(cartData.shipping.address)
    || !hasValue(cartData.shipping.address.firstname)
    || !hasValue(cartData.shipping.address.lastname))
    && !cartContainsOnlyVirtualProduct(cartData.cart)
  ) {
    // If first/last name not available in shipping address.
    isError = true;
    logger.error('Error while finalizing payment. First name or Last name not available in cart for shipping address. Cart: @cart.', {
      '@cart': JSON.stringify(cartData),
    });
  } else if (!hasValue(cartData.cart.billing_address.firstname)
    || !hasValue(cartData.cart.billing_address.lastname)
  ) {
    // If first/last name not available in billing address.
    isError = true;
    logger.error('Error while finalizing payment. First name or Last name not available in cart for billing address. Cart: @cart.', {
      '@cart': JSON.stringify(cartData),
    });
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
    if (hasValue(response.data)
      && hasValue(response.data.error) && response.data.error
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
window.commerceBackend.getCartForCheckout = async () => {
  logger.debug('Loading cart data for checkout page.');

  return getCart()
    .then(async (response) => {
      const cart = response;

      // Check if response itself is empty.
      // This could happen for multiple reasons,
      // For example isAnonymousUserWithoutCart or we got 404.
      if (!hasValue(cart) || !hasValue(cart.data)) {
        logger.warning('Empty response received for getCart API call.');

        return {
          data: {
            error: true,
            error_code: 500,
            error_message: 'Empty response received.',
          },
        };
      }

      const cartId = window.commerceBackend.getCartId();

      if (hasValue(cart.data.error_message)) {
        logger.error('Error while getting cart: @cartId, Error: @message.', {
          '@cartId': cartId,
          '@message': cart.data.error_message,
        });
        return cart.data;
      }

      if (!hasValue(cart.data.cart) || !hasValue(cart.data.cart.items)) {
        logger.warning('Checkout accessed without items in cart for id: @cartId', {
          '@cartId': cartId,
        });

        return {
          data: {
            error: true,
            error_code: 500,
            error_message: 'Checkout accessed without items in cart',
          },
        };
      }

      cart.data = await getProcessedCheckoutData(cart.data);
      return cart;
    })
    .catch((error) => {
      logger.error('Error while getCartForCheckout controller. Error: @message. Code: @responseCode.', {
        '@message': error.message,
        '@responseCode': error.status,
      });

      return {
        data: {
          error: true,
          error_code: error.status,
          error_message: getDefaultErrorMessage(),
        },
      };
    });
};

/**
 * Add click n collect shipping on the cart.
 *
 * @param {object} shippingData
 *   Shipping address info.
 * @param {string} action
 *   Action to perform.
 *
 * @returns {Promise<object>}
 *   Cart data.
 * */
const addCncShippingInfo = async (shippingData, action) => {
  const { store } = { ...shippingData };

  // Create the address object with static data and store cart address.
  let address = {
    static: {
      ...shippingData.store.cart_address,
      ...shippingData.static,
    },
  };

  // Move extension data to static fields.
  if (hasValue(address.static.extension)) {
    address = { ...address, ...address.static.extension };
    delete address.static.extension;
  }

  // Move street to the root.
  if (hasValue(address.static.street)) {
    address.street = address.static.street;
    delete address.static.street;
  }

  const params = {
    extension: {
      action,
    },
    shipping: {
      shipping_address: formatAddressForShippingBilling(address),
      shipping_carrier_code: shippingData.carrier_info.code,
      shipping_method_code: shippingData.carrier_info.method,
      extension_attributes: {
        click_and_collect_type: hasValue(store.rnc_available) ? 'reserve_and_collect' : 'ship_to_store',
        store_code: store.code,
      },
    },
  };

  let cart = await updateCart(params);
  // If cart update has error.
  if (!hasValue(cart.data)
    || (hasValue(cart.data.error) && cart.data.error)
    || (hasValue(cart.data.response_message) && cart.data.response_message === 'json_error')
  ) {
    return cart;
  }

  // Setting city value as 'NONE' so that, we can
  // identify if billing address added is default one and
  // not actually added by the customer on FE.
  if (!hasValue(cart.data.cart.billing_address)
    || !hasValue(cart.data.cart.billing_address.city)
    || cart.data.cart.billing_address.city === 'NONE'
  ) {
    params.shipping.shipping_address.city = 'NONE';
    // Adding billing address.
    cart = await updateBilling(params.shipping.shipping_address);
  }

  return cart;
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

  const type = (hasValue(shippingInfo.shipping_type))
    ? shippingInfo.shipping_type
    : 'home_delivery';

  if (type === 'click_and_collect') {
    // Unset as not needed in further processing.
    delete (shippingInfo.shipping_type);

    logger.notice('Shipping update manual for CNC. Data: @data Address: @address Cart: @cartId', {
      '@data': JSON.stringify(data),
      '@address': JSON.stringify(shippingInfo),
      '@cartId': cartId,
    });

    cart = await addCncShippingInfo(shippingInfo, data.action);

    // Process cart data.
    cart.data = await getProcessedCheckoutData(cart.data);

    return cart;
  }

  // Shipping address.
  const shippingAddress = formatAddressForShippingBilling(shippingInfo);

  // Shipping methods.
  const response = await getHomeDeliveryShippingMethods(shippingAddress);
  if (response.error) {
    logger.notice('Error while shipping update manual for HD. Data: @data Cart: @cartId Error message: @message', {
      '@data': JSON.stringify(data),
      '@cartId': cartId,
      '@message': response.error_message,
    });

    return { data: response };
  }

  const shippingMethods = response.methods;

  let carrierInfo = {};
  if (hasValue(shippingInfo.carrier_info)) {
    carrierInfo = shippingInfo.carrier_info;
  }

  if (!hasValue(carrierInfo)) {
    // Find the first available method.
    const selectedMethod = shippingMethods.find(
      (method) => method.available === true,
    );
    if (selectedMethod && selectedMethod !== null) {
      carrierInfo = {
        code: selectedMethod.carrier_code,
        method: selectedMethod.method_code,
      };
    }
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

  if (hasValue(cart.data) && hasValue(cart.data.shipping) && hasValue(shippingMethods)) {
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
 */
const triggerCheckoutEvent = (event, data) => {
  const params = new URLSearchParams();
  Object.entries(data).forEach(([key, value]) => {
    let stringVal = value;
    if (typeof value === 'object') {
      try {
        stringVal = JSON.stringify(value);
      } catch (e) {
        logger.warning('Could not stringify object for checkout: @data.', {
          '@data': value,
        });
      }
    }
    params.append(key, stringVal);
  });
  params.append('action', event);

  const retVal = navigator.sendBeacon(
    Drupal.url('spc/checkout-event'),
    params,
  );

  if (!retVal) {
    logger.error('Error occurred while triggering checkout event @event.', {
      '@event': event,
    });
  }
};

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
const processPostOrderPlaced = async (cart, orderId, paymentMethod) => {
  let customerId = '';
  if (hasValue(cart.data.cart.customer)
    && hasValue(cart.data.cart.customer.id)) {
    customerId = cart.data.cart.customer.id;
  }

  // Remove card id if it was not a topup purchase.
  if (getTopUpQuote() === null) {
    // Remove cart id and other caches from session.
    window.commerceBackend.removeCartDataFromStorage(true);
  }

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
  // If request is from SocialAuth Popup, restrict further processing.
  // we don't want magento API calls happen on popup, As this is causing issues
  // in processing parent pages.
  if (isRequestFromSocialAuthPopup()) {
    return false;
  }

  const cart = await getCart(true);
  if (!hasValue(cart) || !hasValue(cart.data)) {
    return false;
  }

  if (isObject(cart) && isCartHasOosItem(cart.data)) {
    logger.warning('Error while placing order. Cart has an OOS item. Cart: @cart', {
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
  // Skip the shipping and billing address validation when egift card is enabled
  // and cart contain only virtual products.
  const egiftWithVirtualProduct = cartContainsOnlyVirtualProduct(cart.data.cart);
  if (!hasValue(cart.data.shipping.method)
  && !egiftWithVirtualProduct) {
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
  if (hasValue(cart.data.shipping.address)
    && !hasValue(cart.data.shipping.address.custom_attributes)
    && !egiftWithVirtualProduct) {
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

  if (!isAddressExtensionAttributesValid(cart.data)
    && !egiftWithVirtualProduct) {
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
  if (hasValue(cart.data.shipping.address)
    && (!hasValue(cart.data.shipping.address.firstname)
    || !hasValue(cart.data.shipping.address.lastname))
    && !egiftWithVirtualProduct) {
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
  if (!hasValue(cart.data.cart.billing_address.firstname)
    || !hasValue(cart.data.cart.billing_address.lastname)) {
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

  logger.notice('Invoking place order API now with the following values: masked_cart_id: @maskedCartId, cartId: @cartId, customerId: @customerId, paymentMethod: @paymentMethod, deliveryMethod: @deliveryMethod, totalAmount: @totalAmount', {
    '@maskedCartId': window.commerceBackend.getCartId(),
    '@cartId': cart.data.cart.id,
    '@customerId': window.drupalSettings.userDetails.customerId,
    '@paymentMethod': data.data.paymentMethod.method,
    '@deliveryType': cart.data.shipping.type,
    '@deliveryMethod': cart.data.shipping.method,
    '@totalAmount': cart.data.totals.base_grand_total,
  });

  // As we are using guest cart update in case of Topup, we will not use
  // bearerToken.
  const useBearerToken = (getTopUpQuote() === null);
  return callMagentoApi(getApiEndpoint('placeOrder', params), 'PUT', null, useBearerToken)
    .then(async (response) => {
      const result = {
        success: true,
        isAbsoluteUrl: false,
      };

      if (hasValue(response.data) && hasValue(response.data.redirect_url)) {
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

      if (!hasValue(response.data)) {
        logger.warning('Got empty response while placing the order.');
        return response;
      }

      let orderId = parseInt(response.data, 10);

      // In case of errors, double check the order.
      if (hasValue(response.data.error)
        && response.data.error_code >= 500
        && getCartSettings('doubleCheckEnabled')
        && !isPostpayPaymentMethod(data.data.paymentMethod.method)
        && !isUpapiPaymentMethod(data.data.paymentMethod.method)
      ) {
        // For authenticated user, we have a different approach to validate the
        // order.
        if (isUserAuthenticated()) {
          // Get quote_id from last order to compare with cart id.
          const lastOrder = await getLastOrder(drupalSettings.userDetails.customerId, true);
          if (hasValue(lastOrder)
            && hasValue(lastOrder.quote_id)
            && lastOrder.quote_id === cart.data.cart.id
          ) {
            orderId = lastOrder.order_id;
            // We were able to match the last order id with the cart submitted.
            logger.warning('Place order failed but order was placed, we will move forward. Message: @message. Reserved order id: @reservedOrderId. Cart id: @cartId', {
              '@cartId': cart.data.cart.id,
              '@message': (hasValue(response.data.error_message)) ? response.data.error_message : '',
              '@reservedOrderId': (hasValue(cart.data.cart.reserved_order_id)) ? cart.data.cart.reserved_order_id : '',
            });
          }
        } else {
          // Check if the order is already placed.
          const orderPlaced = await validateOrder(params.cartId);
          if (hasValue(orderPlaced)
            && hasValue(orderPlaced.quote_id)) {
            orderId = orderPlaced.order_id;
            // We were able to match the last order id with the cart submitted.
            logger.warning('Place order failed but order was placed, we will move forward. Message: @message. Reserved order id: @reservedOrderId. Cart id: @cartId', {
              '@cartId': cart.data.cart.id,
              '@message': (hasValue(response.data.error_message)) ? response.data.error_message : '',
              '@reservedOrderId': (hasValue(cart.data.cart.reserved_order_id)) ? cart.data.cart.reserved_order_id : '',
            });
          }
        }
      }

      if (!orderId || Number.isNaN(orderId)) {
        logger.error('Place order failed. Response: @response, Cart: @cart.', {
          '@response': JSON.stringify(response),
          '@cart': JSON.stringify(cart),
        });

        // If response already has error details return them as is.
        if (hasValue(response.data.error)) {
          return response;
        }
        result.error = true;
        result.error_code = 604;
        result.success = false;
        result.error_message = getDefaultErrorMessage();
        return { data: result };
      }

      // Proceed with checkout.
      const secureOrderId = btoa(JSON.stringify({
        order_id: orderId,
        email: cart.data.cart.billing_address.email,
      }));

      // Operations post order placed.
      await processPostOrderPlaced(cart, orderId, data.data.paymentMethod.method);

      result.redirectUrl = `checkout/confirmation?oid=${secureOrderId}}`;

      logger.notice('Order placed successfully. Cart: @cart OrderId: @orderId, Payment Method: @paymentMethod.', {
        '@cart': JSON.stringify(cart),
        '@orderId': orderId,
        '@paymentMethod': data.data.paymentMethod.method,
      });

      return { data: result };
    })
    .catch((response) => {
      logger.error('Error while placing order. Error message: @message, Code: @errorCode.', {
        '@message': hasValue(response.error) ? response.error.message : response,
        '@errorCode': hasValue(response.error) ? response.error.error_code : '',
      });

      // @todo all the error handling.

      const message = prepareOrderFailedMessage(cart.data, data.data, response, 'place order', 'no');
      logger.error('Error while placing order. Error message: @message', {
        '@message': message,
      });

      return response;
    });
};

export {
  getProcessedCheckoutData,
  getCncStores,
};
