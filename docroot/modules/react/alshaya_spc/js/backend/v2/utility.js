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
/* eslint-disable no-unused-vars */
const logger = {
  send: (level, message, context) => {
    // console.log('Error ' + message);
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
/* eslint-enable no-unused-vars */

/**
 * Get user role authenticated or anonymous.
 *
 * @returns {boolean}
 *   True if user is authenticated.
 */
const isUserAuthenticated = () => Boolean(drupalSettings.user.uid);

/**
 * Gets magento api endpoint by user role.
 *
 * @param {string} action
 *   Callname for the API.
 * @param {object} params
 *   The object with cartId, itemId.
 *
 * @returns {*}
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
        ? 'rest/V1/carts/mine/updateCart'
        : `/rest/V1/guest-carts/${params.cartId}/updateCart`;
      break;

    // no default
  }

  return endpoint;
};

/* eslint-disable import/prefer-default-export */
export {
  logger,
  getApiEndpoint,
  isUserAuthenticated,
};
