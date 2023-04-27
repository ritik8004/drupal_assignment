import { hasValue, isArray } from './conditionsUtility';

/**
 * Contains cart error codes.
 */
const cartErrorCodes = {
  cartHasOOSItem: 506,
  cartOrderPlacementError: 505,
  cartCheckoutQuantityMismatch: 9010,
  cartHasUserError: 610,
};

/**
 * Contains CLM Error codes.
 */
const clmErrorCode = 503;

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

  if (!hasValue(response.data.parameters)) {
    return msg;
  }
  const params = response.data.parameters;
  const replacements = {};

  // If parameters is an array, we loop the array to create the replacements object.
  if (isArray(params)) {
    for (let i = 0; i < params.length; i++) {
      replacements[`%${i + 1}`] = params[i];
    }
  } else {
    // If parameters is an object, we loop the object to add % to each key.
    Object.keys(params).forEach((key) => {
      replacements[`%${key}`] = hasValue(params[key]) ? params[key] : '';
    });
  }

  // Replace placeholders.
  msg = Drupal.formatString(msg, replacements);

  // Strip html tags.
  msg = msg.replace(/(<([^>]+)>)/gi, '');

  return msg;
};

/**
 * Returns the error in a specific format.
 *
 * @param {string} message
 *   The processed error message.
 * @param {string} code
 *   The error code.
 * @param {Boolean} custom
 *   Indicates whether the message is a custom message or a backend message.
 *
 * @returns {Object}
 *   The object containing the error data.
 */
const getErrorResponse = (message, code = '-', custom = false) => ({
  error: true,
  error_message: message,
  error_code: code,
  custom,
});

export {
  getErrorResponse,
  cartErrorCodes,
  clmErrorCode,
  getDefaultErrorMessage,
  getExceptionMessageType,
  getProcessedErrorMessage,
};
