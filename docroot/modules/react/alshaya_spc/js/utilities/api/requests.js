import Axios from 'axios';
import { getGlobalCart } from '../get_cart';

export const fetchClicknCollectStores = (coords) => {
  const { cart_id } = getGlobalCart();
  if (!cart_id) {
    return new Promise((resolve) => resolve(null));
  }

  const GET_STORE_URL = Drupal.url(
    `cnc/stores/${cart_id}/${coords.lat}/${coords.lng}`,
  );
  return Axios.get(GET_STORE_URL);
};
