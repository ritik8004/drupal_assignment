/**
 * @file
 * Alshaya RCS Promotions Dynamic Label Manager.
 */

window.dynamicPromotion = window.dynamicPromotion || {};

const PromotionsDynamicLabelsUtil = {
  apply: (cartData) => {
    if (Object.values(cartData.items).length === 0) {
      // Remove existing labels.
      RcsEventManager.fire('applyDynamicPromotions', {
        detail: {
          cart_labels: null,
          products_labels: null,
        }
      });

      // No API call required.
      return;
    }

    const productLabels = {};
    const response = Drupal.alshayaPromotions.getRcsDynamicLabel('', cartData, '', 'cart');
    if (response && response.products_labels) {
      response.products_labels.forEach((item) => {
        // @todo To remove this once we get the proper empty array response
        // from Magento.
        if (!item.labels) {
          item.labels = [];
        }
        productLabels[item.sku] = item;
      });
      // Update the response array with the modified one.
      response.products_labels = productLabels;
    }
    // Fire the event to update dynamic promotion.
    RcsEventManager.fire('applyDynamicPromotions', {
      detail: {
        response
      },
    });
  },
};

window.dynamicPromotion.apply = (cartData) => PromotionsDynamicLabelsUtil.apply(cartData);
