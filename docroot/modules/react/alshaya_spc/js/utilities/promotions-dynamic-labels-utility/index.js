import Axios from 'axios';
import dispatchCustomEvent from '../events';

const PromotionsDynamicLabelsUtil = {
  apply: (cartData) => {
    let apiUrl = Drupal.url('promotions/dynamic-label-cart');
    apiUrl = `${apiUrl}?${Drupal.alshayaSpc.getCartDataAsUrlQueryString(cartData)}`;

    Axios.get(apiUrl).then((response) => {
      if (response.data.cart_labels !== undefined || response.data.products_labels !== undefined) {
        dispatchCustomEvent('applyDynamicPromotions', response.data);
      }
    });
  },

};

export default PromotionsDynamicLabelsUtil;
