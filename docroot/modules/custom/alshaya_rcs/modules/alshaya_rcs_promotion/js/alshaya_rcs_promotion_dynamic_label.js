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
    const { cart_labels, products_labels } = response;

    if (products_labels) {
      products_labels.forEach((item) => {
        // Settings labels as an empty array if the response is null from
        // Magento, as we treat this as an array to perform computation.
        if (!item.labels) {
          item.labels = [];
        }
        productLabels[item.sku] = item;
      });
      // Update the threshold_reached to array if null as we are checking
      // length.
      if (!cart_labels.next_eligible.threshold_reached) {
        response.cart_labels.next_eligible.threshold_reached = [];
      }
      // Update the response array with the modified one.
      response.products_labels = productLabels;
    }
    // Fire the event to update dynamic promotion.
    RcsEventManager.fire('applyDynamicPromotions', {
      detail: {
        cart_labels: response.cart_labels,
        products_labels: response.products_labels,
      },
    });
  },
};

window.dynamicPromotion.apply = (cartData) => PromotionsDynamicLabelsUtil.apply(cartData);
