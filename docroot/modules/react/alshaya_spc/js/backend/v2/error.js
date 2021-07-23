import _isArray from 'lodash/isArray';
import _isEmpty from 'lodash/isEmpty';
import _isUndefined from 'lodash/isUndefined';

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

/**
 * Wrapper function to process the response error message from Magento.
 *
 * @param {object} response
 *   Response data.
 *
 * @return {string}
 *   Processed message.
 */
const getProcessedErrorMessage = (response) => {
  let message = { ...response.data.message };

  if (_isUndefined(response.data.parameters) || _isEmpty(response.data.parameters)) {
    return message;
  }

  const parameters = { ...response.data.parameters };
  const replacements = {};

  // If parameters is an array, we loop the array to create the replacements object.
  if (_isArray(parameters)) {
    for (let i = 0; i < parameters.length; i++) {
      replacements[`%${i + 1}`] = parameters[i];
    }
  } else {
    // If parameters is an object, we loop the object to add % to each key.
    Object.keys(parameters).forEach((key) => {
      replacements[`%${key}`] = parameters[key];
    });
  }

  // Replace placeholders.
  message = Drupal.formatString(message, replacements);

  // Strip html tags.
  message = message.replace(/(<([^>]+)>)/gi, '');

  return message;
};

export {
  cartErrorCodes,
  getDefaultErrorMessage,
  getExceptionMessageType,
  getProcessedErrorMessage,
};
