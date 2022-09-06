import axios from 'axios';
import getStringMessage from './strings';
import dispatchCustomEvent from './events';
import validateCartResponse from './validation_util';
import { hasValue } from '../../../js/utilities/conditionsUtility';
import {
  cartContainsAnyVirtualProduct,
  cartContainsOnlyVirtualProduct, isEgiftUnsupportedPaymentMethod,
} from './egift_util';
import { addPaymentMethodInCart } from './update_cart';
import { isEgiftCardEnabled } from '../../../js/utilities/util';

/**
 * Change the interactiveness of CTAs to avoid multiple user clicks.
 *
 * @param status
 */
export const controlAddressFormCTA = (status) => {
  const addressCTA = document.getElementsByClassName('spc-address-form-submit');
  // While we expect only one CTA. We loop just to ensure we dont break anything
  // if we get multiple, what we are doing is harmless for out of focus CTAs.
  if (addressCTA.length > 0) {
    switch (status) {
      case 'disable':
        for (let i = 0; i < addressCTA.length; i++) {
          addressCTA[i].classList.add('loading');
        }
        break;

      case 'enable':
        for (let i = 0; i < addressCTA.length; i++) {
          addressCTA[i].classList.remove('loading');
        }
        break;

      default:
        // Do nothing.
        break;
    }
  }
};

/**
 * Payment methods data.
 *
 * @return {object}
 *   The result object containing the information of payment methods API name and title.
 */
export const getPaymentMethodsData = () => {
  const methodsData = [];
  if (drupalSettings.payment_methods) {
    Object.entries(drupalSettings.payment_methods).forEach(
      ([key, value]) => {
        methodsData[key] = value.name;
      },
    );
  }
  return methodsData;
};

/**
 * Place order CTA link add loading to avoid multiple clicks.
 *
 * @param status
 */
export const controlPlaceOrderCTA = (status) => {
  const addressCTA = document.querySelector('.complete-purchase a, .complete-purchase button');
  switch (status) {
    case 'disable':
      addressCTA.classList.add('loading');
      break;

    case 'enable':
      addressCTA.classList.remove('loading');
      break;

    default:
      // Do nothing.
      break;
  }
};

/**
 * Place ajax full screen loader.
 */
export const showFullScreenLoader = () => {
  const loaderDivExisting = document.getElementsByClassName('ajax-progress-fullscreen');
  if (loaderDivExisting.length > 0) {
    return;
  }

  controlAddressFormCTA('disable');
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
  controlAddressFormCTA('enable');
};

/**
 * Place order.
 *
 * @param paymentMethod
 * @returns {boolean}
 */
export const placeOrder = (paymentMethod) => {
  showFullScreenLoader();
  controlPlaceOrderCTA('disable');

  const data = {
    paymentMethod: {
      method: paymentMethod,
    },
  };
  const paymentMethodsInfo = getPaymentMethodsData();

  window.commerceBackend.placeOrder({ data })
    .then(
      (response) => {
        if (paymentMethod === 'postpay' && hasValue(response.data.token)) {
          window.postpay.checkout(response.data.token, {
            locale: drupalSettings.postpay_widget_info['data-locale'],
          });
          return;
        }

        if (hasValue(response.data.redirectUrl)) {
          // Add current logs as is with current conditions.
          // @todo review this and make it appropriate / logical.
          if (response.data.error) {
            Drupal.logJavascriptError(`place-order | ${paymentMethodsInfo.[paymentMethod]}`, 'Redirecting user for 3D verification for 2D card.', GTM_CONSTANTS.PAYMENT_ERRORS);
          }

          // If url is absolute, then redirect to the external payment page.
          if (hasValue(response.data.isAbsoluteUrl)) {
            window.location.href = response.data.redirectUrl;
            return;
          }

          // Dispatch an event after order is placed before redirecting to confirmation page.
          dispatchCustomEvent('orderPlaced', true);
          // This here possibly means that we are redirecting to confirmation page.
          window.location = Drupal.url(response.data.redirectUrl);
          return;
        }

        let message = response.data.error_message;
        const errorCode = hasValue(response.data.error_code)
          ? parseInt(response.data.error_code, 10)
          : null;

        if (errorCode === 505) {
          message = getStringMessage('shipping_method_error');
        } else if (errorCode === 506) {
          // If cart has some OOS item.
          Drupal.logJavascriptError(`place-order | ${paymentMethodsInfo.[paymentMethod]}`, `${paymentMethod}: ${response.data.error_message}`, GTM_CONSTANTS.CHECKOUT_ERRORS);
        }

        validateCartResponse(response.data);

        dispatchCustomEvent('spcCheckoutMessageUpdate', {
          type: 'error',
          message,
        });

        // Push error to GTM.
        Drupal.logJavascriptError(`place-order | ${paymentMethodsInfo.[paymentMethod]}`, `${paymentMethod}: ${response.data.error_message}`, GTM_CONSTANTS.GENUINE_PAYMENT_ERRORS);
        removeFullScreenLoader();
        controlPlaceOrderCTA('enable');

        // Enable the 'place order' CTA.
        dispatchCustomEvent('updatePlaceOrderCTA', {
          status: true,
        });
      },
      (error) => {
        // Processing of error here.
        Drupal.logJavascriptError(`place-order | ${paymentMethodsInfo.[paymentMethod]}`, error, GTM_CONSTANTS.GENUINE_PAYMENT_ERRORS);
      },
    );
};

