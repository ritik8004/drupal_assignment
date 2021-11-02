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
const getDefaultErrorMessage = () => Drupal.t('Sorry, something went wrong and we are unable to process your request right now. Please try again later.');

/**
 * Provides the type of the exception message for a message.
 *
 * @param {string} msg
 *   The exception message.
 */
const getExceptionMessageType = (msg) => {
  let type = null;
  const { exceptionMessages } = drupalSettings.cart;
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
  let msg = response.data.message;

  if (_isUndefined(response.data.parameters) || _isEmpty(response.data.parameters)) {
    return msg;
  }
  const params = response.data.parameters;
  const replacements = {};

  // If parameters is an array, we loop the array to create the replacements object.
  if (_isArray(params)) {
    for (let i = 0; i < params.length; i++) {
      replacements[`%${i + 1}`] = params[i];
    }
  } else {
    // If parameters is an object, we loop the object to add % to each key.
    Object.keys(params).forEach((key) => {
      replacements[`%${key}`] = !_isEmpty(params[key]) ? params[key] : '';
    });
  }

  // Replace placeholders.
  msg = Drupal.formatString(msg, replacements);

  // Strip html tags.
  msg = msg.replace(/(<([^>]+)>)/gi, '');

  return msg;
};

export {
  cartErrorCodes,
  getDefaultErrorMessage,
  getExceptionMessageType,
  getProcessedErrorMessage,
};
