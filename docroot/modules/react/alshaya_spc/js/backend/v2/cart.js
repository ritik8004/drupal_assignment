import {
  isAnonymousUserWithoutCart,
  isAuthenticatedUserWithoutCart,
  updateCart,
  getProcessedCartData,
  getCartWithProcessedData,
  getCart,
  associateCartToCustomer,
  clearProductStatusStaticCache,
  mergeGuestCartToCustomer,
} from './common';
import {
  getApiEndpoint,
  isUserAuthenticated,
  removeCartIdFromStorage,
  isRequestFromSocialAuthPopup,
} from './utility';
import logger from '../../../../js/utilities/logger';
import cartActions from '../../utilities/cart_actions';
import {
  hasValue,
  isString,
  isNumber,
} from '../../../../js/utilities/conditionsUtility';
import {
  callMagentoApi,
  getCartSettings,
} from '../../../../js/utilities/requestHelper';
import { getExceptionMessageType } from '../../../../js/utilities/error';
import { getTopUpQuote } from '../../../../js/utilities/egiftCardHelper';
import { isEgiftCardEnabled } from '../../../../js/utilities/util';
import { removeRedemptionOnCartUpdate } from '../../utilities/egift_util';

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
 * Check if user is anonymous and without cart.
 *
 * @returns bool
 */
window.commerceBackend.isAnonymousUserWithoutCart = () => isAnonymousUserWithoutCart();

/**
 * Check if user is authenticated and without cart.
 *
 * @returns bool
 */
window.commerceBackend.isAuthenticatedUserWithoutCart = () => isAuthenticatedUserWithoutCart();

/**
 * Returns the processed cart data.
 *
 * @todo check why getCart in V1 and V2 are different
 * In V1 it does API call all the time.
 * In V2 it loads from static cache if available.
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
 * Adds/removes/updates quantity of product in cart.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise<object>}
 *   A promise object.
 */
