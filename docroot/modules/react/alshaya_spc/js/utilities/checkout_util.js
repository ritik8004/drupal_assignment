import axios from 'axios';
import _isEmpty from 'lodash/isEmpty';
import { removeCartFromStorage } from './storage';
import { updateCartApiUrl } from './update_cart';
import { cartAvailableInStorage, getGlobalCart } from './get_cart';

/**
 * Default error message on checkout screen.
 */
export const getDefaultCheckoutErrorMessage = () => {
  return Drupal.t('Sorry, something went wrong. Please try again later.');
}

/**
 * Get shipping methods.
 *
 * @param cart_id
 * @param data
 * @returns {boolean}
 */
export const getShippingMethods = function (cart_id, data) {
  const { middleware_url } = window.drupalSettings.alshaya_spc;

  return axios
    .post(`${middleware_url}/cart/shipping-methods`, {
      data,
      cart_id,
    })
    .then(
      (response) => response.data,
      (error) => {
        // Processing of error here.
      },
    );
};

/**
 * Place order.
 *
 * @param cart_id
 * @param payment_method
 * @returns {boolean}
 */
export const placeOrder = function (cart_id, payment_method) {
  const { middleware_url } = window.drupalSettings.alshaya_spc;

  const data = {
    paymentMethod: {
      method: payment_method,
    },
  };
  return axios
    .post(`${middleware_url}/cart/place-order`, {
      data,
      cart_id,
    })
    .then(
      (response) => {
        // Remove cart info from storage.
        removeCartFromStorage();

        window.location = Drupal.url('checkout/confirmation');
      },
      (error) => {
        // Processing of error here.
      },
    );
};

export const addShippingInCart = function (action, data) {
  let cart = cartAvailableInStorage();
  if (cart === false) {
    return null;
  }

  if (!Number.isInteger(cart)) {
    cart = cart.cart_id;
  }

  const api_url = updateCartApiUrl();
  return axios
    .post(api_url, {
      action,
      shipping_info: data,
      cart_id: cart,
    })
    .then(
      (response) => {
        if (typeof response.data !== 'object') {
          removeFullScreenLoader();
          return null;
        }
        return response.data;
      },
      (error) => {
        // Processing of error here.
        return {
          error: true,
          error_message: getDefaultCheckoutErrorMessage()
        }
      },
    )
    .catch((error) => {
      console.error(error);
    });
};

/**
 * Adds billing in the cart.
 *
 * @param {*} action
 * @param {*} data
 */
export const addBillingInCart = function (action, data) {
  let cart = cartAvailableInStorage();
  if (cart === false) {
    return null;
  }

  if (!Number.isInteger(cart)) {
    cart = cart.cart_id;
  }

  const api_url = updateCartApiUrl();
  return axios
    .post(api_url, {
      action,
      billing_info: data,
      cart_id: cart,
    })
    .then(
      (response) => {
        return response.data;
      },
      (error) => {
        // Processing of error here.
        return {
          error: true,
          error_message: getDefaultCheckoutErrorMessage()
        }
      },
    )
    .catch((error) => {
      console.error(error);
    });
};

/**
 * Place ajax fulll screen loader.
 */
export const showFullScreenLoader = () => {
  const loaderDiv = document.createElement('div');
  loaderDiv.className = 'ajax-progress ajax-progress-fullscreen';
  document.body.appendChild(loaderDiv);
};

/**
 * Remove ajax loader.
 */
export const removeFullScreenLoader = () => {
  const loaderDiv = document.getElementsByClassName('ajax-progress-fullscreen');
  if (loaderDiv.length > 0) {
    document.body.removeChild(loaderDiv[0]);
  }
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
  if (drupalSettings.map.center.length > 0 && ({}).hasOwnProperty.call(drupalSettings.map.center, 'latitude') && ({}).hasOwnProperty.call(drupalSettings.map.center, 'longitude')) {
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
 * Trigger an event.
 *
 * @param {*} eventName
 * @param {*} data
 */
export const triggerCheckoutEvent = (eventName, data) => {
  const ee = new CustomEvent(eventName, {
    bubbles: true,
    detail: {
      data: () => data,
    },
  });
  document.dispatchEvent(ee);
};
