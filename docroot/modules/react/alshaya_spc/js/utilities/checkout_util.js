import axios from 'axios';
import {
  removeCartFromStorage,
  getStorageInfo,
  getInfoFromStorage,
} from './storage';
import { updateCartApiUrl } from './update_cart';
import { cartAvailableInStorage } from './get_cart';
import getStringMessage from './strings';
import dispatchCustomEvent from './events';

/**
 * Get shipping methods.
 *
 * @param cartId
 * @param data
 * @returns {boolean}
 */
export const getShippingMethods = (cartId, data) => {
  const { middleware_url: middlewareUrl } = window.drupalSettings.alshaya_spc;

  return axios
    .post(`${middlewareUrl}/cart/shipping-methods`, {
      data,
      cartId,
    })
    .then(
      (response) => response.data,
      (error) => {
        // Processing of error here.
        Drupal.logJavascriptError('get-shipping-method', error);
      },
    );
};

/**
 * Place ajax fulll screen loader.
 */
export const showFullScreenLoader = () => {
  const loaderDivExisting = document.getElementsByClassName('ajax-progress-fullscreen');
  if (loaderDivExisting.length > 0) {
    return;
  }

  const loaderDiv = document.createElement('div');
  loaderDiv.className = 'ajax-progress ajax-progress-fullscreen';
  document.body.appendChild(loaderDiv);
};

/**
 * Remove ajax loader.
 */
export const removeFullScreenLoader = () => {
  const loaderDiv = document.getElementsByClassName('ajax-progress-fullscreen');
  while (loaderDiv.length > 0) {
    loaderDiv[0].parentNode.removeChild(loaderDiv[0]);
  }
};

/**
 * Place order.
 *
 * @param cart_id
 * @param paymentMethod
 * @returns {boolean}
 */
export const placeOrder = (paymentMethod) => {
  const { middleware_url: middlewareUrl } = window.drupalSettings.alshaya_spc;

  showFullScreenLoader();

  const data = {
    paymentMethod: {
      method: paymentMethod,
    },
  };
  return axios
    .post(`${middlewareUrl}/cart/place-order`, {
      data,
    })
    .then(
      (response) => {
        if (response.data.error === undefined) {
          // Remove cart info from storage.
          removeCartFromStorage();

          window.location = Drupal.url(response.data.redirectUrl);
          return;
        }

        dispatchCustomEvent('spcCheckoutMessageUpdate', {
          type: 'error',
          message: response.data.error_message,
        });

        removeFullScreenLoader();
      },
      (error) => {
        // Processing of error here.
        Drupal.logJavascriptError('place-order', error);
      },
    );
};

/**
 * Local storage key which we set when user change
 * billing address for HD. This key determines if
 * user billing shipping same or not.
 */
export const isBillingSameAsShippingInStorage = () => {
  const same = localStorage.getItem('billing_shipping_same');
  return (same === null || same === 'true');
};

/**
 * Remove billing address flag from storage.
 *
 * @param {*} cart
 */
export const removeBillingFlagFromStorage = (cart) => {
  // If cart doesn't have billing address
  // set or billing address city value is
  // 'NONE', we remove local storage.
  if (cart.cart !== undefined
    && (cart.cart.billing_address === null
      || cart.cart.billing_address.city === 'NONE')) {
    localStorage.removeItem('billing_shipping_same');
  }
};

export const addShippingInCart = (action, data) => {
  let cart = cartAvailableInStorage();
  if (cart === false) {
    return null;
  }

  if (!Number.isInteger(cart)) {
    cart = cart.cart_id;
  }

  const apiUrl = updateCartApiUrl();
  return axios
    .post(apiUrl, {
      action,
      shipping_info: data,
      cart_id: cart,
      update_billing: isBillingSameAsShippingInStorage(),
    })
    .then(
      (response) => {
        if (typeof response.data !== 'object') {
          removeFullScreenLoader();
          return null;
        }

        // If there is no error on shipping update.
        if (response.data.error === undefined) {
          // Trigger event on shipping update, so that
          // other components take necessary action if required.
          dispatchCustomEvent('onShippingAddressUpdate', response.data);
        }

        return response.data;
      },
      () => ({
        error: true,
        error_message: getStringMessage('global_error'),
      }),
    )
    .catch((error) => {
      Drupal.logJavascriptError('add-shipping-in-cart', error);
    });
};

/**
 * Adds billing in the cart.
 *
 * @param {*} action
 * @param {*} data
 */
export const addBillingInCart = (action, data) => {
  let cart = cartAvailableInStorage();
  if (cart === false) {
    return null;
  }

  if (!Number.isInteger(cart)) {
    cart = cart.cart_id;
  }

  const apiUrl = updateCartApiUrl();
  return axios
    .post(apiUrl, {
      action,
      billing_info: data,
      cart_id: cart,
    })
    .then(
      (response) => response.data,
      () => ({
        error: true,
        error_message: getStringMessage('global_error'),
      }),
    )
    .catch((error) => {
      Drupal.logJavascriptError('add-billing-in-cart', error);
    });
};

/**
 * Refresh cart from MDC.
 */
