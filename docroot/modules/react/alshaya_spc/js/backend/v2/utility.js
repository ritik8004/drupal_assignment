/**
 * Logs messages in the backend.
 *
 * @todo This is a placeholder for logger.
 *
 * @param {string} level
 *   The error level [, error, warning, notice, info, debug].
 * @param {string} message
 *   The message.
 * @param {string} context
 *   The context.
 */
/* eslint-disable no-unused-vars */
const logger = {
  send: (level, message, context) => {

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
 * Contains cart error codes.
 */
const cartErrorCodes = {
  cartHasOOSItem: 506,
  cartOrderPlacementError: 505,
  cartCheckoutQuantityMismatch: 9010,
};

/**
 * Get default error message.
 *
 * @return string
 *   Default error message.
 */
const getDefaultErrorMessage = () => 'Sorry, something went wrong and we are unable to process your request right now. Please try again later.';

export {
  logger,
  cartErrorCodes,
  getDefaultErrorMessage,
};
