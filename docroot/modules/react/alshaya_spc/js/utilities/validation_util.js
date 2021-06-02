import { redirectToCart } from './get_cart';
import dispatchCustomEvent from './events';

/**
 * Clear local storage and reload/redirect to cart page.
 *
 * @param response
 *   API Response.
 */
const validateCartResponse = (response) => {
  if ((typeof response.response_message !== 'undefined'
    && response.response_message !== null
    && response.response_message.status === 'json_error'
    && ((response.response_message.msg === 'OOS') || response.response_message.msg === 'not_enough'))
  ) {
    redirectToCart();
    return false;
  }

  if (typeof response.error_code === 'undefined') {
    return true;
  }

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
      localStorage.setItem('stockErrorResponseMessage', response.error_message);
    }
    redirectToCart();
    return false;
  }

  // If back-end system is down or having errors.
  if (errorCode >= 500) {
    // For OOS error, we redirect to cart page.
    if (errorCode === 506) {
      redirectToCart();
      return false;
    }

    if (window.location.pathname.search(/checkout/i) >= 0) {
      dispatchCustomEvent('spcCheckoutMessageUpdate', {
        type: 'error',
        message: drupalSettings.global_error_message,
      });

      return false;
    }
  }

  return true;
};

export default validateCartResponse;
