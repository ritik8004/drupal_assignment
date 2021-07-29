import _isNull from 'lodash/isNull';
import _isEmpty from 'lodash/isEmpty';
import _isUndefined from 'lodash/isUndefined';
import _isString from 'lodash/isString';
import _isNumber from 'lodash/isNumber';
import {
  callDrupalApi,
  callMagentoApi,
  getCartSettings,
  isAnonymousUserWithoutCart,
  isAuthenticatedUserWithoutCart,
  updateCart,
  getProcessedCartData,
  getCartWithProcessedData, getCart, associateCartToCustomer,
} from './common';
import {
  getApiEndpoint,
  getCartIdFromStorage,
  isUserAuthenticated,
  logger,
  removeCartIdFromStorage,
} from './utility';
import { getExceptionMessageType } from './error';
import { setStorageInfo } from '../../utilities/storage';
import cartActions from '../../utilities/cart_actions';

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
 *
 * @returns {Promise<object>}
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
 * Returns the processed cart data.
 *
 * @param {boolean} force
 *   Force refresh cart data from magento.
 *
 * @returns {Promise<object>}
 *   A promise object.
 */
window.commerceBackend.getCart = (force = false) => getCartWithProcessedData(force);

/**
 * Calls the cart restore API.
 * @todo Implement restoreCart()
 *
 * @returns {Promise<object>}
 *   A promise object.
 */
window.commerceBackend.restoreCart = () => window.commerceBackend.getCart();

/**
 * Static variable to limit API attempts.
 */
let apiCallAttempts = 0;

/**
 * Adds/removes/updates quantity of product in cart.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise<object>}
 *   A promise object.
 */
