import Axios from 'axios';
import dispatchCustomEvent from '../events';

const PromotionsDynamicLabelsUtil = {
  apply: (cartData) => {
    if (Object.values(cartData.items).length === 0) {
      // Remove existing labels.
      dispatchCustomEvent('applyDynamicPromotions', {
        cart_labels: null,
        products_labels: null,
      });

      // No API call required.
      return;
    }

    let apiUrl = Drupal.url('promotions/dynamic-label-cart');
    // We set cacheable=1 so it is always treated as anonymous user request.
    apiUrl = `${apiUrl}?cacheable=1&context=web&${Drupal.alshayaSpc.getCartDataAsUrlQueryString(cartData)}`;

    Axios.get(apiUrl).then((response) => {
      if (response.data.cart_labels !== undefined || response.data.products_labels !== undefined) {
        dispatchCustomEvent('applyDynamicPromotions', response.data);
      }
    });
  },

};

export default PromotionsDynamicLabelsUtil;
