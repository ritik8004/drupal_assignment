import Axios from 'axios';
import qs from 'qs';
import _ from 'lodash';
import { getApiEndpoint, isUserAuthenticated, logger } from './utility';
import { cartErrorCodes, getDefaultErrorMessage } from './error';
import { removeStorageInfo } from '../../utilities/storage';
import { cartActions } from './cart_actions';

window.commerceBackend = window.commerceBackend || {};

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

// Contains the raw unprocessed cart data.
let rawCartData = null;

/**
 * Stores the raw cart data object into the storage.
 *
 * @param {object} data
 *   The raw cart data object.
 */
window.commerceBackend.setRawCartDataInStorage = (data) => {
  rawCartData = data;
};

/**
 * Fetches the raw cart data object from the static storage.
 */
window.commerceBackend.getRawCartDataFromStorage = () => rawCartData;

/**
 * Object to serve as static cache for processed cart data over the course of a
 * request.
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
 * Global constants.
 */

// Magento method, to set for 2d vault (tokenized card) transaction.
// @See CHECKOUT_COM_VAULT_METHOD in \App\Service\CheckoutCom\APIWrapper
const checkoutComVaultMethod = () => 'checkout_com_cc_vault';

// Magento method, to append for UAPAPI vault (tokenized card) transaction.
// @See CHECKOUT_COM_UPAPI_VAULT_METHOD in \App\Service\CheckoutCom\APIWrapper
const checkoutComUpapiVaultMethod = () => 'checkout_com_upapi_vault';

/**
 * Check if user is anonymous and without cart.
 *
 * @returns bool
 */
const isAnonymousUserWithoutCart = () => {
  const cartId = window.commerceBackend.getCartId();
  if (cartId === null || typeof cartId === 'undefined') {
    if (window.drupalSettings.user.uid === 0) {
      return true;
    }
  }
  return false;
};

/**
 * Wrapper to get cart settings.
 *
 * @param {string} key
 *   The key for the configuration.
 * @returns {(number|string!Object!Array)}
 *   Returns the configuration.
 */
const getCartSettings = (key) => window.drupalSettings.cart[key];

/**
 * Get the complete path for the Magento API.
 *
 * @param {string} path
 *  The API path.
 */
const i18nMagentoUrl = (path) => `${getCartSettings('url')}/${getCartSettings('store')}${path}`;

/**
 * Handle errors and messages.
 *
 * @param {Promise} apiResponse
 *   The response from the API.
 *
 * @returns {Promise}
 *   Returns a promise object.
 */
const handleResponse = (apiResponse) => {
  // Deep clone the response object.
  const response = JSON.parse(JSON.stringify(apiResponse));
  // In case we don't receive any response data.
  if (typeof response.data === 'undefined' || response.data.length === 0) {
    logger.error(`Error while doing MDC api. Response result is empty. Status code: ${response.status}`);

    const error = {
      data: {
        error: true,
        error_code: 500,
        error_message: getDefaultErrorMessage(),
      },
    };
    return new Promise((resolve) => resolve(error));
  }

  // Treat each status code.
  if (response.status >= 500) {
    // Server error responses.
    response.data.error = true;
    response.data.error_code = 600;
    response.data.error_message = 'Back-end system is down';
    //
  } else if (response.status === 404) {
    // Client error responses.
    response.data.error = true;
    response.data.error_code = 404;
    response.data.error_message = response.data.message;
    //
  } else if (response.status !== 200) {
    // All other responses.
    response.data.error = true;
    if (typeof response.data.message !== 'undefined') {
      response.data.error_message = response.data.message;
      const errorCode = (typeof response.data.error_code !== 'undefined') ? response.data.error_code : '-';
      logger.error(`Error while doing MDC api call. Error message: ${response.data.message}, Code: ${errorCode}, Response code: ${response.status}.`);

      if (response.status === 400 && typeof response.data.error_code !== 'undefined' && response.data.error_code === cartErrorCodes.cartCheckoutQuantityMismatch) {
        response.data.error_code = cartErrorCodes.cartCheckoutQuantityMismatch;
      } else {
        response.data.error_code = 500;
      }
    }
  } else if (typeof response.data.messages !== 'undefined' && typeof response.data.messages.error !== 'undefined') {
    const error = response.data.messages.error.shift();
    //
    delete (response.data.messages);
    response.data.error = true;
    response.data.error_code = error.code;
    response.data.error_message = error.message;
    //
    logger.error(`Error while doing MDC api call. Error message: ${error.message}`);
  }
  return response;
};

