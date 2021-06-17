import {
  callDrupalApi,
  callMagentoApi,
  getCartSettings,
  isAnonymousUserWithoutCart,
} from './common';
import { logger } from './utility';
import { getDefaultErrorMessage, getExceptionMessageType } from './error';
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
 * Format the cart data to have better structured array.
 *
 * @param {object} cartData
 *   Cart response from Magento.
 *
 * @return {object}
 *   Formatted / processed cart.
 */
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
  const data = { ...cartData };
  // Move customer data to root level.
  data.customer = cartData.cart.customer;
  delete data.cart.customer;
  // Format addresses.
  if (typeof data.customer.addresses !== 'undefined' && data.customer.addresses.length > 0) {
    data.customer.addresses = data.customer.addresses.map((address) => {
      const item = { ...address };
      delete item.id;
      item.region = address.region_id;
      item.customer_address_id = address.id;
      return item;
    });
  }
  // Format shipping info.
  if (typeof cartData.cart.extension_attributes.shipping_assignments[0] !== 'undefined') {
    data.shipping = cartData.cart.extension_attributes.shipping_assignments[0].shipping;
    delete data.cart.extension_attributes.shipping_assignments;
  }
  let shippingMethod = '';
  if (typeof data.shipping.method !== 'undefined' && data.shipping.method !== null) {
    shippingMethod = data.shipping.method;
  }
  if (shippingMethod.indexOf('click_and_collect') >= 0) {
    data.shipping.type = 'click_and_collect';
  } else {
    data.shipping.type = 'home_delivery';
  }

  if (typeof cartData.cart.shipping !== 'undefined') {
    if (typeof cartData.cart.shipping.extension_attributes.click_and_collect_type !== 'undefined') {
      const attributes = cartData.cart.shipping.extension_attributes;
      data.shipping.clickCollectType = attributes.click_and_collect_type;
    }
    if (typeof cartData.cart.shipping.extension_attributes.store_code !== 'undefined') {
      data.shipping.storeCode = cartData.cart.shipping.store_code.click_and_collect_type;
    }
  }

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
 * @returns {Promise}
 *   A promise object.
 */
const getCart = async () => {
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

  // Store the response.
  window.commerceBackend.setRawCartDataInStorage(response.data);
  // Format data.
  response.data = formatCart(response.data);
  // Return formatted cart.
  return new Promise((resolve) => resolve(response));
};

/**
 * Calls the update cart API and returns the updated cart.
 * @todo Implement this function fully while working on the checkout page.
 *
 * @param {object} data
 *  The data to send.
 */
const updateCart = (data) => {
  const cartId = window.commerceBackend.getCartId();

  return callMagentoApi(`/rest/V1/guest-carts/${cartId}/updateCart`, 'POST', JSON.stringify(data))
    .then((response) => {
      if (typeof response.data.error !== 'undefined' && response.data.error) {
        return response;
      }
      // Update the cart data in storage.
      window.commerceBackend.setRawCartDataInStorage(response.data);
      // Process the cart data.
      response.data = getProcessedCartData(response.data);

      return response;
    });
};

/**
 * Gets the cart data.
 *
 * @returns {Promise}
 *   Processed cart data else null.
 */
window.commerceBackend.getCart = async () => {
  const response = await getCart();
  response.data = getProcessedCartData(response.data);
  return new Promise((resolve) => resolve(response));
};

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

export {
  getCart,
  updateCart,
  getProcessedCartData,
};