/**
 * Local storage key which we set when user change
 * billing address for HD. This key determines if
 * user billing shipping same or not.
 */
export const isBillingSameAsShippingInStorage = () => {
  const same = Drupal.getItemFromLocalStorage('billing_shipping_same');
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
    Drupal.removeItemFromLocalStorage('billing_shipping_same');
  }
};

/**
 * set billing address flag in storage.
 *
 * @param {*} cart
 */
export const setBillingFlagInStorage = (cart) => {
  if (cart.cart_id !== undefined
    && cart.shipping.type === 'home_delivery'
    && isBillingSameAsShippingInStorage()) {
    Drupal.addItemInLocalStorage('billing_shipping_same', true);
  }
};

export const addShippingInCart = (action, data) => window.commerceBackend.addShippingMethod({
  action,
  shipping_info: data,
  update_billing: isBillingSameAsShippingInStorage(),
})
  .then(
    (response) => {
      if (typeof response.data !== 'object') {
        removeFullScreenLoader();
        return null;
      }

      if (hasValue(response.data.error)) {
        return response.data;
      }

      if (!validateCartResponse(response.data)) {
        return null;
      }
      // If there is no error on shipping update.
      if (!hasValue(response.data.error)) {
        setBillingFlagInStorage(response.data);
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
    Drupal.logJavascriptError('add-shipping-in-cart', error, GTM_CONSTANTS.CHECKOUT_ERRORS);
  });

/**
 * Adds billing in the cart.
 *
 * @param {*} action
 * @param {*} data
 */
export const addBillingInCart = (action, data) => window.commerceBackend.addBillingMethod({
  action,
  billing_info: data,
})
  .then(
    (response) => {
      if (!validateCartResponse(response.data)) {
        return null;
      }
      return response.data;
    },
    () => ({
      error: true,
      error_message: getStringMessage('global_error'),
    }),
  )
  .catch((error) => {
    Drupal.logJavascriptError('add-billing-in-cart', error, GTM_CONSTANTS.CHECKOUT_ERRORS);
  });

/**
 * Refresh cart from MDC.
 */
export const validateCartData = () => {
  const cartData = window.commerceBackend.getCartDataFromStorage();
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

  return window.commerceBackend.refreshCart({
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
      Drupal.logJavascriptError('checkout-refresh-cart-data', error, GTM_CONSTANTS.CHECKOUT_ERRORS);
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
  const currentCart = window.commerceBackend.getCartDataFromStorage();
  const currentTotalItems = Object.keys(currentCart.cart.items).length;
  const newCartItems = Object.keys(newCart.items).length;
  if (newCartItems !== currentTotalItems) {
    return false;
  }

  return true;
};

/**
 * Send a custom message in FE for 604 status code.
 *
 * @param {*} cartResult
 */
export const customStockErrorMessage = (cartResult) => {
  let errorMessage = cartResult.error_message;
  if (cartResult.error_code === '604') {
    errorMessage = Drupal.t('The product that you are trying to add is not available.');
  }
  return errorMessage;
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
  if (cartResult
    && cartResult.error === undefined
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

      // Dispatch an event when user is moving to checkout page by
      // clicking on `continue to checkout` link.
      dispatchCustomEvent('continueToCheckoutFromCart', { cartResult });

      // Redirect to next page.
      window.location.href = Drupal.url(continueCheckoutLink);
      return;
    }
  }

  if (!validateCartResponse(cartResult)) {
    return;
  }

  // If error/exception, show at cart top.
  if (cartResult.error !== undefined) {
    const errorMessage = customStockErrorMessage(cartResult);
    dispatchCustomEvent('spcCartMessageUpdate', {
      type: 'error',
      message: errorMessage,
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
    const errmsg = 'Sorry, one or more products in your basket are no longer available and were removed from your basket.';
    // Dispatch event for error to show.
    dispatchCustomEvent('spcCartMessageUpdate', {
      type: 'error',
      message: Drupal.t('@errmsg', { '@errmsg': errmsg }),
    });
    Drupal.logJavascriptError('continue to checkout', errmsg, GTM_CONSTANTS.CART_ERRORS);
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
    && cart.delivery_type === cart.cart.shipping.type) {
    return true;
  }

  return false;
};

/**
 * Determines if shipping method is set in cart.
 *
 * @param {object} cart
 *   The cart object.
 */
export const isShippingMethodSet = (cart) => {
  // Set this as true if egift card is enabled and only virtual product is added
  // in the cart.
  if (cartContainsOnlyVirtualProduct(cart.cart)) {
    return true;
  }

  return cart.cart.shipping.method !== null;
};

/**
 * Get recommended products.
 *
 * @param {*} skus
 * @param {*} type
 */
export const getRecommendedProducts = (skus, type) => {
  const skuString = Object.keys(skus).map((key) => `skus[${key}]=${encodeURIComponent(skus[key])}`).join('&');

  return axios.get(Drupal.url(`products/cart-linked-skus?type=${type}&${skuString}&context=cart&cacheable=1`))
    .then((response) => response.data);
};

export const validateInfo = (data) => axios.post(Drupal.url('spc/validate-info'), data);

export const isQtyLimitReached = (msg) => msg.indexOf('The maximum quantity per item has been exceeded');

/**
 * Get amount with currency.
 *
 * @param priceAmount
 *   The price amount.
 * @param string
 *   True to Return amount with currency as string, false to return as array.
 *
 * @returns {string|*}
 *   Return string with price and currency or return array of price and
 *   currency.
 */
export const getAmountWithCurrency = (priceAmount, string = true) => {
  let amount = priceAmount === null ? 0 : priceAmount;

  // Remove commas if any.
  amount = amount.toString().replace(/,/g, '');
  amount = !Number.isNaN(Number(amount)) === true ? parseFloat(amount) : 0;

  const { currency_config: currencyConfig } = drupalSettings.alshaya_spc;
  // The keys currency and amount are used in PriceElement component.
  const priceParts = {
    currency: currencyConfig.currency_code,
    amount: amount.toFixed(currencyConfig.decimal_points),
  };

  const returnArray = currencyConfig.currency_code_position === 'before'
    ? priceParts
    : Object.assign([], priceParts).reverse();

  if (!string) {
    return returnArray;
  }

  return Object.values(returnArray).join(' ');
};

export const replaceCodTokens = (replacement, text) => {
  if (text.length === 0) {
    return '';
  }

  const textSplit = text.split('[surcharge]');
  return textSplit.reduce((prefix, suffix) => [prefix, replacement, suffix]);
};

/**
 * Validate cvv number.
 *
 * @param {string} cvv
 *   The cvv number to validate.
 *
 * @return {boolean}
 *   Return true if cvv number is valid else false.
 */
export function validateCvv(cvv) {
  const cvvLength = cvv.toString().length;
  return [3, 4].includes(cvvLength) && !Number.isNaN(cvv);
}

export const applyCode = (e) => {
  const codeValue = e.target.innerHTML;
  if (codeValue !== undefined) {
    document.getElementById('promo-code').value = codeValue.trim();
    document.getElementById('promo-action-button').click();
  }
};

let checkoutComUpapiApplePayConfig = {};
export const setUpapiApplePayCofig = () => {
  if (({}).hasOwnProperty.call(drupalSettings, 'checkoutComUpapiApplePay')) {
    checkoutComUpapiApplePayConfig = drupalSettings.checkoutComUpapiApplePay;
    Object.freeze(checkoutComUpapiApplePayConfig);
  }
};

export const getUpapiApplePayConfig = () => checkoutComUpapiApplePayConfig;

/**
 * Helper function to get bin validation config.
 */
export const getBinValidationConfig = () => {
  let config = {};
  if (typeof drupalSettings.checkoutComUpapi !== 'undefined'
    && ({}).hasOwnProperty.call(drupalSettings.checkoutComUpapi, 'binValidation')) {
    config = drupalSettings.checkoutComUpapi.binValidation || {};
  }

  return config;
};

/**
 * Bin validation.
 *
 * @param {*} bin
 */
export const binValidation = (bin) => {
  const { binValidationSupportedPaymentMethods, cardBinNumbers } = getBinValidationConfig();
  let valid = true;
  let errorMessage = 'invalid_card';

  binValidationSupportedPaymentMethods.split(',').every((paymentMethod) => {
    // If the given bin number matches with the bins of given payment method
    // then this card belongs to that payment method, so throw an error
    // asking user to use that payment method.
    const paymentMethodBinNumbers = cardBinNumbers[paymentMethod];

    if (paymentMethodBinNumbers !== undefined
      && Object.values(paymentMethodBinNumbers.split(',')).includes(bin)) {
      valid = false;
      errorMessage = `card_bin_validation_error_message_${paymentMethod}`;
      return false;
    }
    return true;
  });

  if (valid === false) {
    return ({ error: true, error_message: errorMessage });
  }

  return valid;
};

/**
 * Helper to get cnc store limit config.
 */
export const getCnCStoresLimit = () => drupalSettings.cnc_stores_limit || 0;

/**
 * Update payment method and then place order.
 *
 * @param {string} paymentMethod
 *   The paymentMethod using which user is placing order.
 */
export const updatePaymentAndPlaceOrder = (paymentMethod) => {
  const analytics = Drupal.alshayaSpc.getGAData();

  const data = {
    payment: {
      method: paymentMethod,
      additional_data: {},
      analytics,
    },
  };
  const paymentMethodsInfo = getPaymentMethodsData();

  const cartUpdate = addPaymentMethodInCart('update payment', data);
  if (cartUpdate instanceof Promise) {
    cartUpdate.then((result) => {
      if (!result) {
        // Remove loader in case of error.
        removeFullScreenLoader();

        dispatchCustomEvent('spcCheckoutMessageUpdate', {
          type: 'error',
          message: drupalSettings.globalErrorMessage,
        });
      } else {
        placeOrder(paymentMethod);
      }
    }).catch((error) => {
      Drupal.logJavascriptError(`change payment method | ${paymentMethodsInfo.[paymentMethod]}`, error, GTM_CONSTANTS.GENUINE_PAYMENT_ERRORS);
    });
  }
};

/**
 * Get next allowed payment method when virtual product is present.
 */
export const getNextAllowedPaymentMethodCode = (paymentMethods, cart) => {
  const sortedMethods = Object.values(paymentMethods).sort((a, b) => a.weight - b.weight);
  const cartHasVirtualProduct = cartContainsAnyVirtualProduct(cart.cart);
  let paymentMethodCode = null;
  if (isEgiftCardEnabled()) {
    for (let i = 0; i <= sortedMethods.length; i++) {
      if (cartHasVirtualProduct
        && !isEgiftUnsupportedPaymentMethod(sortedMethods[i].code)) {
        paymentMethodCode = sortedMethods[i].code;
        break;
      }
    }
  }
  return hasValue(paymentMethodCode) ? paymentMethodCode : sortedMethods[0].code;
};

/**
 * Returns balance payable amount if present.
 *
 * @returns {string}
 *   Amount.
 */
export const getPayable = (value) => {
  const amount = hasValue(value.cart.totals.totalBalancePayable)
    ? value.cart.totals.totalBalancePayable
    : value.cart.totals.base_grand_total;
  return amount;
};
