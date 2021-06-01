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
  cartErrorCodes,
  getDefaultErrorMessage,
};
