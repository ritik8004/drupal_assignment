import Axios from 'axios';

const PromotionsDynamicLabels = {
  apply: (cartData) => {
    // Clear values first.
    PromotionsDynamicLabels.updateQualifiedMessage('', '');
    PromotionsDynamicLabels.updateNextEligibleMessage('', '');

    let apiUrl = Drupal.url('promotions/dynamic-label-cart');
    apiUrl = `${apiUrl}?${Drupal.alshayaSpc.getCartDataAsUrlQueryString(cartData)}`;

    Axios.get(apiUrl).then((response) => {
      if (response.data.cart_labels !== undefined) {
        Object.values(response.data.cart_labels.qualified).forEach((message) => {
          PromotionsDynamicLabels.updateQualifiedMessage(
            message.type,
            message.label,
          );
        });

        if (response.data.cart_labels.next_eligible !== undefined
          && response.data.cart_labels.next_eligible.type !== undefined) {
          PromotionsDynamicLabels.updateNextEligibleMessage(
            response.data.cart_labels.next_eligible.type,
            response.data.cart_labels.next_eligible.label,
          );
        }
      }

      if (response.data.products_labels !== undefined) {
        Object.values(response.data.products_labels).forEach((data) => {
          PromotionsDynamicLabels.updateProductMessage(data.sku, data.labels);
        });
      }
    });
  },

  updateQualifiedMessage: (type, message) => {
    const element = document.getElementById('spc-cart-promotion-dynamic-message-qualified');

    if (message.length === 0) {
      element.className = '';
    } else {
      element.classList.add(`type-${type}`);
    }

    element.innerHTML = message;
  },

  updateNextEligibleMessage: (type, message) => {
    const element = document.getElementById('spc-cart-promotion-dynamic-message-next-eligible');
    if (message.length === 0) {
      element.className = '';
    } else {
      element.classList.add(`type-${type}`);
    }
    element.innerHTML = message;
  },

  updateProductMessage: (sku, data) => {
    const element = document.querySelector(`[data-sku="${sku}"] .spc-cart-promotion-dynamic-message-product`);
    element.innerHTML = '';

    Object.values(data).forEach((message) => {
      const anchor = document.createElement('a');
      anchor.setAttribute('href', message.link);
      anchor.innerHTML = message.label;
      element.appendChild(anchor);
    });
  },

};

export default PromotionsDynamicLabels;