/**
 * Get magento customer token.
 *
 * @returns {string}
 */
const getCustomerToken = () => {
  const token = localStorage.getItem('magento_customer_token');
  if (typeof token === 'undefined' || token === false || token === '') {
    logger.error(`Magento customer token is not set for user ${drupalSettings.user.uid}`);
  }
  return token;
};

/**
 * Make an AJAX call to Magento API.
 *
 * @param {string} url
 *   The url to send the request to.
 * @param {string} method
 *   The request method.
 * @param {object} data
 *   The object to send for POST request.
 *
 * @returns {Promise}
 *   Returns a promise object.
 */
const callMagentoApi = (url, method, data) => {
  const params = {
    url: i18nMagentoUrl(url),
    method,
    headers: {
      'Content-Type': 'application/json',
      'Alshaya-Channel': 'web',
    },
  };

  if (isUserAuthenticated()) {
    params.headers.Authorization = `Bearer ${getCustomerToken()}`;
  }

  if (typeof data !== 'undefined' && data && Object.keys(data).length > 0) {
    params.data = data;
  }

  return Axios(params)
    .then((response) => handleResponse(response))
    .catch((error) => {
      if (error.response) {
        // The request was made and the server responded with a status code
        // that falls out of the range of 2xx
        return handleResponse(error.response);
      }
      if (error.request) {
        // The request was made but no response was received
        return handleResponse(error.request);
      }
      // Something happened in setting up the request that triggered an Error
      return logger.error(error.message);
    });
};

/**
 * Make an AJAX call to Drupal API.
 *
 * @param {string} url
 *   The url to send the request to.
 * @param {string} method
 *   The request method.
 * @param {string} requestOptions
 *   The request options.
 *
 * @returns {Promise}
 *   Returns a promise object.
 */
const callDrupalApi = (url, method, requestOptions) => {
  const headers = {};
  const params = {
    url: `/${window.drupalSettings.path.currentLanguage}${url}`,
    method,
  };

  if (typeof requestOptions !== 'undefined' && requestOptions && Object.keys(requestOptions).length > 0) {
    Object.keys(requestOptions).forEach((optionName) => {
      if (optionName === 'form_params') {
        headers['Content-Type'] = 'application/x-www-form-urlencoded';
        params.data = qs.stringify(requestOptions[optionName]);
        return;
      }
      params[optionName] = requestOptions[optionName];
    });
  }

  return Axios(params);
};

/**
 * Format the cart data to have better structured array.
 *
 * @param {object} cartData
 *   Cart response from Magento.
 *
 * @return {object}
 *   Formatted / processed cart.
 */
