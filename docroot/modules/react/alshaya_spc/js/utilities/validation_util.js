import { redirectToCart } from './get_cart';
import { removeCartFromStorage } from './storage';

/**
 * Clear local storage and reload/redirect to cart page.
 *
 * @param response
 *   API Response.
 */
const validateCartResponse = (response) => {
  if (typeof response.error_code === 'undefined') {
    return true;
  }

  const errorCode = parseInt(response.error_code, 10);

  if (errorCode === 400 || errorCode === 404) {
    removeCartFromStorage();
    window.location.href = Drupal.url('cart');
    return false;
  }
  if (errorCode === 9010) {
    const isNotCartPage = redirectToCart();
    // This will happen in case of stock mismatch scenario between Magento and
    // OMS. In that case we redirect to cart page and show the error message
    // recived in the response.
    if (isNotCartPage && (typeof response.error_message !== 'undefined')) {
      localStorage.setItem('stockErrorResponseMessage', response.error_message);
    }
    return false;
  }
  return true;
};

export default validateCartResponse;
