import Axios from 'axios';
import qs from 'qs';
import Cookies from 'js-cookie';
import {
  getApiEndpoint,
  getCartIdFromStorage,
  isUserAuthenticated,
  removeCartIdFromStorage,
  detectCFChallenge,
  detectCaptcha,
} from './utility';
import logger from '../../utilities/logger';
import {
  cartErrorCodes,
  getDefaultErrorMessage,
  getExceptionMessageType,
  getProcessedErrorMessage,
} from './error';
import StaticStorage from './staticStorage';
import { removeStorageInfo, setStorageInfo } from '../../utilities/storage';
import {
  hasValue,
  isObject,
  isArray,
} from '../../../../js/utilities/conditionsUtility';
import getAgentDataForExtension from './smartAgent';
import collectionPointsEnabled from '../../../../js/utilities/pudoAramaxCollection';

window.authenticatedUserCartId = 'NA';

window.commerceBackend = window.commerceBackend || {};

/**
 * Gets the cart ID for existing cart.
 *
 * @returns {string|integer|null}
 *   The cart id or null if not available.
 */
window.commerceBackend.getCartId = () => {
  // This is for ALX InStorE feature.
  // We want to be able to resume guest carts from URL,
  // we pass that id from backend via Cookie to Browser.
  const resumeCartId = Cookies.get('resume_cart_id');
  if (hasValue(resumeCartId)) {
    removeStorageInfo('cart_data');
    setStorageInfo(resumeCartId, 'cart_id');
    Cookies.remove('resume_cart_id');
  }

  let cartId = getCartIdFromStorage();
  if (!hasValue(cartId)) {
    // For authenticated users we get the cart id from the cart.
    const data = window.commerceBackend.getRawCartDataFromStorage();
    if (hasValue(data)
      && hasValue(data.cart)
      && hasValue(data.cart.id)
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

const logApiStats = (response) => {
  try {
    if (!hasValue(response) || !hasValue(response.config) || !hasValue(response.config.headers)) {
      return response;
    }

    const transferTime = Date.now() - response.config.headers.RequestTime;
    logger.debug('Finished API request @url in @transferTime, ResponseCode: @responseCode, Method: @method.', {
      '@url': response.config.url,
      '@transferTime': transferTime,
      '@responseCode': response.status,
      '@method': response.config.method,
    });
  } catch (error) {
    logger.error('Failed to log API response time, error: @message', {
      '@message': error.message,
    });
  }

  return response;
};

/**
 * Logs API response to the logging system.
 *
 * @param {string} type
 *   The type of log message.
 * @param {*} message
 *   The message text.
 * @param {*} statusCode
 *   The API response status code.
 * @param {*} code
 *   The code value in the response.
 */
const logApiResponse = (type, message, statusCode, code) => {
  logger[type]('Commerce backend call failed. Response Code: @responseCode, Error Code: @resultCode, Exception: @message.', {
    '@responseCode': statusCode,
    '@resultCode': hasValue(code) ? code : '-',
    '@message': hasValue(message) ? message : '-',
  });
};

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
  logApiStats(apiResponse);
  const response = {};
  response.data = {};
  response.status = apiResponse.status;

  // In case we don't receive any response data.
  if (typeof apiResponse.data === 'undefined') {
    logApiResponse('warning', 'Error while doing MDC api. Response result is empty.', apiResponse.status);

    const error = {
      data: {
        error: true,
        error_code: 500,
        error_message: getDefaultErrorMessage(),
      },
    };
    return new Promise((resolve) => resolve(error));
  }

  // If the response contains Captcha, the page will be reloaded once per session.
  detectCaptcha(apiResponse);
  // If the response contains a CF Challenge, the page will be reloaded once per session.
  detectCFChallenge(apiResponse);
  // Treat each status code.
  if (apiResponse.status === 202) {
    // Place order can return 202, this isn't error.
    // Do nothing here, we will let code below return the response.
  } else if (apiResponse.status === 500) {
    logApiResponse('warning', getProcessedErrorMessage(apiResponse), apiResponse.status);

    // Server error responses.
    response.data.error = true;
    response.data.error_code = 500;
  } else if (apiResponse.status > 500) {
    // Server error responses.
    response.data.error = true;
    response.data.error_code = 600;
    logApiResponse('warning', apiResponse.data.error_message, apiResponse.status);
  } else if (apiResponse.status === 401) {
    if (isUserAuthenticated()) {
      // Customer Token expired.
      logApiResponse('warning', `Got 401 response, redirecting to user/logout. ${apiResponse.data.message}`, apiResponse.status);

      // Log the user out and redirect to the login page.
      window.location = Drupal.url('user/logout');

      // Throw an error to prevent further javascript execution.
      throw new Error('The customer token is invalid.');
    }

    response.data.error = true;
    response.data.error_code = 401;
    logApiResponse('warning', apiResponse.data.message, apiResponse.status);
  } else if (apiResponse.status !== 200) {
    // Set default values.
    response.data.error = true;
    response.data.error_message = getDefaultErrorMessage();

    // Check for empty resonse data.
    if (!hasValue(apiResponse) || !hasValue(apiResponse.data)) {
      logApiResponse('warning', 'Error while doing MDC api. Response result is empty', apiResponse.status);
      response.data.error_code = 500;
    } else if (apiResponse.status === 404
      && !hasValue(apiResponse.data)
      && hasValue(apiResponse.message)) {
      response.data.code = 404;
      response.data.error_code = 404;
      response.data.error_message = response.message;

      // Log the error message.
      logApiResponse('warning', response.data.error_message, apiResponse.status, response.data.code);
    } else if (hasValue(apiResponse.data.message)) {
      // Process message.
      response.data.error_message = getProcessedErrorMessage(apiResponse);

      // Log the error message.
      logApiResponse('warning', response.data.error_message, apiResponse.status, apiResponse.data.code);

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
    } else if (hasValue(apiResponse.data.messages)
      && hasValue(apiResponse.data.messages.error)
      && hasValue(response.data.messages.error)
    ) {
      // Other messages.
      const error = apiResponse.data.messages.error[0];
      logApiResponse('info', error.message, apiResponse.status, error.code);
      response.data.error_code = error.code;
      response.data.error_message = error.message;
    }
  } else if (typeof apiResponse.data.messages !== 'undefined'
    && typeof apiResponse.data.messages.error !== 'undefined') {
    const error = apiResponse.data.messages.error.shift();
    response.data.error = true;
    response.data.error_code = error.code;
    response.data.error_message = error.message;
    logApiResponse('info', error.message, apiResponse.status, error.code);
  } else if (isArray(apiResponse.data.response_message)
    && hasValue(apiResponse.data.response_message[1])
    && apiResponse.data.response_message[1] === 'error') {
    // When there is error in response_message from custom updateCart API.
    response.data.error = true;
    response.data.error_code = 400;
    [response.data.error_message] = apiResponse.data.response_message;
    logApiResponse('info', JSON.stringify(response.data.response_message), apiResponse.status, response.data.error_code);
  }

  // Assign response data as is if no error.
  if (typeof response.data.error === 'undefined') {
    response.data = JSON.parse(JSON.stringify(apiResponse.data));
  } else if (apiResponse.status > 400 && apiResponse.status < 700) {
    // Format error for specific cases so that in the front end we show user
    // friendly error messages.
    response.data.error_message = getDefaultErrorMessage();
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

  params.headers = params.headers || {};
  params.headers.RequestTime = Date.now();

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

      logger.error('Something happened in setting up the request that triggered an error: @message.', {
        '@message': error.message,
      });

      return error;
    });
};

/**
 * Make an synchronous AJAX call to Magento API.
 * @todo: Update CallMagentoApi with sync request.
 *
 * @param {string} url
 *   The url to send the request to.
 * @param {string} method
 *   The request method.
 * @param {object} dataObject
 *   The object to send for POST request.
 *
 * @returns {object}
 *   Returns a ajax response.
 */
const callMagentoApiSynchronous = (url, method = 'GET', dataObject = {}) => {
  const requestHeaders = {
    'Content-Type': 'application/json',
    'Alshaya-Channel': 'web',
    RequestTime: Date.now(),
  };
  if (isUserAuthenticated()) {
    requestHeaders.Authorization = `Bearer ${window.drupalSettings.userDetails.customerToken}`;
  }
  let result;
  jQuery.ajax({
    url: i18nMagentoUrl(url),
    type: method,
    async: false,
    cache: false,
    data: dataObject,
    headers: requestHeaders,
    success(response) {
      result = response;
    },
    error(exception) {
      logger.error('Something happened in setting up the request that triggered an error: @message.', {
        '@message': exception.statusText,
      });
      result = exception;
    },
  });
  return result;
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

  params.headers = params.headers || {};
  params.headers.RequestTime = Date.now();

  return Axios(params)
    .then((response) => logApiStats(response))
    .catch((error) => {
      if (hasValue(error.response) && hasValue(error.response.status)) {
        logApiStats(error.response);
        const responseCode = parseInt(error.response.status, 10);

        if (responseCode === 404) {
          logger.warning('Drupal page no longer available.', { ...params });
          return null;
        }

        logger.error('Drupal API call failed.', {
          responseCode,
          ...params,
        });
        return null;
      }

      logger.error('Something happened in setting up the request that triggered an error: @message.', {
        '@message': error.message,
        ...params,
      });

      return null;
    });
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
  // As of now we don't need deep clone of the passed object.
  // As Method calls are storing the result on the same object.
  // For ex - response.data = formatCart(response.data);
  // if in future, method call is storing result on any other object.
  // Clone of the argument passed, will be needed which can be achieved using.
  // const data = JSON.parse(JSON.stringify(cartData));
  const data = cartData;
  // Check if there is no cart data.
  if (!hasValue(data.cart) || !isObject(data.cart)) {
    return data;
  }

  // Move customer data to root level.
  if (hasValue(data.cart.customer)) {
    data.customer = data.cart.customer;
    delete data.cart.customer;
  }

  // Format addresses.
  if (hasValue(data.customer) && hasValue(data.customer.addresses)) {
    data.customer.addresses = data.customer.addresses.map((address) => {
      const item = { ...address };
      delete item.id;
      item.region = address.region_id;
      item.customer_address_id = address.id;
      return item;
    });
  }

  // Format shipping info.
  if (hasValue(data.cart.extension_attributes)) {
    if (hasValue(data.cart.extension_attributes.shipping_assignments)) {
      if (hasValue(data.cart.extension_attributes.shipping_assignments[0].shipping)) {
        data.shipping = data.cart.extension_attributes.shipping_assignments[0].shipping;
        delete data.cart.extension_attributes.shipping_assignments;
      }
    }
  } else {
    data.shipping = {};
  }

  let shippingMethod = '';
  if (hasValue(data.shipping)) {
    if (hasValue(data.shipping.method)) {
      shippingMethod = data.shipping.method;
    }
    if (hasValue(shippingMethod) && shippingMethod.indexOf('click_and_collect') >= 0) {
      data.shipping.type = 'click_and_collect';
    } else if (isUserAuthenticated()
      && (typeof data.shipping.address.customer_address_id === 'undefined' || !(data.shipping.address.customer_address_id))) {
      // Ignore the address if not available from address book for customer.
      data.shipping = {};
    } else {
      data.shipping.type = 'home_delivery';
    }
  }

  if (hasValue(data.shipping) && hasValue(data.shipping.extension_attributes)) {
    const extensionAttributes = data.shipping.extension_attributes;
    if (hasValue(extensionAttributes.click_and_collect_type)) {
      data.shipping.clickCollectType = extensionAttributes.click_and_collect_type;
    }
    if (hasValue(extensionAttributes.store_code)) {
      data.shipping.storeCode = extensionAttributes.store_code;
    }

    // If collection point feature is enabled, extract collection point details
    // from shipping data.
    if (collectionPointsEnabled()) {
      data.shipping.collection_point = extensionAttributes.collection_point;
      data.shipping.pickup_date = extensionAttributes.pickup_date;
      data.shipping.price_amount = extensionAttributes.price_amount;
      data.shipping.pudo_available = extensionAttributes.pudo_available;
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
  if (typeof staticProductStatus[sku] !== 'undefined') {
    return staticProductStatus[sku];
  }

  // Bypass CloudFlare to get fresh stock data.
  // Rules are added in CF to disable caching for urls having the following
  // query string.
  // The query string is added since same APIs are used by MAPP also.
  const response = await callDrupalApi(`/rest/v1/product-status/${btoa(sku)}`, 'GET', { _cf_cache_bypass: '1' });
  if (!hasValue(response) || !hasValue(response.data) || hasValue(response.data.error)) {
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
  // In case of errors, return the error object.
  if (hasValue(cartData) && hasValue(cartData.error) && cartData.error) {
    return cartData;
  }

  // If the cart object is empty, return null.
  if (!hasValue(cartData) || !hasValue(cartData.cart)) {
    return null;
  }

  const data = {
    cart_id: window.commerceBackend.getCartId(),
    cart_id_int: cartData.cart.id,
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
      base_grand_total: cartData.totals.base_grand_total,
      base_grand_total_without_surcharge: cartData.totals.base_grand_total,
      discount_amount: cartData.totals.discount_amount,
      surcharge: 0,
      items: cartData.totals.items,
      allExcludedForAdcard: cartData.totals.extension_attributes.is_all_items_excluded_for_adv_card,
    },
    items: [],
    ...(collectionPointsEnabled() && hasValue(cartData.shipping))
      && { collection_charge: cartData.shipping.price_amount || '' },
  };

  // Totals.
  if (typeof cartData.totals.base_grand_total !== 'undefined') {
    data.cart_total = cartData.totals.base_grand_total;
    data.minicart_total = cartData.totals.base_grand_total;
  }

  if (!hasValue(cartData.shipping) || !hasValue(cartData.shipping.method)) {
    // We use null to show "Excluding Delivery".
    data.totals.shipping_incl_tax = null;
  } else if (cartData.shipping.type !== 'click_and_collect') {
    // For click_n_collect we don't want to show this line at all.
    data.totals.shipping_incl_tax = (hasValue(cartData.totals.shipping_incl_tax))
      ? cartData.totals.shipping_incl_tax
      : 0;
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

      // Get stock data on cart and checkout pages.
      const spcPageType = window.spcPageType || '';
      if (spcPageType === 'cart' || spcPageType === 'checkout') {
        // Suppressing the lint error for now.
        // eslint-disable-next-line no-await-in-loop
        const stockInfo = await getProductStatus(item.sku);

        // Do not show the products which are not available in
        // system but only available in cart.
        if (!hasValue(stockInfo) || hasValue(stockInfo.error)) {
          logger.warning('Product not available in system but available in cart. SKU: @sku, CartId: @cartId, StockInfo: @stockInfo.', {
            '@sku': item.sku,
            '@cartId': data.cart_id_int,
            '@stockInfo': JSON.stringify(stockInfo || {}),
          });

          delete data.items[item.sku];
          // eslint-disable-next-line no-continue
          continue;
        }

        data.items[item.sku].in_stock = stockInfo.in_stock;
        data.items[item.sku].stock = stockInfo.stock;

        // If any item is OOS.
        if (!hasValue(stockInfo.in_stock) || !hasValue(stockInfo.stock)) {
          data.in_stock = false;
        }
      }

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
      logger.warning('getCart() returned error: @errorCode.', {
        '@errorCode': response.data.error_code,
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
  if (!hasValue(cart) || !hasValue(cart.data)) {
    return null;
  }

  cart.data = await getProcessedCartData(cart.data);

  return cart;
};

/**
 * Check if user is authenticated and without cart.
 *
 * @returns bool
 */
const isAuthenticatedUserWithoutCart = async () => {
  const response = await getCart();
  if (!hasValue(response)
    || !hasValue(response.data)
    || !hasValue(response.data.cart)
    || !hasValue(response.data.cart.id)
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
  if (!hasValue(response) || !hasValue(response.data)) {
    return null;
  }

  const cart = response.data;
  if (hasValue(cart) && hasValue(cart.customer) && hasValue(cart.customer.id)) {
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
  if (!hasValue(request)) {
    logger.error('Cart update operation not containing any data.');
    return 500;
  }

  // If action info or cart id not available.
  if (!hasValue(request.extension) || !hasValue(request.extension.action)) {
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
    if (!hasValue(cartCustomerId)) {
      // @todo Check if we should associate cart and proceed.
      // Todo copied from middleware.
      return 400;
    }

    // This is serious.
    if (cartCustomerId !== drupalSettings.userDetails.customerId) {
      logger.error('Mismatch session customer id: @customerId and cart customer id: @cartCustomerId.', {
        '@customerId': drupalSettings.userDetails.customerId,
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
 * @param {object} postData
 *  The data to send.
 *
 * @returns {Promise<AxiosPromise<object>>}
 *   A promise object with cart data.
 */
const updateCart = async (postData) => {
  const data = { ...postData };
  const cartId = window.commerceBackend.getCartId();

  let action = '';
  data.extension = data.extension || {};
  if (hasValue(data.extension.action)) {
    action = data.extension.action;
  }

  // Add Smart Agent data to extension.
  data.extension = Object.assign(data.extension, getAgentDataForExtension());

  // Validate params before updating the cart.
  const validationResult = await preUpdateValidation(data);
  if (hasValue(validationResult.error) && validationResult.error) {
    return new Promise((resolve, reject) => reject(validationResult));
  }

  logger.debug('Updating Cart. CartId: @cartId, Action: @action, Request: @request.', {
    '@cartId': cartId,
    '@request': JSON.stringify(data),
    '@action': action,
  });

  return callMagentoApi(getApiEndpoint('updateCart', { cartId }), 'POST', JSON.stringify(data))
    .then((response) => {
      if (!hasValue(response.data)
        || (hasValue(response.data.error) && response.data.error)) {
        return response;
      }

      // Format data.
      response.data = formatCart(response.data);

      // Update the cart data in storage.
      window.commerceBackend.setRawCartDataInStorage(response.data);

      return response;
    })
    .catch((response) => {
      logger.warning('Error while updating cart on MDC for action: @action. Error message: @message, Code: @errorCode', {
        '@action': action,
        '@message': response.error.message,
        '@errorCode': response.error.error_code,
      });
      // @todo add error handling, see try/catch block in Cart:updateCart().
      return response;
    });
};

window.commerceBackend.pushAgentDetailsInCart = async () => {
  // Do simple refresh cart to make sure we push data before sharing.
  const postData = {
    extension: {
      action: 'refresh',
    },
  };

  return updateCart(postData)
    .then(async (response) => {
      // Process cart data.
      response.data = await getProcessedCartData(response.data);
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
  let email = StaticStorage.get('cartCustomerEmail');
  if (email !== null) {
    return email;
  }

  const response = await getCart();
  if (!hasValue(response) || !hasValue(response.data)) {
    email = '';
  } else {
    const cart = response.data;
    if (hasValue(cart.customer)
      && hasValue(cart.customer.email)
      && cart.customer.email !== ''
    ) {
      email = cart.customer.email;
    }
  }

  StaticStorage.set('cartCustomerEmail', email);
  return email;
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
  if (hasValue(cartData.cart.items)) {
    for (let i = 0; i < cartData.cart.items.length; i++) {
      const item = cartData.cart.items[i];
      // If error at item level.
      if (hasValue(item.extension_attributes)
        && hasValue(item.extension_attributes.error_message)
      ) {
        const exceptionType = getExceptionMessageType(item.extension_attributes.error_message);
        if (hasValue(exceptionType) && exceptionType === 'OOS') {
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

/**
 * Helper function to prepare filter url query string.
 *
 * @param array $filters
 *   Array containing all filters, must contain field and value, can contain
 *   condition_type too or all that is supported by Magento.
 * @param string $base
 *   Filter Base, mostly searchCriteria.
 * @param int $group_id
 *   Filter group id, mostly 0.
 *
 * @return string
 *   Prepared URL query string.
 */
const prepareFilterUrl = (filters, base = 'searchCriteria', groupId = 0) => {
  let url = '';

  filters.forEach((filter, index) => {
    Object.keys(filter).forEach((key) => {
      // Prepared string like below.
      // searchCriteria[filter_groups][0][filters][0][field]=field
      // This is how Magento search criteria in APIs work.
      url = url.concat(`${base}[filter_groups][${groupId}][filters][${index}][${key}]=${filter[key]}`);
      url = url.concat('&');
    });
  });

  return url;
};

/**
 * Function to get locations for delivery matrix.
 *
 * @param string $filterField
 *   The field name to filter on.
 * @param string $filterValue
 *   The value of the field to filter on.
 *
 * @return mixed
 *   Response from API.
 */
const getLocations = async (filterField = 'attribute_id', filterValue = 'governate') => {
  const filters = [];
  // Add filter for field values.
  const fieldFilters = {
    field: filterField,
    value: filterValue,
    condition_type: 'eq',
  };

  filters.push(fieldFilters);

  // Always add status check.
  const statusFilters = {
    field: 'status',
    value: '1',
    condition_type: 'eq',
  };
  filters.push(statusFilters);

  // Filter by Country.
  const countryFilters = {
    field: 'country_id',
    value: drupalSettings.country_code,
    condition_type: 'eq',
  };

  filters.push(countryFilters);
  // @todo pending cofirmation from MDC on using api call for each click.
  let url = '/V1/deliverymatrix/address-locations/search?';
  const params = prepareFilterUrl(filters);
  url = url.concat(params);
  try {
    // Associate cart to customer.
    const response = await callMagentoApi(url, 'GET', {});

    if (hasValue(response.data.error) && response.data.error) {
      logger.error('Error in getting shipping methods for cart. Error: @message', {
        '@message': response.data.error_message,
      });

      return getFormattedError(response.data.error_code, response.data.error_message);
    }

    if (!hasValue(response.data)) {
      const message = 'Got empty response while getting shipping methods.';
      logger.notice(message);

      return getFormattedError(600, message);
    }

    return response.data;
  } catch (error) {
    logger.error('Error occurred while fetching governates data. Message: @message.', {
      '@message': error.message,
    });
  }

  return null;
};

/**
 * Gets governates list items.
 *
 * @returns {Promise<object>}
 *  returns list of governates.
 */
window.commerceBackend.getGovernatesList = async () => {
  if (!drupalSettings.address_fields) {
    logger.error('Error in getting address fields mappings');

    return {};
  }
  if (drupalSettings.address_fields
    && !(drupalSettings.address_fields.area_parent.visible)) {
    return {};
  }
  const mapping = drupalSettings.address_fields;
  // Use the magento field name from mapping.
  const responseData = await getLocations('attribute_id', mapping.area_parent.key);

  if (responseData !== null && responseData.total_count > 0) {
    return responseData;
  }
  logger.warning('No governates found in the list as count is zero, API Response: @response.', {
    '@response': JSON.stringify(responseData),
  });

  return {};
};

/**
 * Gets area List items.
 *
 * @returns {Promise<object>}
 *  returns list of area under a governate if governate id is valid.
 */
window.commerceBackend.getDeliveryAreaList = async (governateId) => {
  let responseData = null;
  if (governateId !== undefined) {
    // Get all area items if governate is none.
    if (governateId !== 'none') {
      responseData = await getLocations('parent_id', governateId);
    } else {
      responseData = await getLocations('attribute_id', 'area');
    }
    if (responseData !== null && responseData.total_count > 0) {
      return responseData;
    }
    logger.warning('No areas found under governate Id : @governateId, API Response: @response.', {
      '@response': JSON.stringify(responseData),
      '@governateId': governateId,
    });
  }
  return {};
};

/**
 * Gets individual area detail.
 *
 * @returns {Promise<object>}
 *  returns details of area if area id is valid.
 */
window.commerceBackend.getDeliveryAreaValue = async (areaId) => {
  if (areaId !== undefined) {
    const responseData = await getLocations('location_id', areaId);
    if (responseData !== null && responseData.total_count > 0) {
      return responseData;
    }
    logger.warning('No details found for area Id : @areaId, API Response: @response.', {
      '@response': JSON.stringify(responseData),
      '@areaId': areaId,
    });
  }
  return {};
};

/**
 * Gets governates list items.
 *
 * @returns {Promise<object>}
 *  returns list of governates.
 */
window.commerceBackend.getShippingMethods = async (currentArea, sku = undefined) => {
  let cartId = null;
  if (sku === undefined) {
    const cartData = window.commerceBackend.getCartDataFromStorage();
    if (cartData.cart.cart_id !== null) {
      cartId = cartData.cart.cart_id_int;
    }
  }
  const url = '/V1/deliverymatrix/get-applicable-shipping-methods';
  const attributes = [];
  if (currentArea !== null) {
    Object.keys(currentArea.value).forEach((key) => {
      const areaItemsObj = {
        attribute_code: key,
        value: currentArea.value[key],
      };
      attributes.push(areaItemsObj);
    });
  }
  try {
    const params = {
      productAndAddressInformation: {
        cart_id: cartId,
        product_sku: (sku !== undefined) ? sku : null,
        address: {
          custom_attributes: attributes,
        },
      },
    };

    // Associate cart to customer.
    const response = await callMagentoApi(url, 'POST', params);
    if (!hasValue(response.data) || hasValue(response.data.error)) {
      logger.error('Error occurred while fetching governates, Response: @response.', {
        '@response': JSON.stringify(response.data),
      });
      return null;
    }

    // If no city available, return empty.
    if (!hasValue(response.data)) {
      return null;
    }
    return response.data;
  } catch (error) {
    logger.error('Error occurred while fetching governates data. Message: @message.', {
      '@message': error.message,
    });
  }
  return {};
};

export {
  isAnonymousUserWithoutCart,
  isAuthenticatedUserWithoutCart,
  associateCartToCustomer,
  callDrupalApi,
  callMagentoApi,
  callMagentoApiSynchronous,
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
  getLocations,
  prepareFilterUrl,
};