const formatCart = (cartData) => {
  const data = _.cloneDeep(cartData);

  // Check if there is no cart data.
  if (_.isUndefined(data.cart) || !_.isObject(data.cart)) {
    return data;
  }

  // Move customer data to root level.
  if (!_.isEmpty(data.cart.customer)) {
    data.customer = data.cart.customer;
    delete data.cart.customer;
  }

  // Format addresses.
  if (!_.isEmpty(data.customer) && !_.isEmpty(data.customer.addresses)) {
    data.customer.addresses = data.customer.addresses.map((address) => {
      const item = { ...address };
      delete item.id;
      item.region = address.region_id;
      item.customer_address_id = address.id;
      return item;
    });
  }

  // Format shipping info.
  if (!_.isEmpty(data.cart.extension_attributes)) {
    if (!_.isEmpty(data.cart.extension_attributes.shipping_assignments)) {
      if (!_.isEmpty(data.cart.extension_attributes.shipping_assignments[0].shipping)) {
        data.shipping = data.cart.extension_attributes.shipping_assignments[0].shipping;
        delete data.cart.extension_attributes.shipping_assignments;
      }
    }
  } else {
    data.shipping = {};
  }

  let shippingMethod = '';
  if (!_.isEmpty(data.shipping)) {
    if (!_.isEmpty(data.shipping.method)) {
      shippingMethod = data.shipping.method;
    }
    if (!_.isEmpty(shippingMethod) && shippingMethod.indexOf('click_and_collect') >= 0) {
      data.shipping.type = 'click_and_collect';
    } else {
      data.shipping.type = 'home_delivery';
    }
  }

  if (!_.isEmpty(data.shipping) && !_.isEmpty(data.shipping.extension_attributes)) {
    const extensionAttributes = data.shipping.extension_attributes;
    if (!_.isEmpty(extensionAttributes.click_and_collect_type)) {
      data.shipping.clickCollectType = extensionAttributes.click_and_collect_type;
    }
    if (!_.isEmpty(extensionAttributes.store_code)) {
      data.shipping.storeCode = extensionAttributes.store_code;
    }
    delete data.shipping.extension_attributes;
  }

  // Initialise payment data holder.
  data.payment = {};

  // When shipping method is empty, Set shipping and billing info to empty,
  // so that we can show empty shipping and billing component in react
  // to allow users to fill addresses.
  if (shippingMethod === '') {
    data.shipping = {};
    data.cart.billing_address = {};
  }
  return data;
};

/**
 * Transforms cart data to match the data structure from middleware.
 *
 * @param {object} cartData
 *   The cart data object.
 */
const getProcessedCartData = (cartData) => {
  if (typeof cartData === 'undefined' || typeof cartData.cart === 'undefined') {
    return null;
  }

  const data = {
    cart_id: window.commerceBackend.getCartId(),
    uid: (window.drupalSettings.user.uid) ? window.drupalSettings.user.uid : 0,
    langcode: window.drupalSettings.path.currentLanguage,
    customer: cartData.customer,
    coupon_code: typeof cartData.totals.coupon_code !== 'undefined' ? cartData.totals.coupon_code : '',
    appliedRules: cartData.cart.applied_rule_ids,
    items_qty: cartData.cart.items_qty,
    cart_total: 0,
    minicart_total: 0,
    surcharge: cartData.cart.extension_attributes.surcharge,
    response_message: null,
    in_stock: true,
    is_error: false,
    stale_cart: (typeof cartData.stale_cart !== 'undefined') ? cartData.stale_cart : false,
    totals: {
      subtotal_incl_tax: cartData.totals.subtotal_incl_tax,
      shipping_incl_tax: null,
      base_grand_total: cartData.totals.base_grand_total,
      base_grand_total_without_surcharge: cartData.totals.base_grand_total,
      discount_amount: cartData.totals.discount_amount,
      surcharge: 0,
    },
    items: [],
  };

  // Totals.
  if (typeof cartData.totals.base_grand_total !== 'undefined') {
    data.cart_total = cartData.totals.base_grand_total;
    data.minicart_total = cartData.totals.base_grand_total;
  }

  if (typeof cartData.shipping !== 'undefined') {
    // For click_n_collect we don't want to show this line at all.
    if (cartData.shipping.type !== 'click_and_collect') {
      data.totals.shipping_incl_tax = cartData.totals.shipping_incl_tax;
    }
  }

  if (typeof cartData.cart.extension_attributes.surcharge !== 'undefined' && cartData.cart.extension_attributes.surcharge.amount > 0 && cartData.cart.extension_attributes.surcharge.is_applied) {
    data.totals.surcharge = cartData.cart.extension_attributes.surcharge.amount;
    // We don't show surcharge amount on cart total and on mini cart.
    data.totals.base_grand_total_without_surcharge -= data.totals.surcharge;
    data.minicart_total -= data.totals.surcharge;
  }

  if (typeof cartData.response_message[1] !== 'undefined') {
    data.response_message = {
      status: cartData.response_message[1],
      msg: cartData.response_message[0],
    };
  }

  if (typeof cartData.cart.items !== 'undefined' && cartData.cart.items.length > 0) {
    data.items = {};
    cartData.cart.items.forEach((item) => {
      // @todo check why item id is different from v1 and v2 for
      // https://local.alshaya-bpae.com/en/buy-21st-century-c-1000mg-prolonged-release-110-tablets-red.html

      data.items[item.sku] = {
        id: item.item_id,
        title: item.name,
        qty: item.qty,
        price: item.price,
        sku: item.sku,
        freeItem: false,
        finalPrice: item.price,
        in_stock: true, // @todo get stock information
        stock: 99999, // @todo get stock information
      };

      if (typeof item.extension_attributes !== 'undefined') {
        if (typeof item.extension_attributes.error_message !== 'undefined') {
          data.items[item.sku].error_msg = item.extension_attributes.error_message;
          data.is_error = true;
        }

        if (typeof item.extension_attributes.promo_rule_id !== 'undefined') {
          data.items[item.sku].promoRuleId = item.extension_attributes.promo_rule_id;
        }
      }

      // This is to determine whether item to be shown free or not in cart.
      cartData.totals.items.forEach((totalItem) => {
        // If total price of item matches discount, we mark as free.
        if (item.item_id === totalItem.item_id) {
          // Final price to use.
          // For the free gift the key 'price_incl_tax' is missing.
          if (typeof totalItem.price_incl_tax !== 'undefined') {
            data.items[item.sku].finalPrice = totalItem.price_incl_tax;
          } else {
            data.items[item.sku].finalPrice = totalItem.base_price;
          }

          // Free Item is only for free gift products which are having
          // price 0, rest all are free but still via different rules.
          if (totalItem.base_price === 0 && typeof totalItem.extension_attributes !== 'undefined' && typeof totalItem.extension_attributes.amasty_promo !== 'undefined') {
            data.items[item.sku].freeItem = true;
          }
        }
      });

      // @todo Get stock data.
    });
  } else {
    data.items = [];
  }
  return data;
};

