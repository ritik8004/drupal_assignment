import { redirectToCart } from './get_cart';
import dispatchCustomEvent from './events';
import { hasValue } from '../../../js/utilities/conditionsUtility';

/**
 * Clear local storage and reload/redirect to cart page.
 *
 * @param response
 *   API Response.
 */
const validateCartResponse = (response) => {
  if (!hasValue(response)) {
    redirectToCart();
    return false;
  }

  // For some OOS cases we get error in response_message.
  // For most of the OOS cases we expect error code 506,
  // which is checked later.
  if (hasValue(response.response_message)
    && response.response_message.status === 'json_error'
    && (response.response_message.msg === 'OOS' || response.response_message.msg === 'not_enough')
  ) {
    redirectToCart();
    return false;
  }

  // Return if no error.
  if (!hasValue(response.error_code)) {
    return true;
  }

  // Get error code as integer for faster testing.
  const errorCode = parseInt(response.error_code, 10);

  // If there was validation issue or cart no longer available.
  if (errorCode === 400 || errorCode === 404) {
    window.commerceBackend.removeCartDataFromStorage();
    window.location.href = Drupal.url('cart');
    return false;
  }

  if (errorCode === 9010) {
    // This will happen in case of stock mismatch scenario between Magento and
    // OMS. In that case we redirect to cart page and show the error message
    // recived in the response.
    if (typeof response.error_message !== 'undefined') {
      // @todo: we may add an expiration time.
      Drupal.addItemInLocalStorage('stockErrorResponseMessage', response.error_message);
    }
    redirectToCart();
    return false;
  }

  // If back-end system is down or having errors.
  if (errorCode >= 500) {
    // For OOS error, we redirect to cart page.
    if (errorCode === 506) {
      // Push OOS errors to GTM.
      if (drupalSettings.path.currentPath === 'checkout') {
        Drupal.logJavascriptError('update-cart-item-data', response.error_message, GTM_CONSTANTS.CHECKOUT_ERRORS);
      } else if (drupalSettings.path.currentPath === 'cart') {
        Drupal.logJavascriptError('update-cart-item-data', response.error_message, GTM_CONSTANTS.CART_ERRORS);
      }
      redirectToCart();
      return false;
    }

    if (window.location.pathname.search(/checkout/i) >= 0) {
      let errorMessage = drupalSettings.globalErrorMessage;
      // This happens when cart is locked.
      if (errorCode === 610 && typeof response.error_message !== 'undefined') {
        errorMessage = response.error_message;
      }
      dispatchCustomEvent('spcCheckoutMessageUpdate', {
        type: 'error',
        message: errorMessage,
      });

      return false;
    }
  }

  return true;
};

export default validateCartResponse;