window.commerceBackend.addUpdateRemoveCartItem = async (data) => {
  // If request is from SocialAuth Popup, restrict further processing.
  // we don't want magento API calls happen on popup, As this is causing issues
  // in processing parent pages.
  if (isRequestFromSocialAuthPopup()) {
    return null;
  }

  let requestMethod = null;
  let requestUrl = null;
  let itemData = null;

  let cartId = window.commerceBackend.getCartId();
  // If we try to add/remove item while we don't have anything or corrupt
  // session, we create the cart object.
  if (isAnonymousUserWithoutCart() || await isAuthenticatedUserWithoutCart()) {
    cartId = await window.commerceBackend.createCart();
    // If we still don't have a cart, we cannot continue.
    if (!hasValue(cartId)) {
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
        sku: data.sku,
        qty: quantity,
        product_option: productOptions,
        quote_id: cartId,
      },
    };

    // Check if product type is virtual (eg: eGift card), if product type
    // is virtual then update product type and options to cart item.
    if (data.product_type === 'virtual') {
      itemData.cartItem.product_type = data.product_type;
      itemData.cartItem.product_option = data.options;
    }
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

  if (hasValue(response.data) && hasValue(response.data.error)) {
    logger.warning('Error updating cart. CartId: @cartId. Post: @post, Response: @response', {
      '@cartId': cartId,
      '@post': JSON.stringify(itemData),
      '@response': JSON.stringify(response.data),
    });

    // 404 errors could happen when we try to post to invalid cart id.
    if (response.data.error_code === 404) {
      const freshCart = await getCart(true);

      // Try to load fresh cart, if this fails it means we need to create new one.
      if (!hasValue(freshCart)) {
        // Remove the cart id from storage.
        window.commerceBackend.removeCartDataFromStorage(true);

        const apiCallAttempts = Drupal.alshayaSpc.staticStorage.get('apiCallAttempts') || 0;

        // Create new one and retry but only if user is trying to add item to cart.
        if (data.action === 'add item'
          && parseInt(getCartSettings('retryMaxAttempts'), 10) > apiCallAttempts) {
          Drupal.alshayaSpc.staticStorage.set('apiCallAttempts', (apiCallAttempts + 1));

          // Create a new cart.
          cartId = await window.commerceBackend.createCart();
          if (!hasValue(cartId)) {
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
      window.commerceBackend.triggerStockRefresh({ [sku]: 0 });
    } else if (exceptionType === 'not_enough') {
      window.commerceBackend.triggerStockRefresh({ [sku]: quantity });
    }

    return returnExistingCartWithError(response.data.error_code, response.data.error_message);
  }

  // Reset counter.
  Drupal.alshayaSpc.staticStorage.remove('apiCallAttempts');

  const cartData = await window.commerceBackend.getCart(true);
  // Remove redemption of egift when feature is enabled and redemption is
  // already applied.
  if (isEgiftCardEnabled()) {
    // As we don't get cart object here in this function, we need to get a fresh
    // cart to check if redemption is already applied in the cart.
    await removeRedemptionOnCartUpdate(cartData.data);
  }
  // Return a promise object.
  return new Promise((resolve) => {
    resolve(cartData);
  });
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
      // Remove redemption of egift when feature is enabled and redemption is
      // already applied.
      if (isEgiftCardEnabled()) {
        await removeRedemptionOnCartUpdate(response.data);
      }
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
  let postData = {
    extension: {
      action: 'refresh',
    },
  };

  if (getCartSettings('refreshMode') === 'full') {
    postData = data.postData;
  }

  return updateCart(postData)
    .then(async (response) => {
      // If we get OOS error on refresh cart, then we clear the static stock
      // storages so that fresh stock data is fetched.
      if (hasValue(response.data.response_message)
        && response.data.response_message[1] === 'json_error'
        && response.data.response_message[0].indexOf('out of stock') > -1) {
        clearProductStatusStaticCache();
        window.commerceBackend.clearStockStaticCache();
        // Refresh stock for all items in the cart.
        window.commerceBackend.triggerStockRefresh({});
      }
      // Process cart data.
      response.data = await getProcessedCartData(response.data);
      // Remove redemption of egift when feature is enabled and redemption is
      // already applied.
      if (isEgiftCardEnabled()) {
        await removeRedemptionOnCartUpdate(response.data);
      }
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
  // If request is from SocialAuth Popup, restrict further processing.
  // we don't want magento API calls happen on popup, As this is causing issues
  // in processing parent pages.
  if (isRequestFromSocialAuthPopup()) {
    return null;
  }

  // Remove cart_id from storage.
  removeCartIdFromStorage();

  // Create new cart and return the data.
  const response = await callMagentoApi(getApiEndpoint('createCart'), 'POST', {});
  if (response.status === 200 && hasValue(response.data)
    && (isString(response.data) || isNumber(response.data))
  ) {
    logger.notice('New cart created: @cartId, for Customer: @customerId.', {
      '@cartId': response.data,
      '@customerId': window.drupalSettings.userDetails.customerId,
    });

    // If its a guest customer, keep cart_id in the local storage.
    if (!isUserAuthenticated()) {
      Drupal.addItemInLocalStorage('cart_id', response.data);
    }

    // Get fresh cart once to ensure static caches are warm.
    await getCart(true);

    return response.data;
  }

  const errorMessage = (hasValue(response.data.error_message))
    ? response.data.error_message
    : '';
  logger.warning('Error while creating cart on MDC. Error: @message', {
    '@message': errorMessage,
  });
  return null;
};

window.commerceBackend.associateCartToCustomer = async (pageType) => {
  // If request is from SocialAuth Popup, restrict further processing.
  // we don't want magento API calls happen on popup, As this is causing issues
  // in processing parent pages.
  if (isRequestFromSocialAuthPopup()) {
    return;
  }
  // If user is not logged in, no further processing required.
  // We are not suppose to call associated cart for customer if user is doing
  // topup.
  if (!isUserAuthenticated() || getTopUpQuote() !== null) {
    return;
  }

  const guestCartId = window.commerceBackend.getCartIdFromStorage();

  // No further checks required if card id not available in storage.
  if (!hasValue(guestCartId)) {
    return;
  }

  Drupal.alshayaSpc.staticStorage.set('associating_cart', true);

  if (document.referrer.indexOf('cart/login') > -1 && pageType === 'checkout') {
    // If the user is authenticated and we have cart_id in the local storage
    // it means the customer just became authenticated from cart login.
    // We need to associate the cart and remove the cart_id from local storage.
    await associateCartToCustomer(guestCartId);
  } else {
    // If user is authenticated and we have cart_id, user has logged in from
    // other than cart login eg: user login, social login, user register then
    // we merge guest cart with customer.
    await mergeGuestCartToCustomer();
  }

  Drupal.alshayaSpc.staticStorage.remove('associating_cart');
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

  if (!hasValue(sku) || !hasValue(promoCode) || !hasValue(langCode)) {
    logger.error('Missing request header parameters. SKU: @sku, Promo: @promoCode, Langcode: @langCode', {
      '@sku': sku || '',
      '@promoCode': promoCode || '',
      '@langCode': langCode || '',
    });
    cart = await window.commerceBackend.getCart();
  } else {
    // Apply promo code.
    cart = await window.commerceBackend.applyRemovePromo({
      promo: promoCode,
      action: cartActions.cartApplyCoupon,
    });

    // Validations.
    if (!hasValue(cart.data)
      || (hasValue(cart.data.error) && cart.data.error)
    ) {
      logger.warning('Cart is empty. Cart: @cart', {
        '@cart': JSON.stringify(cart),
      });
    } else if (!hasValue(cart.data.appliedRules)) {
      logger.warning('Invalid promo code. Cart: @cart, Promo: @promoCode', {
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
        const options = (hasValue(data.configurable_values)) ? data.configurable_values : [];
        params.items.push({
          sku,
          qty: 1,
          product_type: skuType,
          product_option: (!hasValue(options))
            ? []
            : {
              extension_attributes: {
                configurable_item_options: options,
              },
            },
          variant_sku: (hasValue(data.variant)) ? data.variant : null,
          extension_attributes: {
            promo_rule_id: promoRuleId,
          },
        });
      }

      // Update cart.
      const updated = await updateCart(params);
      // If cart update has error.
      if (!hasValue(updated.data) || (hasValue(updated.data.error) && updated.data.error)) {
        logger.warning('Update cart failed. Cart: @cart', {
          '@cart': JSON.stringify(cart),
        });
      } else {
        if (hasValue(updated) && hasValue(updated.data)) {
          updated.data = await getProcessedCartData(updated.data);
        }
        cart = updated;
      }
    }
  }

  return cart;
};