/**
 * Calls the cart get API.
 *
 * @param {boolean} force
 *   Flag for static/fresh cartData.
 *
 * @returns {Promise}
 *   A promise object.
 */
const getCart = async (force = false) => {
  if (window.commerceBackend.getRawCartDataFromStorage() !== null && !force) {
    return { data: window.commerceBackend.getRawCartDataFromStorage() };
  }

  const cartId = window.commerceBackend.getCartId();
  if (cartId === null) {
    return new Promise((resolve) => resolve(cartId));
  }

  const response = await callMagentoApi(getApiEndpoint('getCart', cartId), 'GET', {});

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

  // Format data.
  response.data = formatCart(response.data);

  // Store the formatted data.
  window.commerceBackend.setRawCartDataInStorage(response.data);

  // Return formatted cart.
  return response;
};

/**
 * Format the cart data to have better structured array.
 * This is the equivalent to CartController:getCart().
 *
 * @returns {Promise}
 *   A promise object.
 */
const getCartWithProcessedData = async () => {
  // @todo implement missing logic, see CartController:getCart().
  const response = await getCart();
  response.data = getProcessedCartData(response.data);
  return response;
};

/**
 * Return customer id from current session.
 *
 * @return {int|null}
 *   Return customer id or null.
 */
const getCartCustomerId = async () => {
  const response = await getCart();
  const cart = response.data;
  if (!_.isEmpty(cart) && !_.isEmpty(cart.customer) && !_.isUndefined(cart.customer.id)) {
    return cart.customer.id;
  }
  return null;
};

/**
 * Validate arguments and returns the respective error code.
 *
 * @param {object} request
 *  The request data.
 *
 * @returns {promise}
 *   Promise containing the error code.
 */
