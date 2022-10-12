import { hasValue } from '../../../../js/utilities/conditionsUtility';

/**
 * Redirect to url if keyword matches as per
 * rules configured in algolia dashboard.
 */
export default async function getSingleProductByColorSku(skuCode) {
  const response = await global.rcsPhCommerceBackend.getData('single_product_by_color_sku', {
    sku: skuCode,
  });
  if (hasValue(response)) {
    return response;
  }
  // If graphQl API is returning Error.
  Drupal.alshayaLogger('error', 'Error while calling the graph ql to fetch product info for sku: @sku', {
    '@sku': skuCode,
  });
  return null;
}
