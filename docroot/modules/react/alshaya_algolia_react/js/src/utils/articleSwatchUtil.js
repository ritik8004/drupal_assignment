import { hasValue } from '../../../../js/utilities/conditionsUtility';

/**
 * Redirect to url if keyword matches as per
 * rules configured in algolia dashboard.
 */
export default async function getSingleProductByColorSku(skuCode) {
  const sliderArticleSwatches = drupalSettings.reactTeaserView.swatches.showColorSwatchSlider;
  let response = null;
  // Call the graphql api for the sku to get the product info data
  // when Color swatches Slider is enabled.
  if (hasValue(sliderArticleSwatches)) {
    const colorSwatchQuery = alshayaGraphqlQuery.color_swatches.query;
    const data = {
      sku: skuCode,
      type: alshayaGraphqlQuery.color_swatches.variables.type,
    };
    // Call the Graphql Api when showColorSwatchSlider is TRUE.
    response = await global.graphqlQueryHelper.invokeGraphqlApi(colorSwatchQuery, 'GET', data);
    response = response.data.products.items;
    // Call the graphql api for the sku to get the product info data
    // when Article Swatches is enabled.
  } else {
    // Call the Graphql Api when showArticleSwatches is TRUE.
    response = await global.rcsPhCommerceBackend.getData('single_product_by_color_sku', {
      sku: skuCode,
    });
  }
  if (hasValue(response)) {
    return response;
  }
  // If graphQl API is returning Error.
  Drupal.alshayaLogger('error', 'Error while calling the GraphQL to fetch product info for sku: @sku', {
    '@sku': skuCode,
  });
  return null;
}
