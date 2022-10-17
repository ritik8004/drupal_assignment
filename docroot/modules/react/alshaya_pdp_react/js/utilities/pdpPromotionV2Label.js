import { hasValue } from '../../../js/utilities/conditionsUtility';

const staticDataStore = {
  pdpPromotion: [],
};

/**
 * Get product promotions from graphQL.
 */
export default async function getPdpPromotionV2Labels(skuMainCode) {
  const staticStorageKey = `pdpPromotion_${skuMainCode}`;

  const promotionVal = hasValue(staticDataStore.pdpPromotion[staticStorageKey])
    ? staticDataStore.pdpPromotion[staticStorageKey]
    : [];

  if (promotionVal.length > 0) {
    return promotionVal;
  }

  const response = await global.rcsPhCommerceBackend.getData('single_product_by_sku', {
    sku: skuMainCode,
  });

  if (hasValue(response.data)) {
    if (hasValue(response.data.products.items[0].promotions)) {
      const promotionData = response.data.products.items[0].promotions;
      promotionData.forEach((promotion, index) => {
        promotionVal[index] = {
          promo_web_url: promotion.url,
          text: promotion.label,
          context: promotion.context,
          type: promotion.type,
        };
      });
    }
    staticDataStore.pdpPromotion[staticStorageKey] = promotionVal;

    return promotionVal;
  }
  // If graphQL API is returning Error.
  Drupal.alshayaLogger('error', 'Error while calling the graphQL to fetch product info for sku: @sku', {
    '@sku': skuMainCode,
  });
  return null;
}
