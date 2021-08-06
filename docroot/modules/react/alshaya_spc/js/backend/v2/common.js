import Axios from 'axios';
import qs from 'qs';
import _isArray from 'lodash/isArray';
import _cloneDeep from 'lodash/cloneDeep';
import _isUndefined from 'lodash/isUndefined';
import _isObject from 'lodash/isObject';
import _isEmpty from 'lodash/isEmpty';
import _isNull from 'lodash/isNull';
import {
  getApiEndpoint,
  getCartIdFromStorage,
  isUserAuthenticated,
  logger,
  removeCartIdFromStorage,
} from './utility';
import {
  cartErrorCodes,
  getDefaultErrorMessage,
  getExceptionMessageType,
  getProcessedErrorMessage,
} from './error';
import cartActions from '../../utilities/cart_actions';
import StaticStorage from './staticStorage';
import { removeStorageInfo, setStorageInfo } from '../../utilities/storage';
import hasValue from '../../../../js/utilities/conditionsUtility';

window.authenticatedUserCartId = 'NA';

window.commerceBackend = window.commerceBackend || {};

/**
 * Gets the cart ID for existing cart.
 *
 * @returns {string|integer|null}
 *   The cart id or null if not available.
 */
window.commerceBackend.getCartId = () => {
  let cartId = getCartIdFromStorage();
  if (_isNull(cartId)) {
    // For authenticated users we get the cart id from the cart.
    const data = window.commerceBackend.getRawCartDataFromStorage();
    if (!_isNull(data)
      && !_isUndefined(data.cart)
      && !_isUndefined(data.cart.id)
    ) {
      cartId = data.cart.id;
    }
  }

  if (typeof cartId === 'string' || typeof cartId === 'number') {
    return cartId;
  }
  return null;
};

/**
 * Stores the raw cart data object into the storage.
 *
 * @param {object} data
 *   The raw cart data object.
 */
window.commerceBackend.setRawCartDataInStorage = (data) => {
  StaticStorage.set('cart_raw', data);
};

/**
 * Fetches the raw cart data object from the static storage.
 */
window.commerceBackend.getRawCartDataFromStorage = () => StaticStorage.get('cart_raw');

/**
 * Stores skus and quantities.
 */
const staticStockMismatchSkusData = [];

/**
 * Sets the static array so that it can be processed later.
 *
 * @param {string} sku
 *   The SKU value.
 * @param {integer} quantity
 *   The quantity of the SKU.
 */
const matchStockQuantity = (sku, quantity = 0) => {
  staticStockMismatchSkusData[sku] = quantity;
};

/**
 * Gets the cart data.
 *
 * @returns {object|null}
 *   Processed cart data else null.
 */
window.commerceBackend.getCartDataFromStorage = () => StaticStorage.get('cart');

/**
 * Sets the cart data to storage.
 *
 * @param data
 *   The cart data.
 */
window.commerceBackend.setCartDataInStorage = (data) => {
  const cartInfo = { ...data };
  cartInfo.last_update = new Date().getTime();
  StaticStorage.set('cart', cartInfo);

  // @todo find better way to get this using commerceBackend.
  // As of now it not possible to get it on page load before all
  // other JS is executed and for all other JS refactoring
  // required is huge.
  setStorageInfo(cartInfo, 'cart_data');
};

/**
 * Removes the cart data from storage.
 *
 * @param {boolean}
 *  Whether we should remove all items.
 */
