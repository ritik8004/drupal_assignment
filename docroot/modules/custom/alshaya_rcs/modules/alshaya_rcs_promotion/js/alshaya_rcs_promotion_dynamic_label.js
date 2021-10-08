/**
 * @file
 * Alshaya RCS Promotions Dynamic Label Manager.
 */

window.dynamicPromotion = window.dynamicPromotion || {};

const PromotionsDynamicLabelsUtil = {
  apply: (cartData) => {
    if (Object.values(cartData.items).length === 0) {
      // Remove existing labels.
      dispatchRcsCustomEvent('applyDynamicPromotions', {
        cart_labels: null,
        products_labels: null,
      });

      // No API call required.
      return;
    }

    const productLabels = {};
    Object.keys(cartData.items).forEach((key) => {
      const response = Drupal.alshayaPromotions.getRcsDynamicLabel(cartData.items[key].sku, cartData, 'api');
      if (response && typeof response.data !== 'undefined') {
        productLabels[key] = {
          sku: cartData.items[key].sku,
          labels: JSON.parse(response.data.dynamicPromotionLabel.label),
        };
      }
    });
    dispatchRcsCustomEvent('applyDynamicPromotions', {
      products_labels: productLabels,
      // @todo Change the cart_labels logic once the changes are done in
      // Magento.
      cart_labels: {
        qualified: [],
        next_eligible: [],
      },
    });
  },
};

window.dynamicPromotion.apply = (cartData) => PromotionsDynamicLabelsUtil.apply(cartData);