export const validateCartData = () => {
  const cartData = getInfoFromStorage();
  // If cart not available at all.
  if (!cartData
    || !cartData.cart
    || cartData.cart.cart_id === null) {
    return null;
  }

  const postData = {};
  const items = [];
  // Prepare data for cart update.
  Object.entries(cartData.cart.items).forEach(([key, value]) => {
    const item = {
      sku: key,
      qty: value.qty,
      quote_id: cartData.cart.cart_id,
    };
    items.push(item);
  });

  postData.items = items;

  // If coupon is applied on cart.
  if (cartData.cart.coupon_code !== undefined
    && cartData.cart.coupon_code.length > 0) {
    postData.coupon = cartData.cart.coupon_code;
  }

  const apiUrl = updateCartApiUrl();
  return axios
    .post(apiUrl, {
      action: 'refresh',
      cart_id: cartData.cart.cart_id,
      postData,
    })
    .then(
      (response) => response.data,
      () => ({
        error: true,
        error_message: getStringMessage('global_error'),
      }),
    )
    .catch((error) => {
      // Error processing here.
      Drupal.logJavascriptError('checkout-refresh-cart-data', error);
    });
};

/**
 * Get current location coordinates.
 */
export const getLocationAccess = () => {
  // If location access is enabled by user.
  if (navigator && navigator.geolocation) {
    return new Promise(
      (resolve, reject) => navigator.geolocation.getCurrentPosition(resolve, reject),
    );
  }

  return new Promise(
    (resolve) => resolve({}),
  );
};

export const getDefaultMapCenter = () => {
  if (typeof drupalSettings.map.center !== 'undefined' && ({}).hasOwnProperty.call(drupalSettings.map.center, 'latitude') && ({}).hasOwnProperty.call(drupalSettings.map.center, 'longitude')) {
    const { latitude: lat, longitude: lng } = drupalSettings.map.center;
    return { lat, lng };
  }
  return {};
};

/**
 * Clean mobile number array.
 */
export const cleanMobileNumber = (mobile) => {
  if (!mobile) {
    return '';
  }

  // If plain mobile number, return as is.
  if (typeof mobile === 'string') {
    return mobile.replace(`+${drupalSettings.country_mobile_code}`, '');
  }

  if (typeof mobile.value === 'string') {
    return mobile.value.replace(`+${drupalSettings.country_mobile_code}`, '');
  }

  return '';
};

/**
 * Check if new cart and existing storage has same number of items.
 *
 * @param {*} newCart
 */
export const cartLocalStorageHasSameItems = (newCart) => {
  const currentCart = getStorageInfo();
  const currentTotalItems = Object.keys(currentCart.cart.items).length;
  const newCartItems = Object.keys(newCart.items).length;
  if (newCartItems !== currentTotalItems) {
    return false;
  }

  return true;
};

/**
 * Validation on cart items.
 *
 * @param {*} cartResult
 * @param {*} redirect
 */
export const cartValidationOnUpdate = (cartResult, redirect) => {
  let sameNumberOfItems = true;
  // If no error or OOS.
  if (cartResult.error === undefined
    && cartResult.in_stock !== false
    && cartResult.is_error === false
    && (cartResult.response_message === null
      || cartResult.response_message.status !== 'error_coupon')) {
    // If storage has same number of items as we get in cart.
    sameNumberOfItems = cartLocalStorageHasSameItems(cartResult);
    if (sameNumberOfItems === true
      && redirect === true) {
      const continueCheckoutLink = (drupalSettings.user.uid === 0)
        ? 'cart/login'
        : 'checkout';

      // Redirect to next page.
      window.location.href = Drupal.url(continueCheckoutLink);
      return;
    }
  }

  // If error/exception, show at cart top.
  if (cartResult.error !== undefined) {
    dispatchCustomEvent('spcCartMessageUpdate', {
      type: 'error',
      message: cartResult.error_message,
    });
    return;
  }

  // If error from invalid coupon.
  if (cartResult.response_message !== null
    && cartResult.response_message.status === 'error_coupon') {
    // Calling 'promo' error event.
    dispatchCustomEvent('spcCartPromoError', {
      message: cartResult.response_message.msg,
    });
  }

  // Calling refresh mini cart event so that storage is updated.
  dispatchCustomEvent('refreshMiniCart', {
    data: () => cartResult,
  });

  // Calling refresh cart event so that cart components
  // are refreshed.
  dispatchCustomEvent('refreshCart', {
    data: () => cartResult,
  });

  // If items count we get from MDC update and what in
  // local storage different, we show message on top.
  if (sameNumberOfItems === false) {
    // Dispatch event for error to show.
    dispatchCustomEvent('spcCartMessageUpdate', {
      type: 'error',
      message: Drupal.t('Sorry, one or more products in your basket are no longer available and were removed from your basket.'),
    });
    return;
  }

  if (cartResult.is_error) {
    // Dispatch event for error to show.
    dispatchCustomEvent('spcCartMessageUpdate', {
      type: null,
      message: null,
    });
  }
};

/**
 * Check if CnC enabled or not.
 *
 * @param {*} cart
 */
export const isCnCEnabled = (cart) => {
  const { cnc_enabled: cncEnabled } = cart;
  const cncGlobaleEnable = drupalSettings.cnc_enabled;

  let cncAvailable = true;
  // If CNC is disabled.
  if (!cncGlobaleEnable || !cncEnabled) {
    cncAvailable = false;
  }

  return cncAvailable;
};

/**
 * Determines if delivery method set in cart is same as user
 * selected or not.
 */
export const isDeliveryTypeSameAsInCart = (cart) => {
  // If not set, means user didn;t change.
  if (cart.delivery_type === undefined) {
    return true;
  }

  if (cart.delivery_type !== undefined
    && cart.delivery_type === cart.cart.delivery_type) {
    return true;
  }

  return false;
};

export const validateInfo = (data) => axios.post(Drupal.url('spc/validate-info'), data);

export const isQtyLimitReached = (msg) => msg.indexOf('The maximum quantity per item has been exceeded');
