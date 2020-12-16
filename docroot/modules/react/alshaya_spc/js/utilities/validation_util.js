import { redirectToCart } from './get_cart';
import { removeCartFromStorage } from './storage';

/**
 * Clear local storage and reload/redirect to cart page.
 *
 * @param response
 *   API Response.
 */
export const validateCartResponse = (response) => {
  if (typeof response.error_code === 'undefined') {
    return;
  }

  const errorCode = parseInt(response.error_code, 10);

  if (errorCode === 400 || errorCode === 404) {
    removeCartFromStorage();
    window.location.href = Drupal.url('cart');
  }
};

/**
 * Redirect to cart page on invalid response code.
 *
 * @param response
 *   API Response.
 */
export const validateMiddlewareResponse = (response) => {
  if (typeof response.error_code === 'undefined') {
    return;
  }

  const errorCode = parseInt(response.error_code, 10);

  if (errorCode === 9010) {
    redirectToCart();
  }
};
