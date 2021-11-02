import { getStorageItem } from './storage';

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
  // These contexts will only be available when we have access to the data.
  // For example, on checkout page, we don't have access to cart_data in local
  // storage, so we won't have a context for cartIdInt.
  const customerId = drupalSettings.userDetails.customerId || 0;
  const cartId = window.commerceBackend.getCartId() || null;
  const cartIdInt = getStorageItem('cart_data', 'cart.cart_id_int') || null;
  const context = e.detail;
  // We are prepending the letter 'c' on custom contexts to differentiate them
  // from existing contexts.
  if (customerId) {
    context.cCustomerId = customerId;
  }
  if (cartId) {
    context.cCartId = cartId;
  }
  if (cartIdInt) {
    context.cCartIdInt = cartIdInt;
  }
});

export default logger;
