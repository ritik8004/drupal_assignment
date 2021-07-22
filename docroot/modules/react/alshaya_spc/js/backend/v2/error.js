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

/**
 * Provides the type of the exception message for a message.
 *
 * @param {string} msg
 *   The exception message.
 */
const getExceptionMessageType = (msg) => {
  let type = null;
  const exceptionMessages = window.drupalSettings.cart.exception_messages;
  const messages = Object.keys(exceptionMessages);

  for (let i = 0; i < messages.length; i++) {
    if (messages[i].indexOf(msg) > -1) {
      type = exceptionMessages[messages[i]];
      break;
    }
  }

  return type;
};

export {
  cartErrorCodes,
  getDefaultErrorMessage,
  getExceptionMessageType,
};
