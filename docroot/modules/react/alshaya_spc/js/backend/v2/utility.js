import Axios from 'axios';
import {
  getStorageInfo,
  removeStorageInfo,
  setStorageInfo,
} from '../../utilities/storage';

/**
 * Logs messages in the backend.
 *
 * @todo This is a placeholder for logger.
 *
 * @param {string} level
 *   The error level.
 * @param {string} message
 *   The message.
 * @param {string} context
 *   The context.
 */
const logger = {
  send: (level, message, context) => {
    if (typeof Drupal.logViaDataDog !== 'undefined') {
      Drupal.logViaDataDog(level, message, context);
      return;
    }

    // Avoid console log in npm tests.
    if (typeof drupalSettings.jest !== 'undefined') {
      return;
    }

    console.debug(level, Drupal.formatString(message, context));
  },
  emergency: (message, context) => logger.send('emergency', message, context),
  alert: (message, context) => logger.send('alert', message, context),
  critical: (message, context) => logger.send('critical', message, context),
  error: (message, context) => logger.send('error', message, context),
  warning: (message, context) => logger.send('warning', message, context),
  notice: (message, context) => logger.send('notice', message, context),
  info: (message, context) => logger.send('info', message, context),
  debug: (message, context) => logger.send('debug', message, context),
};

/**
 * Get user role authenticated or anonymous.
 *
 * @returns {boolean}
 *   True if user is authenticated.
 */
const isUserAuthenticated = () => Boolean(window.drupalSettings.userDetails.customerId);

const removeCartIdFromStorage = () => {
  // Always remove cart_data, we have added this as workaround with
  // to-do at right place.
  removeStorageInfo('cart_data');

  if (isUserAuthenticated()) {
    setStorageInfo(window.authenticatedUserCartId, 'cart_id');
    return;
  }

  removeStorageInfo('cart_id');
};

const getCartIdFromStorage = () => {
  let cartId = getStorageInfo('cart_id');

  // Check if cartId is of authenticated user.
  if (cartId === window.authenticatedUserCartId) {
    // Reload the page if user is not authenticated based on settings.
    if (!isUserAuthenticated()) {
      removeCartIdFromStorage();

      // eslint-disable-next-line no-self-assign
      window.location.href = window.location.href;
    }

    // Replace with null so we don't need to add conditions everywhere.
    cartId = null;
  }

  return cartId;
};

/**
 * Gets magento api endpoint by user role.
 *
 * @param {string} action
 *   Callname for the API.
 * @param {object} params
 *   The object with cartId, itemId.
 *
 * @returns {string}
 *   The api endpoint.
 */
const getApiEndpoint = (action, params = {}) => {
  let endpoint = '';
  switch (action) {
    case 'associateCart':
      endpoint = isUserAuthenticated()
        ? '/V1/carts/mine/associate-cart'
        : '';
      break;

    case 'createCart':
      endpoint = isUserAuthenticated()
        ? '/V1/carts/mine'
        : '/V1/guest-carts';
      break;

    case 'getCart':
      endpoint = isUserAuthenticated()
        ? '/V1/carts/mine/getCart'
        : `/V1/guest-carts/${params.cartId}/getCart`;
      break;

    case 'addUpdateItems':
      endpoint = isUserAuthenticated()
        ? '/V1/carts/mine/items'
        : `/V1/guest-carts/${params.cartId}/items`;
      break;

    case 'removeItems':
      endpoint = isUserAuthenticated()
        ? `/V1/carts/mine/items/${params.itemId}`
        : `/V1/guest-carts/${params.cartId}/items/${params.itemId}`;
      break;

    case 'updateCart':
      endpoint = isUserAuthenticated()
        ? '/V1/carts/mine/updateCart'
        : `/V1/guest-carts/${params.cartId}/updateCart`;
      break;

    case 'estimateShippingMethods':
      endpoint = isUserAuthenticated()
        ? '/V1/carts/mine/estimate-shipping-methods'
        : `/V1/guest-carts/${params.cartId}/estimate-shipping-methods`;
      break;

    case 'getPaymentMethods':
      endpoint = isUserAuthenticated()
        ? '/V1/carts/mine/payment-methods'
        : `/V1/guest-carts/${params.cartId}/payment-methods`;
      break;

    case 'selectedPaymentMethod':
      endpoint = isUserAuthenticated()
        ? '/V1/carts/mine/selected-payment-method'
        : `/V1/guest-carts/${params.cartId}/selected-payment-method`;
      break;

    case 'placeOrder':
      endpoint = isUserAuthenticated()
        ? '/V1/carts/mine/order'
        : `/V1/guest-carts/${params.cartId}/order`;
      break;

    case 'getCartStores':
      endpoint = isUserAuthenticated()
        ? `/V1/click-and-collect/stores/cart/mine/lat/${params.lat}/lon/${params.lon}`
        : `/V1/click-and-collect/stores/guest-cart/${params.cartId}/lat/${params.lat}/lon/${params.lon}`;
      break;

    case 'getLastOrder':
      endpoint = isUserAuthenticated()
        ? '/V1/customer-order/me/getLastOrder'
        : '';
      break;

    default:
      logger.critical('Endpoint does not exist for action: @action.', {
        '@action': action,
      });
  }

  return endpoint;
};

/**
 * Gets the ip address of the client.
 *
 * @returns {string}
 *   Thge ip address.
 */
const getIp = () => Axios({ url: 'https://www.cloudflare.com/cdn-cgi/trace' })
  .then((response) => {
    if (typeof response.data === 'undefined' || response.data === '') {
      return '';
    }
    return response.data.trim().split('\n').map((e) => {
      const item = e.split('=');
      return (item[0] === 'ip') ? item[1] : null;
    }).filter((value) => value != null)[0];
  });

/* eslint-disable import/prefer-default-export */
export {
  logger,
  getApiEndpoint,
  isUserAuthenticated,
  getIp,
  getCartIdFromStorage,
  removeCartIdFromStorage,
};
