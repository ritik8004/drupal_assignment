import axios from 'axios';
import { i18nMiddleWareUrl } from './i18n_url';

/**
 * Get the middleware get order endpoint.
 *
 * @returns {string}
 */
export const getOrderApiUrl = (id) => i18nMiddleWareUrl(`order/${id}`);

export const redirectToCart = () => {
  window.location = Drupal.url('cart');
};

export const fetchOrderData = (id) => {
  // Prepare api url.
  const apiUrl = getOrderApiUrl(id);

  return axios.get(apiUrl)
    .then((response) => response.data)
    .catch(() => {
      redirectToCart();
    });
};