window.commerceBackend.addUpdateRemoveCartItem = async (data) => {
  let requestMethod = null;
  let requestUrl = null;
  let itemData = null;

  let cartId = window.commerceBackend.getCartId();
  // If we try to add/remove item while we don't have anything or corrupt
  // session, we create the cart object.
  if (isAnonymousUserWithoutCart() || await isAuthenticatedUserWithoutCart()) {
    cartId = await window.commerceBackend.createCart();
    // If we still don't have a cart, we cannot continue.
    if (_isNull(cartId)) {
      throw new Error('Error creating cart when adding/removing item.');
    }
  }

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
    const params = {
      cartId,
      itemId: cartItem.item_id,
    };
    requestUrl = getApiEndpoint('removeItems', params);
  }

  if (data.action === 'add item' || data.action === 'update item') {
    requestMethod = 'POST';
    requestUrl = getApiEndpoint('addUpdateItems', { cartId });
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
    itemData.cartItem.item_id = cartItem.item_id;
  }

  // Do a sanity check before proceeding since an item can be removed in above processes.
  // Eg. in remove cart when promo code is removed.
  cartItem = getCartItem(sku);
  if ((data.action === 'update item' || data.action === 'remove item') && !cartItem) {
    // Do nothing if item no longer available.
    return window.commerceBackend.getCart();
  }

  const response = await callMagentoApi(requestUrl, requestMethod, itemData);

  if (response.data.error === true) {
    logger.error('Error updating cart. CartId: @cartId. Post: @post, Response: @response', {
      '@cartId': cartId,
      '@post': JSON.stringify(itemData),
      '@response': JSON.stringify(response.data),
    });

    // 404 errors could happen when we try to post to invalid cart id.
    if (response.data.error_code === 404) {
      const freshCart = await getCart(true);

      // Try to load fresh cart, if this fails it means we need to create new one.
      if (_isEmpty(freshCart)) {
        // Remove the cart id from storage.
        window.commerceBackend.removeCartDataFromStorage(true);

        // Create new one and retry but only if user is trying to add item to cart.
        if (data.action === 'add item'
          && parseInt(
            drupalSettings.cart.checkout_settings.max_native_update_attempts,
            10,
          ) > apiCallAttempts) {
          apiCallAttempts += 1;

          // Create a new cart.
          cartId = await window.commerceBackend.createCart();
          if (_isNull(cartId)) {
            // Cart creation is also failing, simply return.
            return null;
          }

          return window.commerceBackend.addUpdateRemoveCartItem(data);
        }
      }

      // If cart is still available, it means something else is wrong.
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

  // Reset counter.
  apiCallAttempts = 0;

  return window.commerceBackend.getCart(true);
};

/**
 * Applies/Removes promo code to the cart and returns the cart.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise<object>}
 *   A promise object.
 */
window.commerceBackend.applyRemovePromo = async (data) => {
  const params = {
    extension: {
      action: data.action,
    },
  };

  if (typeof data.promo !== 'undefined' && data.promo) {
    params.coupon = data.promo;
  }

  return updateCart(params)
    .then(async (response) => {
      // Process cart data.
      response.data = await getProcessedCartData(response.data);
      return response;
    });
};

/**
 * Refreshes cart data and returns the cart.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise<object>}
 *   A promise object.
 */
window.commerceBackend.refreshCart = async (data) => {
  const checkoutSettings = getCartSettings('checkout_settings');
  let postData = {
    extension: {
      action: 'refresh',
    },
  };

  if (checkoutSettings.cart_refresh_mode === 'full') {
    postData = data.postData;
  }

  return updateCart(postData)
    .then(async (response) => {
      // Process cart data.
      response.data = await getProcessedCartData(response.data);
      return response;
    });
};

/**
 * Creates a new cart and stores cart Id in the local storage.
 *
 * @returns {promise<integer|null>}
 *   The cart id or null.
 */
window.commerceBackend.createCart = async () => {
  // Remove cart_id from storage.
  removeCartIdFromStorage();

  // Create new cart and return the data.
  const response = await callMagentoApi(getApiEndpoint('createCart'), 'POST', {});
  if (response.status === 200 && !_isUndefined(response.data)
    && (_isString(response.data) || _isNumber(response.data))
  ) {
    const Id = window.drupalSettings.userDetails.customerId;
    logger.notice(`New cart created: ${response.data}, customer_id: ${Id}`);

    // If its a guest customer, keep cart_id in the local storage.
    if (!isUserAuthenticated()) {
      setStorageInfo(response.data, 'cart_id');
    }

    return response.data;
  }

  const errorMessage = (!_isUndefined(response.data.error_message))
    ? response.data.error_message
    : '';
  logger.notice(`Error while creating cart on MDC. Error message: ${errorMessage}`);
  return null;
};

window.commerceBackend.associateCartToCustomer = async (pageType) => {
  // If user is not logged in, no further processing required.
  if (!isUserAuthenticated()) {
    return;
  }

  const guestCartId = getCartIdFromStorage();

  // No further checks required if card id not available in storage.
  if (_isEmpty(guestCartId)) {
    return;
  }

  // Try to load customer's cart if not doing this on checkout page.
  if (pageType !== 'checkout') {
    const cart = await getCart();
    if (!_isEmpty(cart.data.cart.items)) {
      // If the current cart has items, we carry on with this cart and remove
      // the guest cart id from local storage.
      removeCartIdFromStorage();
      return;
    }
  }

  // If the user is authenticated and we have cart_id in the local storage
  // it means the customer just became authenticated.
  // We need to associate the cart and remove the cart_id from local storage.
  await associateCartToCustomer();
};

/**
 * Adds free gift to the cart.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.addFreeGift = async (data) => {
  const { sku, promoRuleId } = data;
  const skuType = data.type;
  const langCode = data.langcode;
  const promoCode = data.promo;
  let cart = null;

  if (_isEmpty(sku) || _isEmpty(promoCode) || _isEmpty(langCode)) {
    logger.error(`Missing request header parameters. SKU: ${sku}, Promo: ${promoCode}, Langcode: ${langCode}`, {});
    cart = await window.commerceBackend.getCart();
  } else {
    // Apply promo code.
    cart = await window.commerceBackend.applyRemovePromo({
      promo: promoCode,
      action: cartActions.cartApplyCoupon,
    });

    // Validations.
    if (_isEmpty(cart.data)
      || (!_isUndefined(cart.data.error) && cart.data.error)
    ) {
      logger.error('Cart is empty. Cart: @cart', {
        '@cart': JSON.stringify(cart),
      });
    } else if (_isUndefined(cart.data.appliedRules)
      || _isEmpty(cart.data.appliedRules)
    ) {
      logger.error('Invalid promo code. Cart: @cart, Promo: @promoCode', {
        '@cart': JSON.stringify(cart.data),
        '@promoCode': promoCode,
      });
    } else {
      // Update cart with free gift.
      const params = { ...data };
      params.items = [];
      params.extension = {
        action: cartActions.cartAddItem,
      };

      if (skuType === 'simple') {
        params.items.push({
          sku,
          qty: 1,
          product_type: skuType,
          extension_attributes: {
            promo_rule_id: promoRuleId,
          },
        });
      } else {
        const options = (!_isEmpty(data.configurable_values)) ? data.configurable_values : [];
        params.items.push({
          sku,
          qty: 1,
          product_type: skuType,
          product_option: (_isEmpty(options))
            ? []
            : {
              extension_attributes: {
                configurable_item_options: options,
              },
            },
          variant_sku: (!_isEmpty(data.variant)) ? data.variant : null,
          extension_attributes: {
            promo_rule_id: promoRuleId,
          },
        });
      }

      // Update cart.
      const updated = await updateCart(params);
      // If cart update has error.
      if (_isEmpty(updated.data) || (!_isUndefined(updated.data.error) && updated.data.error)) {
        logger.error('Update cart failed. Cart: @cart', {
          '@cart': JSON.stringify(cart),
        });
      } else {
        if (!_isEmpty(updated.data) && _isUndefined(updated.data.error)) {
          updated.data = await getProcessedCartData(updated.data);
        }
        cart = updated;
      }
    }
  }

  return cart;
};