const validateRequestData = async (request) => {
  // Return error response if not valid data.
  // Setting custom error code for bad response so that
  // we could distinguish this error.
  if (_.isEmpty(request)) {
    logger.error('Cart update operation not containing any data. Error 500.');
    return 500;
  }

  // If action info or cart id not available.
  if (_.isEmpty(request.action)) {
    logger.error('Cart update operation not containing any action. Error 400.');
    return 400;
  }

  let actions = [
    cartActions.cartAddItem,
    cartActions.cartUpdateItem,
    cartActions.cartRemoveItem,
  ];
  if (actions.includes(request.action) && _.isUndefined(request.sku)) {
    const logData = JSON.stringify(request);
    logger.error(`Cart update operation not containing any sku. Data: ${logData}`);
    return 400;
  }

  // @todo test request data on the browser.
  actions = [
    cartActions.cartAddItem,
    cartActions.cartUpdateItem,
  ];
  if (actions.includes(request.action) && _.isUndefined(request.quantity)) {
    const logData = JSON.stringify(request);
    logger.error(`Cart update operation not containing any quantity. Data: ${logData}`);
    return 400;
  }

  // For new cart request, we don't need any further validations.
  // Or if request has cart id but cart not exist in session,
  // create new cart for the user.
  if (request.action === cartActions.cartAddItem
    && (_.isUndefined(request.cart_id) || window.commerceBackend.getCartId() === null)) {
    return 200;
  }

  // For any cart update operation, cart should be available in session.
  if (window.commerceBackend.getCartId() === null) {
    const logData = JSON.stringify(request);
    logger.error(`Trying to do cart update operation while cart is not available in session. Data: ${logData}`);
    return 404;
  }

  // Backend validation.
  const cartCustomerId = await getCartCustomerId();
  if (window.drupalSettings.userDetails.customerId > 0) {
    if (_.isNull(cartCustomerId)) {
      return 400;
    }

    // This is serious.
    if (cartCustomerId !== window.drupalSettings.userDetails.customerId) {
      logger.error(`Mismatch session customer id:${window.drupalSettings.userDetails.customerId} and card customer id:${cartCustomerId}.`);
      return 400;
    }
  }

  return 200;
};

/**
 * Runs validations before updating cart.
 *
 * @param {object} request
 *  The request data.
 *
 * @returns {int|object}
 *   Returns true if the data is valid or an object containing the error.
 */
const preUpdateValidation = async (request) => {
  const validationResponse = await validateRequestData(request);
  if (validationResponse !== 200) {
    const error = {
      data: {
        error: true,
        error_code: validationResponse,
        error_message: getDefaultErrorMessage(),
      },
    };
    return new Promise((resolve) => resolve(error));
  }
  return true;
};

/**
 * Calls the update cart API and returns the updated cart.
 *
 * @param {object} data
 *  The data to send.
 *
 * @returns {Promise}
 *   A promise object with cart data.
 */
const updateCart = async (data) => {
  const cartId = window.commerceBackend.getCartId();

  let action = '';
  if (!_.isEmpty(data.extension) && !_.isEmpty(data.extension.action)) {
    action = data.extension.action;
  }

  // Log the shipping / billing address we pass to magento.
  if (action === cartActions.cartBillingUpdate || action === cartActions.cartShippingUpdate) {
    const logData = JSON.stringify(data);
    logger.notice(`Billing / Shipping address data: ${logData}. CartId: ${cartId}`);
  }

  return callMagentoApi(`/rest/V1/guest-carts/${cartId}/updateCart`, 'POST', JSON.stringify(data))
    .then((response) => {
      if (typeof response.data.error !== 'undefined' && response.data.error) {
        return response;
      }
      // Update the cart data in storage.
      window.commerceBackend.setRawCartDataInStorage(response.data);
      return response;
    })
    .catch((response) => {
      const errorCode = response.error.error_code;
      const errorMessage = response.error.message;
      logger.error(`Error while updating cart on MDC for action ${action}. Error message: ${errorMessage}, Code: ${errorCode}`);
      // @todo add error handling, see try/catch block in Cart:updateCart().
      return response;
    });
};

/**
 * Formats the error message as required for cart.
 *
 * @param {int} code
 *   The response code.
 * @param {string} message
 *   The response message.
 */
const getFormattedError = (code, message) => ({
  error: true,
  error_code: code,
  error_message: message,
  response_message: [message, 'error'],
});

export {
  isAnonymousUserWithoutCart,
  callDrupalApi,
  callMagentoApi,
  preUpdateValidation,
  getCart,
  getCartWithProcessedData,
  updateCart,
  getProcessedCartData,
  checkoutComUpapiVaultMethod,
  checkoutComVaultMethod,
  getCartSettings,
  getFormattedError,
};
