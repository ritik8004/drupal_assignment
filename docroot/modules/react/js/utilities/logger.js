import { getStorageItem } from '../../alshaya_spc/js/utilities/storage';

/**
 * Logs messages in the backend.
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

    // eslint-disable-next-line no-console
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
 * Provides extra DataDog contexts.
 */
document.addEventListener('dataDogContextAlter', (e) => {
  const context = e.detail;
  // These variables should be considered as helpers for troubleshooting but
  // may in some cases not be accurate.
  const uid = drupalSettings.userDetails.customerId;
  if (uid) {
    context.cCustomerId = uid;
  }

  const cartId = window.commerceBackend.getCartId();
  if (cartId) {
    context.cCartId = cartId;
    const cartIdInt = getStorageItem('cart_data', 'cart.cart_id_int');
    if (cartIdInt) {
      context.cCartIdInt = cartIdInt;
    }
  }
});

export default logger;
