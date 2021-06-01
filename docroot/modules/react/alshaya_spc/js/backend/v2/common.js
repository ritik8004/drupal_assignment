/* eslint no-return-await: "error" */

import Axios from 'axios';

/**
 * Check if user is anonymous and without cart.
 *
 * @returns bool
 */
const isAnonymousUserWithoutCart = () => {
  const cartId = window.commerceBackend.getCartId();
  if (cartId === null || typeof cartId === 'undefined') {
    if (drupalSettings.user.uid === 0) {
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
    },
  };

  if (typeof data !== 'undefined' && data && Object.keys(data).length > 0) {
    params.data = data;
  }

  // @todo error handling as found in MagentoApiWrapper::doRequest()
  return Axios(params);
};

/**
 * Object to serve as static cache for cart data over the course of a request.
 */
let cartData = null;

/**
 * Gets the stored cart data.
 */
const getCartData = () => cartData;

/**
 * Sets the cart data to static memory.
 *
 * @param {object} data
 *   The cart object to set.
 */
const setCartData = (data) => {
  const cartInfo = { ...data };
  cartInfo.last_update = new Date().getTime();
  cartData = cartInfo;
};

/**
 * Unsets the cart data in static memory.
 */
const removeCartData = () => {
  cartData = null;
};

/**
 * Calls the get cart API.
 *
 * @returns {promise}
 *   A promise object which resolves to a cart object or null.
 */
const getCart = () => {
  const cartId = window.commerceBackend.getCartId();
  if (cartId === null) {
    return new Promise((resolve) => resolve(cartId));
  }

  // @todo: Handle error.
  return callMagentoApi(`/rest/V1/guest-carts/${cartId}/getCart`, 'GET', {})
    .then((response) => window.commerceBackend.processCartData(response.data));
};

/**
 * Searches for the cart item for the provided SKU in the cart data.
 *
 * @param {string} sku
 *   The sku value.
 *
 * @returns {object|null}
 *   Returns the cart item if found else returns null.
 */
const getCartItem = (sku) => {
  const cart = getCartData();
  if (!cart || typeof cart.cart === 'undefined' || !cart.cart || typeof cart.cart.items === 'undefined' || cart.cart.items.length === 0) {
    return null;
  }

  const currentItem = Object.values(cart.cart.items).find((item) => item.sku === sku);
  if (typeof currentItem !== 'undefined') {
    return currentItem;
  }

  return null;
};

/**
 * Gets the coupon applied in the cart.
 *
 * @returns {string|null}
 *   The coupon code string or null.
 */
const getCoupon = () => {
  const cart = getCartData();
  if (!cart || (typeof cart.totals !== 'undefined' && Object.keys(cart.totals).length !== 0)) {
    return typeof cart.totals.coupon_code !== 'undefined'
      ? cart.totals.coupon_code
      : null;
  }

  return null;
};

/**
 * Calls the update cart API and returns the updated cart.
 * @todo Implement this function while working on the checkout page.
 *
 * @param {object} data
 *  The data to send.
 */
const updateCart = async () => null;

/**
 * Adds/removes/updates quantity of product in cart.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise}
 *   A promise object.
 */
const addUpdateRemoveCartItem = async (data) => {
  let requestMethod = null;
  let requestUrl = null;
  let itemData = null;
  let cartId = window.commerceBackend.getCartId();
  let productOptions = {};
  const quantity = typeof data.quantity !== 'undefined' && data.quantity
    ? data.quantity
    : 1;
  const sku = typeof data.variant_sku !== 'undefined' && data.variant_sku
    ? data.variant_sku
    : data.sku;

  if (data.action === 'remove item') {
    const cartItem = getCartItem(sku);
    // Do nothing if item no longer available.
    if (!cartItem) {
      return getCart();
    }
    // If it is free gift with coupon, remove coupon too.
    if (typeof cartItem.price !== 'undefined'
      && typeof cartItem.extension_attributes !== 'undefined'
      && typeof cartItem.extension_attributes.promo_rule_id !== 'undefined') {
      const appliedCoupon = getCoupon();
      if (appliedCoupon) {
        // @todo Implement this function.
        // window.commerceBackend.applyRemovePromo({promo: appliedCoupon, action: 'remove coupon'});
      }
    }
    requestMethod = 'DELETE';
    requestUrl = `/rest/V1/guest-carts/${cartId}/items/${cartItem.id}`;
  }

  if (data.action === 'add item') {
    // If we try to add item while we don't have anything or corrupt
    // session, we create the cart object.
    cartId = window.commerceBackend.getCartId();
    if (!cartId) {
      cartId = await window.commerceBackend.createCart();
    }
    if (Array.isArray(cartId)) {
      return new Promise((resolve) => resolve(cartId));
    }
    // @todo: Associate cart to the customer.
  }

  if (data.action === 'add item' || data.action === 'update item') {
    requestMethod = 'POST';
    requestUrl = `/rest/V1/guest-carts/${cartId}/items`;
    // Executed for Add and Update case.
    if (typeof data.options !== 'undefined' && data.options.length > 0) {
      productOptions = {
        extension_attributes: {
          configurable_item_options: data.options,
        },
      };
    }
    itemData = {
      cartItem: {
        sku,
        qty: quantity,
        product_option: productOptions,
        quote_id: cartId,
      },
    };
  }

  if (data.action === 'update item') {
    const cartItem = getCartItem(sku);
    if (!cartItem) {
      // Do nothing if item no longer available.
      return getCart();
    }
    // Set the cart item id to ensure we set new quantity instead of adding it.
    itemData.cartItem.item_id = cartItem.id;
  }

  const response = await callMagentoApi(requestUrl, requestMethod, itemData);
  // @todo: Handle all the different errors.
  if (response.data.error === true) {
    if (typeof response.data.error_message === 'undefined') {
      response.data.error_message = 'Error adding item to the cart.';
    }
    const error = {
      data: response.data,
    };
    return new Promise((resolve) => resolve(error));
  }

  return window.commerceBackend.getCart();
};

export {
  addUpdateRemoveCartItem,
  isAnonymousUserWithoutCart,
  callMagentoApi,
  updateCart,
  getCart,
  getCartData,
  setCartData,
  removeCartData,
};
