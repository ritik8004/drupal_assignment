import {
  callDrupalApi,
  callMagentoApi,
  getCartSettings,
  isAnonymousUserWithoutCart,
  updateCart,
  getCartWithProcessedData,
} from './common';
import { logger } from './utility';
import { getExceptionMessageType } from './error';
import { removeStorageInfo, setStorageInfo } from '../../utilities/storage';

window.commerceBackend = window.commerceBackend || {};

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
  const cart = window.commerceBackend.getRawCartDataFromStorage();
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
  const cart = window.commerceBackend.getRawCartDataFromStorage();
  if (!cart || (typeof cart.totals !== 'undefined' && Object.keys(cart.totals).length !== 0)) {
    return typeof cart.totals.coupon_code !== 'undefined'
      ? cart.totals.coupon_code
      : null;
  }

  return null;
};

/**
 * Formats the error message as required for cart.
 *
 * @param {int} code
 *   The response code.
 * @param {string} message
 *   The response message.
 */
const returnExistingCartWithError = (code, message) => ({
  data: {
    error: true,
    error_code: code,
    error_message: message,
    response_message: [message, 'error'],
  },
});

/**
 * Triggers the stock refresh process for the provided skus.
 *
 * @param {object} data
 *   Data containing sku and stock quantity information.
 */
const triggerStockRefresh = (data) => callDrupalApi(
  '/spc/checkout-event',
  'POST',
  {
    form_params: {
      action: 'refresh stock',
      skus_quantity: data,
    },
  },
).catch((error) => {
  logger.error(
    `Error occurred while triggering checkout event refresh stock. Message: ${error.message}`,
  );
});

/**
 * Check if user is anonymous and without cart.
 *
 * @returns bool
 */
window.commerceBackend.isAnonymousUserWithoutCart = () => isAnonymousUserWithoutCart();

/**
 * Applies transformations to the structure of cart data.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.getCart = () => getCartWithProcessedData();

/**
 * Calls the cart restore API.
 * @todo Implement restoreCart()
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.restoreCart = () => window.commerceBackend.getCart();

/**
 * Adds/removes/updates quantity of product in cart.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.addUpdateRemoveCartItem = async (data) => {
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
  let cartItem = null;

  if (data.action === 'remove item') {
    cartItem = getCartItem(sku);
    // Do nothing if item no longer available.
    if (!cartItem) {
      return window.commerceBackend.getCart();
    }
    // If it is free gift with coupon, remove coupon too.
    if (typeof cartItem.price !== 'undefined'
      && typeof cartItem.extension_attributes !== 'undefined'
      && typeof cartItem.extension_attributes.promo_rule_id !== 'undefined') {
      const appliedCoupon = getCoupon();
      if (appliedCoupon) {
        await window.commerceBackend.applyRemovePromo({ promo: appliedCoupon, action: 'remove coupon' });
      }
    }
    requestMethod = 'DELETE';
    requestUrl = `/rest/V1/guest-carts/${cartId}/items/${cartItem.item_id}`;
  }

  if (data.action === 'add item') {
    // If we try to add item while we don't have anything or corrupt
    // session, we create the cart object.
    cartId = window.commerceBackend.getCartId();
    if (!cartId) {
      cartId = await window.commerceBackend.createCart();
    }
    if (typeof cartId.error !== 'undefined') {
      return cartId;
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
    cartItem = getCartItem(sku);
    if (!cartItem) {
      // Do nothing if item no longer available.
      return window.commerceBackend.getCart();
    }
    // Set the cart item id to ensure we set new quantity instead of adding it.
    itemData.cartItem.item_id = cartItem.id;
  }

  // Do a sanity check before proceeding since an item can be removed in above processes.
  // Eg. in remove cart when promo code is removed.
  cartItem = getCartItem(sku);
  if ((data.action === 'update item' || data.action === 'remove item') && !cartItem) {
    // Do nothing if item no longer available.
    return window.commerceBackend.getCart();
  }

  let apiCallAttempts = 1;

  const response = await callMagentoApi(requestUrl, requestMethod, itemData);

  if (response.data.error === true) {
    if (response.data.error_code === 404) {
      // 400 errors happens when we try to post to invalid cart id.
      const postString = JSON.stringify(itemData);
      logger.error(`Error updating cart. Cart Id ${cartId}. Post string ${postString}`);
      // Remove the cart from storage.
      window.commerceBackend.removeCartDataFromStorage();
      removeStorageInfo('cart_id');

      if (
        data.action === 'add item'
        && parseInt(
          window.drupalSettings.cart.checkout_settings.max_native_update_attempts,
          10,
        ) > apiCallAttempts
      ) {
        apiCallAttempts += 1;
        // Create a new cart.
        cartId = await window.commerceBackend.createCart();
        if (typeof cartId.error !== 'undefined') {
          return cartId;
        }
        setStorageInfo('cart_id', cartId);
        const cartData = await window.commerceBackend.getCart();
        window.commerceBackend.setCartDataInStorage(cartData);
        return window.commerceBackend.addUpdateRemoveCartItem(data);
      }

      return response;
    }

    const exceptionType = getExceptionMessageType(response.data.error_message);
    if (exceptionType === 'OOS') {
      await triggerStockRefresh({ [sku]: 0 });
    } else if (exceptionType === 'not_enough') {
      await triggerStockRefresh({ [sku]: quantity });
    }

    return returnExistingCartWithError(response.data.error_code, response.data.error_message);
  }

  return window.commerceBackend.getCart();
};

/**
 * Applies/Removes promo code to the cart and returns the cart.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.applyRemovePromo = (data) => {
  const params = {
    extension: {
      action: data.action,
    },
  };
  if (typeof data.promo !== 'undefined' && data.promo) {
    params.coupon = data.promo;
  }
  return updateCart(params);
};

/**
 * Refreshes cart data and returns the cart.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.refreshCart = (data) => {
  const checkoutSettings = getCartSettings('checkout_settings');
  let postData = {
    extension: {
      action: 'refresh',
    },
  };

  if (checkoutSettings.cart_refresh_mode === 'full') {
    postData = data.postData;
  }

  return updateCart(postData);
};

/**
 * Creates a new cart and stores cart Id in the local storage.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.createCart = async () => {
  const response = await callMagentoApi('/rest/V1/guest-carts', 'POST', {});
  if (typeof response.data.error !== 'undefined') {
    return response.data;
  }
  setStorageInfo(response.data, 'cart_id');
  return response.data;
};
