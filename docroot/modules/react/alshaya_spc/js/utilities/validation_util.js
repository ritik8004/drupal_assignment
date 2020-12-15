import { removeCartFromStorage } from './storage';

/**
 * Clear local storage and reload/redirect to cart page.
 *
 * @param response
 *   API Response.
 */
const validateCartResponse = (response) => {
  if (typeof response.error_code === 'undefined') {
    return;
  }

  const errorCode = parseInt(response.error_code, 10);

  if (errorCode === 400 || errorCode === 404) {
    removeCartFromStorage();
    window.location.href = Drupal.url('cart');
  }
};

export default validateCartResponse;
