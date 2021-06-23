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
const isUserAuthenticated = () => {
  const { uid } = drupalSettings.user;
  return (uid !== 0);
};

/**
 * Gets magento api endpoint by user role.
 *
 * @param {string} action
 *   Callname for the API.
 * @param {string} cartId
 *   The Cart id.
 * @param {string} itemId
 *   The product item id.
 *
 * @returns {*}
 *   The api endpoint.
 */
const getApiEndpoint = (action, cartId = '', itemId = '') => {
  const type = isUserAuthenticated() ? 'authenticated' : 'anonymous';
  const apis = {
    createCart: {
      authenticated: '/rest/V1/carts/mine',
      anonymous: '/rest/V1/guest-carts',
    },
    getCart: {
      authenticated: '/rest/V1/carts/mine/getCart',
      anonymous: `/rest/V1/guest-carts/${cartId}/getCart`,
    },
    addUpdateItems: {
      authenticated: '/rest/V1/carts/mine/items',
      anonymous: `/rest/V1/guest-carts/${cartId}/items`,
    },
    removeItems: {
      authenticated: `/rest/V1/carts/mine/items/${itemId}`,
      anonymous: `/rest/V1/guest-carts/${cartId}/items/${itemId}`,
    },
  };
  return apis[action][type];
};

/* eslint-disable import/prefer-default-export */
export {
  logger,
  getApiEndpoint,
  isUserAuthenticated,
};
