import Axios from 'axios';

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
    console.log(level, Drupal.formatString(message, context));
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
    case 'createCart':
      endpoint = isUserAuthenticated()
        ? '/rest/V1/carts/mine'
        : '/rest/V1/guest-carts';
      break;

    case 'getCart':
      endpoint = isUserAuthenticated()
        ? '/rest/V1/carts/mine/getCart'
        : `/rest/V1/guest-carts/${params.cartId}/getCart`;
      break;

    case 'addUpdateItems':
      endpoint = isUserAuthenticated()
        ? '/rest/V1/carts/mine/items'
        : `/rest/V1/guest-carts/${params.cartId}/items`;
      break;

    case 'removeItems':
      endpoint = isUserAuthenticated()
        ? `/rest/V1/carts/mine/items/${params.itemId}`
        : `/rest/V1/guest-carts/${params.cartId}/items/${params.itemId}`;
      break;

    case 'updateCart':
      endpoint = isUserAuthenticated()
        ? '/rest/V1/carts/mine/updateCart'
        : `/rest/V1/guest-carts/${params.cartId}/updateCart`;
      break;

    case 'estimateShippingMethods':
      endpoint = isUserAuthenticated()
        ? '/rest/V1/carts/mine/estimate-shipping-methods'
        : `/rest/V1/guest-carts/${params.cartId}/estimate-shipping-methods`;
      break;

    case 'getPaymentMethods':
      endpoint = isUserAuthenticated()
        ? '/rest/V1/carts/mine/payment-methods'
        : `/rest/V1/guest-carts/${params.cartId}/payment-methods`;
      break;

    case 'selectedPaymentMethod':
      endpoint = isUserAuthenticated()
        ? '/rest/V1/carts/mine/selected-payment-method'
        : `/rest/V1/guest-carts/${params.cartId}/selected-payment-method`;
      break;

    case 'placeOrder':
      endpoint = isUserAuthenticated()
        ? '/rest/V1/carts/mine/order'
        : `/rest/V1/guest-carts/${params.cartId}/order`;
      break;

    case 'getCartStores':
      endpoint = isUserAuthenticated()
        ? `/rest/V1/click-and-collect/stores/cart/mine/lat/${params.lat}/lon/${params.lon}`
        : `/rest/V1/click-and-collect/stores/guest-cart/${params.cartId}/lat/${params.lat}/lon/${params.lon}`;
      break;

    default:
      logger.critical(`Endpoint does not exist for action : ${action}`);
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
};
