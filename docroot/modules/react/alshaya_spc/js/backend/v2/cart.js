import {
  callMagentoApi,
  isAnonymousUserWithoutCart,
  updateCart,
} from './common';
import { logger } from './utility';
import { getDefaultErrorMessage } from './error';
import { removeStorageInfo } from '../../utilities/storage';

window.commerceBackend = window.commerceBackend || {};

/**
 * Object to serve as static cache for cart data over the course of a request.
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
window.commerceBackend.setCartData = (data) => {
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
 * Check if user is anonymous and without cart.
 *
 * @returns bool
 */
window.commerceBackend.isAnonymousUserWithoutCart = () => isAnonymousUserWithoutCart();

/**
 * Calls the cart get API.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.getCart = async () => {
  const cartId = window.commerceBackend.getCartId();
  if (cartId === null) {
    return new Promise((resolve) => resolve(cartId));
  }

  const response = await callMagentoApi(`/rest/V1/guest-carts/${cartId}/getCart`, 'GET', {});

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

  // Process data.
  response.data = window.commerceBackend.processCartData(response.data);
  return new Promise((resolve) => resolve(response));
};

/**
 * Adds item to the cart and returns the cart.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.addUpdateRemoveCartItem = (data) => updateCart(data);

/**
 * Calls the cart restore API.
 * @todo Implement restoreCart()
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.restoreCart = () => window.commerceBackend.getCart();

/**
 * Adds item to the cart and returns the cart.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.addToCart = (data) => updateCart(data);

/**
 * Applies/Removes promo code to the cart and returns the cart.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.applyRemovePromo = (data) => updateCart(data);

/**
 * Adds/Removes/Changes quantity of items in the cart and returns the cart.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.updateCartItemData = (data) => updateCart(data);

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

/**
 * Creates a new cart and stores cart Id in the local storage.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.createCart = async () => {
  const response = await callMagentoApi('/rest/V1/guest-carts', 'POST', {});
  localStorage.setItem('cart_id', response.data);
  return response.data;
};

/**
 * Transforms cart data to match the data structure from middleware.
 *
 * @param {object} cartData
 *   The cart data object.
 */
window.commerceBackend.processCartData = (cartData) => {
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
