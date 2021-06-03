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
 * Contains the mapping of exception messages to their corresponding type.
 */
const exceptionMessages = {
  'This product is out of stock.': 'OOS',
  'لقد نفدت كمية هذا المنتج.': 'OOS',
  'Some of the products are out of stock.': 'OOS',
  'Not all of your products are available in the requested quantity.': 'OOS',
  "We don't have as many": 'not_enough',
  'The requested qty is not available': 'not_enough',
  'هذا المنتج غير متوفر في المخزن.': 'OOS',
  'بعض المنتجات غير متوفرة بالمخزن.': 'OOS',
  'ليس لدينا العديد من': 'not_enough',
  'The maximum quantity per item has been exceeded': 'quantity_limit',
  'Fraud rule detected. Reauthorization is required': 'FRAUD',
};

/**
 * Provides the type of the exception message for a message.
 *
 * @param {string} msg
 *   The exception message.
 */
const getExceptionMessageType = (msg) => {
  let type = null;
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
