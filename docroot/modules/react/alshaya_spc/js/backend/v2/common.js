/* eslint no-return-await: "error" */

import Axios from 'axios';
import { logger } from './utility';
import { cartErrorCodes, getDefaultErrorMessage } from './error';
import { removeStorageInfo } from '../../utilities/storage';

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
 * @param {Promise} response
 *   The response from the API.
 *
 * @returns {Promise}
 *   Returns a promise object.
 */
const handleResponse = (response) => {
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
  if (response.status > 500) {
    // Server error responses.
    response.data.error = true;
    response.data.error_code = 600;
    response.data.error_message = 'Back-end system is down';
    //
  } else if (response.status === 404 || (typeof response.data.message !== 'undefined')) {
    // Client error responses.
    response.data.error = true;
    response.data.error_code = 404;
    response.data.error_message = response.data.message;
    //
  } else if (response.status !== 200) {
    // All other responses.
    response.data.error = true;
    if (typeof response.data.message !== 'undefined') {
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
    response.data.error = true;
    response.data.error_code = error.code;
    response.data.error_message = error.message;
    //
    logger.error(`Error while doing MDC api call. Error message no empty. Error message: ${error.message}`);
  }
  return response;
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

  if (typeof data !== 'undefined' && Object.keys(data).length > 0) {
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
 * Calls the update cart API.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise}
 *   A promise object.
 */
const updateCart = async (data) => {
  let response = null;
  let cartId = window.commerceBackend.getCartId();
  if (cartId === null) {
    cartId = await window.commerceBackend.createCart();
  }
  if (cartId === null) {
    return new Promise((resolve) => resolve(cartId));
  }

  const itemData = {
    cartItem: {
      sku: data.variant_sku,
      qty: data.quantity,
      quote_id: cartId,
    },
  };

  response = await callMagentoApi(`/rest/V1/guest-carts/${cartId}/items`, 'POST', itemData);
  if (response.data.error === true) {
    if (response.data.error_code === 404) {
      // 400 errors happens when we try to post to invalid cart id.
      const postString = JSON.stringify(itemData);
      logger.error(`Error updating cart. Cart Id ${cartId}. Post string ${postString}`);
      // Remove the cart from storage.
      removeStorageInfo('cart_id');
      // Create a new cart.
      await window.commerceBackend.createCart();
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

  return window.commerceBackend.getCart();
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
    customer: cartData.cart.customer,
    coupon_code: '', // @todo where to find this? cart.totals.coupon_code
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

  // @todo confirm this
  if (typeof cartData.response_message[1] !== 'undefined') {
    data.response_message = {
      status: cartData.response_message[1],
      msg: cartData.response_message[2],
    };
  }

  if (typeof cartData.cart.items !== 'undefined') {
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

      if (typeof item.extension_attributes !== 'undefined' && typeof item.extension_attributes.error_message !== 'undefined') {
        data.items[item.item_id].error_msg = item.extension_attributes.error_message;
        data.is_error = true;
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
          if (totalItem.base_price === 0 && typeof totalItem.extension_attributes !== 'undefined' && typeof totalItem.amasty_promo !== 'undefined') {
            data.items[item.sku].freeItem = true;
          }
        }
      });

      // @todo Get stock data.
    });
  }
  return data;
};

export {
  isAnonymousUserWithoutCart,
  callMagentoApi,
  updateCart,
  getProcessedCartData,
};