window.commerceBackend.removeCartDataFromStorage = (resetAll = false) => {
  StaticStorage.clear();

  removeStorageInfo('cart_data');

  // Remove last selected payment on page load.
  // We use this to ensure we trigger events for payment method
  // selection at-least once and not more than once.
  removeStorageInfo('last_selected_payment');

  if (resetAll) {
    removeCartIdFromStorage();
  }
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
const i18nMagentoUrl = (path) => `${getCartSettings('url')}${path}`;

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
  const response = {};
  response.data = {};
  response.status = apiResponse.status;

  // In case we don't receive any response data.
  if (typeof apiResponse.data === 'undefined') {
    logger.error('Error while doing MDC api. Response result is empty. Status code: @status', {
      '@status': response.status,
    });

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
  if (apiResponse.status === 500) {
    logger.warning('500 error from backend. Message: @message.', {
      '@message': getProcessedErrorMessage(apiResponse),
    });

    // Server error responses.
    response.data.error = true;
    response.data.error_code = 500;
    response.data.error_message = getDefaultErrorMessage();
  } else if (apiResponse.status > 500) {
    // Server error responses.
    response.data.error = true;
    response.data.error_code = 600;
    response.data.error_message = 'Back-end system is down';
  } else if (apiResponse.status === 401) {
    if (isUserAuthenticated()) {
      // Customer Token expired.
      logger.warning('Got 401 response, redirecting to user/logout. Message: @message.', {
        '@message': apiResponse.data.message,
      });

      // Log the user out and redirect to the login page.
      window.location = Drupal.url('user/logout');

      // Throw an error to prevent further javascript execution.
      throw new Error('The customer token is invalid.');
    }

    response.data.error = true;
    response.data.error_code = 401;
    response.data.error_message = apiResponse.data.message;
  } else if (apiResponse.status !== 200) {
    // Set default values.
    response.data.error = true;
    response.data.error_message = getDefaultErrorMessage();

    // Check for empty resonse data.
    if (_isNull(apiResponse) || _isUndefined(apiResponse.data)) {
      logger.warning('Error while doing MDC api. Response result is empty. Status code: @code', {
        '@code': response.status,
      });
      response.data.error_code = 500;
    } else if (apiResponse.status === 404
      && _isUndefined(apiResponse.data)
      && !_isUndefined(apiResponse.message)
      && !_isEmpty(apiResponse.message)) {
      response.data.code = 404;
      response.data.error_code = 404;
      response.data.error_message = response.message;

      // Log the error message.
      logger.warning('Error while doing MDC api call. Error message: @message, Code: @result_code, Response code: @response_code.', {
        '@message': response.data.error_message,
        '@result_code': (typeof response.data.code !== 'undefined') ? response.data.code : '-',
        '@response_code': response.status,
      });
    } else if (!_isUndefined(apiResponse.data.message) && !_isEmpty(apiResponse.data.message)) {
      // Process message.
      response.data.error_message = getProcessedErrorMessage(apiResponse);

      // Log the error message.
      logger.warning('Error while doing MDC api call. Error message: @message, Code: @result_code, Response code: @response_code.', {
        '@message': response.data.error_message,
        '@result_code': (typeof apiResponse.data.code !== 'undefined') ? apiResponse.data.code : '-',
        '@response_code': apiResponse.status,
      });

      // The following case happens when there is a stock mismatch between
      // Magento and OMS.
      if (apiResponse.status === 400
        && typeof apiResponse.data.code !== 'undefined'
        && apiResponse.data.code === cartErrorCodes.cartCheckoutQuantityMismatch) {
        response.data.code = cartErrorCodes.cartCheckoutQuantityMismatch;
        response.data.error_code = cartErrorCodes.cartCheckoutQuantityMismatch;
      } else if (apiResponse.status === 404) {
        response.data.error_code = 404;
      } else {
        response.data.error_code = 500;
      }
    } else if (!_isUndefined(apiResponse.data.messages)
      && !_isEmpty(apiResponse.data.messages)
      && !_isUndefined(apiResponse.data.messages.error)
      && !_isEmpty(response.data.messages.error)
    ) {
      // Other messages.
      const error = apiResponse.data.messages.error[0];
      logger.info('Error while doing MDC api call. Error message: @message, Code: @result_code, Response code: @response_code.', {
        '@message': error.message,
      });
      response.data.error_code = error.code;
      response.data.error_message = error.message;
    }
  } else if (typeof apiResponse.data.messages !== 'undefined'
    && typeof apiResponse.data.messages.error !== 'undefined') {
    const error = apiResponse.data.messages.error.shift();
    response.data.error = true;
    response.data.error_code = error.code;
    response.data.error_message = error.message;
    logger.info('Error while doing MDC api call. Error: @error_message', {
      '@error_message': error.message,
    });
  } else if (_isArray(apiResponse.data.response_message)
    && !_isUndefined(apiResponse.data.response_message[1])
    && apiResponse.data.response_message[1] === 'error') {
    // When there is error in response_message from custom updateCart API.
    response.data.error = true;
    response.data.error_code = 400;
    [response.data.error_message] = apiResponse.data.response_message;
    logger.info('Error while doing MDC api call. Error: @error_message', {
      '@error_message': JSON.stringify(response.data.response_message),
    });
  }

  // Assign response data as is if no error.
  if (typeof response.data.error === 'undefined') {
    response.data = JSON.parse(JSON.stringify(apiResponse.data));
  }

  return new Promise((resolve) => resolve(response));
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
 * @returns {Promise<AxiosPromise<object>>}
 *   Returns a promise object.
 */
const callMagentoApi = (url, method = 'GET', data = {}) => {
  const params = {
    url: i18nMagentoUrl(url),
    method,
    headers: {
      'Content-Type': 'application/json',
      'Alshaya-Channel': 'web',
    },
  };

  if (isUserAuthenticated()) {
    params.headers.Authorization = `Bearer ${window.drupalSettings.userDetails.customerToken}`;
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

      logger.error('Something happened in setting up the request that triggered an error: @error.', {
        '@error': error.message,
      });

      return error;
    });
};

/**
 * Make an AJAX call to Drupal API.
 *
 * @param {string} url
 *   The url to send the request to.
 * @param {string} method
 *   The request method.
 * @param {object} data
 *   The object to send with the request.
 *
 * @returns {Promise<AxiosPromise<object>>}
 *   Returns a promise object.
 */
const callDrupalApi = (url, method = 'GET', data = {}) => {
  const headers = {};
  const params = {
    url: `/${window.drupalSettings.path.currentLanguage}${url}`,
    method,
    data,
  };

  if (typeof data !== 'undefined' && data && Object.keys(data).length > 0) {
    Object.keys(data).forEach((optionName) => {
      if (optionName === 'form_params') {
        headers['Content-Type'] = 'application/x-www-form-urlencoded';
        params.data = qs.stringify(data[optionName]);
      }
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
  const data = _cloneDeep(cartData);

  // Check if there is no cart data.
  if (_isUndefined(data.cart) || !_isObject(data.cart)) {
    return data;
  }

  // Move customer data to root level.
  if (!_isEmpty(data.cart.customer)) {
    data.customer = data.cart.customer;
    delete data.cart.customer;
  }

  // Format addresses.
  if (!_isEmpty(data.customer) && !_isEmpty(data.customer.addresses)) {
    data.customer.addresses = data.customer.addresses.map((address) => {
      const item = { ...address };
      delete item.id;
      item.region = address.region_id;
      item.customer_address_id = address.id;
      return item;
    });
  }

  // Format shipping info.
  if (!_isEmpty(data.cart.extension_attributes)) {
    if (!_isEmpty(data.cart.extension_attributes.shipping_assignments)) {
      if (!_isEmpty(data.cart.extension_attributes.shipping_assignments[0].shipping)) {
        data.shipping = data.cart.extension_attributes.shipping_assignments[0].shipping;
        delete data.cart.extension_attributes.shipping_assignments;
      }
    }
  } else {
    data.shipping = {};
  }

  let shippingMethod = '';
  if (!_isEmpty(data.shipping)) {
    if (!_isEmpty(data.shipping.method)) {
      shippingMethod = data.shipping.method;
    }
    if (!_isEmpty(shippingMethod) && shippingMethod.indexOf('click_and_collect') >= 0) {
      data.shipping.type = 'click_and_collect';
    } else if (isUserAuthenticated()
      && (typeof data.shipping.address.customer_address_id === 'undefined' || !(data.shipping.address.customer_address_id))) {
      // Ignore the address if not available from address book for customer.
      data.shipping = {};
    } else {
      data.shipping.type = 'home_delivery';
    }
  }

  if (!_isEmpty(data.shipping) && !_isEmpty(data.shipping.extension_attributes)) {
    const extensionAttributes = data.shipping.extension_attributes;
    if (!_isEmpty(extensionAttributes.click_and_collect_type)) {
      data.shipping.clickCollectType = extensionAttributes.click_and_collect_type;
    }
    if (!_isEmpty(extensionAttributes.store_code)) {
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
 * Static cache for getProductStatus().
 *
 * @type {null}
 */
const staticProductStatus = [];

/**
 * Get data related to product status.
 * @todo Allow bulk requests, see CORE-32123
 *
 * @param {Promise<string|null>} sku
 *  The sku for which the status is required.
 */
const getProductStatus = async (sku) => {
  if (typeof sku === 'undefined' || !sku) {
    return null;
  }

  // Return from static, if available.
  if (!_isUndefined(staticProductStatus[sku])) {
    return staticProductStatus[sku];
  }

  // Bypass CloudFlare to get fresh stock data.
  // Rules are added in CF to disable caching for urls having the following
  // query string.
  // The query string is added since same APIs are used by MAPP also.
  const response = await callDrupalApi(`/rest/v1/product-status/${btoa(sku)}`, 'GET', { _cf_cache_bypass: '1' });
  if (_isEmpty(response.data)
    || (!_isUndefined(response.data.error) && response.data.error)
  ) {
    staticProductStatus[sku] = null;
  } else {
    staticProductStatus[sku] = response.data;
  }

  return staticProductStatus[sku];
};

/**
 * Transforms cart data to match the data structure from middleware.
 *
 * @param {object} cartData
 *   The cart data object.
 *
 * @returns {object}
 *   The processed cart data.
 */
const getProcessedCartData = async (cartData) => {
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
    for (let i = 0; i < cartData.cart.items.length; i++) {
      const item = cartData.cart.items[i];
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
      };

      // Get stock data.
      // Suppressing the lint error for now.
      // eslint-disable-next-line no-await-in-loop
      const stockInfo = await getProductStatus(item.sku);
      data.items[item.sku].in_stock = stockInfo.in_stock;
      data.items[item.sku].stock = stockInfo.stock;

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

      // If any item is OOS.
      if (!_isEmpty(data.items[item.sku].stock) || data.items[item.sku].stock === 0) {
        data.in_stock = false;
      }
    }
  } else {
    data.items = [];
  }
  return data;
};

/**
 * Check if user is anonymous and without cart.
 *
 * @returns bool
 */
const isAnonymousUserWithoutCart = () => {
  const cartId = window.commerceBackend.getCartId();
  if (cartId === null || typeof cartId === 'undefined') {
    if (window.drupalSettings.userDetails.customerId === 0) {
      return true;
    }
  }
  return false;
};

const clearInvalidCart = () => {
  const isAssociatingCart = StaticStorage.get('associating_cart') || false;
  if (getCartIdFromStorage() && !isAssociatingCart) {
    logger.warning('Removing cart from local storage and reloading.');

    // Remove cart_id from storage.
    removeCartIdFromStorage();

    // Reload the page now that we have removed cart id from storage.
    // eslint-disable-next-line no-self-assign
    window.location.href = window.location.href;
  }
};

/**
 * Calls the cart get API.
 *
 * @param {boolean} force
 *   Flag for static/fresh cartData.
 *
 * @returns {Promise<AxiosPromise<object>>|null}
 *   A promise object containing the cart or null.
 */
const getCart = async (force = false) => {
  if (!force && window.commerceBackend.getRawCartDataFromStorage() !== null) {
    return { data: window.commerceBackend.getRawCartDataFromStorage() };
  }

  if (isAnonymousUserWithoutCart()) {
    return null;
  }

  const cartId = window.commerceBackend.getCartId();
  const response = await callMagentoApi(getApiEndpoint('getCart', { cartId }), 'GET', {});

  response.data = response.data || {};

  if (hasValue(response.data.error)) {
    if ((hasValue(response.status) && response.status === 404)
        || (hasValue(response.data) && response.data.error_code === 404)
        || (hasValue(response.data.message) && response.data.error_message.indexOf('No such entity with cartId') > -1)
    ) {
      logger.warning('getCart() returned error: @code.', {
        '@code': response.data.error_code,
      });

      clearInvalidCart();

      // If cart is no longer available, no need to return any error.
      return null;
    }

    return {
      data: {
        error: response.data.error,
        error_code: response.data.error_code,
        error_message: getDefaultErrorMessage(),
      },
    };
  }

  // If no error and no response, consider that as 404.
  if (!hasValue(response.data)) {
    clearInvalidCart();
    return null;
  }

  // Format data.
  response.data = formatCart(response.data);

  // Store the formatted data.
  window.commerceBackend.setRawCartDataInStorage(response.data);

  // Return formatted cart.
  return response;
};

/**
 * Adds a customer to cart.
 *
 * @returns {Promise<object/boolean>}
 *   Returns the updated cart or false.
 */
const associateCartToCustomer = async (guestCartId) => {
  // Prepare params.
  const params = { cartId: guestCartId };

  // Associate cart to customer.
  const response = await callMagentoApi(getApiEndpoint('associateCart'), 'POST', params);

  // It's possible that page got reloaded quickly after login.
  // For example on social login.
  if (response.message === 'Request aborted') {
    return;
  }

  if (response.status !== 200) {
    logger.warning('Error while associating cart: @cartId to customer: @customerId. Response: @response.', {
      '@cartId': guestCartId,
      '@customerId': window.drupalSettings.userDetails.customerId,
      '@response': JSON.stringify(response),
    });

    // Clear local storage and let the customer continue without association.
    removeCartIdFromStorage();
    StaticStorage.clear();
    return;
  }

  logger.notice('Guest Cart @guestCartId associated to customer @customerId.', {
    '@customerId': window.drupalSettings.userDetails.customerId,
    '@guestCartId': guestCartId,
  });

  // Clear local storage.
  removeCartIdFromStorage();
  StaticStorage.clear();

  // Reload cart.
  await getCart(true);
};

/**
 * Format the cart data to have better structured array.
 * This is the equivalent to CartController:getCart().
 *
 * @param {boolean} force
 *   Force refresh cart data from magento.
 *
 * @returns {Promise<AxiosPromise<object>>}
 *   A promise object.
 */
const getCartWithProcessedData = async (force = false) => {
  // @todo implement missing logic, see CartController:getCart().
  const cart = await getCart(force);
  if (_isNull(cart) || _isUndefined(cart.data)) {
    return null;
  }

  // If we don't have any errors, process the cart data.
  if (!_isEmpty(cart.data) && _isUndefined(cart.data.error)) {
    cart.data = await getProcessedCartData(cart.data);
  }

  return cart;
};

/**
 * Check if user is authenticated and without cart.
 *
 * @returns bool
 */
const isAuthenticatedUserWithoutCart = async () => {
  const response = await getCart();
  if (_isNull(response)
    || _isUndefined(response.data)
    || _isUndefined(response.data.cart)
    || _isUndefined(response.data.cart.id)
  ) {
    return true;
  }
  return false;
};

/**
 * Return customer id from current session.
 *
 * @returns {Promise<integer|null>}
 *   Return customer id or null.
 */
const getCartCustomerId = async () => {
  const response = await getCart();
  if (_isNull(response) || _isUndefined(response.data)) {
    return null;
  }

  const cart = response.data;
  if (!_isEmpty(cart) && !_isEmpty(cart.customer) && !_isUndefined(cart.customer.id)) {
    return parseInt(cart.customer.id, 10);
  }
  return null;
};

/**
 * Validate arguments and returns the respective error code.
 *
 * @param {object} request
 *  The request data.
 *
 * @returns {Promise<integer>}
 *   Promise containing the error code.
 */
const validateRequestData = async (request) => {
  // Return error response if not valid data.
  // Setting custom error code for bad response so that
  // we could distinguish this error.
  if (_isEmpty(request)) {
    logger.error('Cart update operation not containing any data.');
    return 500;
  }

  // If action info or cart id not available.
  if (_isEmpty(request.extension) || _isUndefined(request.extension.action)) {
    logger.error('Cart update operation not containing any action. Data: @data.', {
      '@data': JSON.stringify(request),
    });
    return 400;
  }

  // For any cart update operation, cart should be available in session.
  if (window.commerceBackend.getCartId() === null) {
    logger.warning('Trying to do cart update operation while cart is not available in session. Data: @data.', {
      '@data': JSON.stringify(request),
    });
    return 404;
  }

  // Backend validation.
  const cartCustomerId = await getCartCustomerId();
  if (drupalSettings.userDetails.customerId > 0) {
    if (_isNull(cartCustomerId)) {
      // @todo Check if we should associate cart and proceed.
      // Todo copied from middleware.
      return 400;
    }

    // This is serious.
    if (cartCustomerId !== drupalSettings.userDetails.customerId) {
      logger.error('Mismatch session customer id: @sessionCustomerId and card customer id: @cartCustomerId.', {
        '@sessionCustomerId': drupalSettings.userDetails.customerId,
        '@cartCustomerId': cartCustomerId,
      });
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
 * @returns {Promise<object|boolean>}
 *   Returns true if the data is valid or an object in case of error.
 */
const preUpdateValidation = async (request) => {
  const validationResponse = await validateRequestData(request);
  if (validationResponse !== 200) {
    return {
      error: true,
      error_code: validationResponse,
      error_message: getDefaultErrorMessage(),
      response_message: {
        status: '',
        msg: getDefaultErrorMessage(),
      },
    };
  }
  return true;
};

/**
 * Calls the update cart API and returns the updated cart.
 *
 * @param {object} data
 *  The data to send.
 *
 * @returns {Promise<AxiosPromise<object>>}
 *   A promise object with cart data.
 */
const updateCart = async (data) => {
  const cartId = window.commerceBackend.getCartId();

  let action = '';
  if (!_isEmpty(data.extension) && !_isEmpty(data.extension.action)) {
    action = data.extension.action;
  }

  // Validate params before updating the cart.
  const validationResult = await preUpdateValidation(data);
  if (!_isUndefined(validationResult.error) && validationResult.error) {
    return new Promise((resolve, reject) => reject(validationResult));
  }

  // Log the shipping / billing address we pass to magento.
  if (action === cartActions.cartBillingUpdate || action === cartActions.cartShippingUpdate) {
    logger.debug('Billing / Shipping update. CartId: @cartId, Address: @address.', {
      '@cartId': cartId,
      '@address': JSON.stringify(data),
    });
  }

  return callMagentoApi(getApiEndpoint('updateCart', { cartId }), 'POST', JSON.stringify(data))
    .then((response) => {
      if (_isEmpty(response.data)
        || (!_isUndefined(response.data.error) && response.data.error)) {
        return response;
      }

      // Format data.
      response.data = formatCart(response.data);

      // Update the cart data in storage.
      window.commerceBackend.setRawCartDataInStorage(response.data);

      return response;
    })
    .catch((response) => {
      logger.warning('Error while updating cart on MDC for action: @action. Error message: @errorMessage, Code: @errorCode', {
        '@action': action,
        '@errorMessage': response.error.message,
        '@errorCode': response.error.error_code,
      });
      // @todo add error handling, see try/catch block in Cart:updateCart().
      return response;
    });
};

/**
 * Return customer email from cart in session.
 *
 * @returns {Promise<string|null>}
 *   Return customer email or null.
 */
const getCartCustomerEmail = async () => {
  const response = await getCart();
  if (_isNull(response) || _isUndefined(response.data)) {
    return null;
  }

  const cart = response.data;
  if (!_isUndefined(cart.customer)
    && !_isUndefined(cart.customer.email)
    && !_isEmpty(cart.customer.email)
  ) {
    return cart.customer.email;
  }
  return null;
};

/**
 * Checks if cart has OOS item or not by item level attribute.
 *
 * @param {object} cart
 *   Cart data.
 *
 * @return {bool}
 *   TRUE if cart has an OOS item.
 */
const isCartHasOosItem = (cartData) => {
  if (!_isEmpty(cartData.cart.items)) {
    for (let i = 0; i < cartData.cart.items.length; i++) {
      const item = cartData.cart.items[i];
      // If error at item level.
      if (!_isUndefined(item.extension_attributes)
        && !_isUndefined(item.extension_attributes.error_message)
      ) {
        const exceptionType = getExceptionMessageType(item.extension_attributes.error_message);
        if (!_isEmpty(exceptionType) && exceptionType === 'OOS') {
          return true;
        }
      }
    }
  }
  return false;
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
  response_message: {
    msg: message,
    status: 'error',
  },
});

export {
  isAnonymousUserWithoutCart,
  isAuthenticatedUserWithoutCart,
  associateCartToCustomer,
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
  getCartCustomerEmail,
  getCartCustomerId,
  matchStockQuantity,
  isCartHasOosItem,
  getProductStatus,
};
