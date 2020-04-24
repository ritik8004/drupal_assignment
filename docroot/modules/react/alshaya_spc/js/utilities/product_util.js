import axios from 'axios';

/**
 * Get the product detail from PDP api.
 *
 * @param {*} sku
 */
const getProductDetail = (sku) => axios.get(`rest/v1/product/${sku}?context=cart`)
  .then((response) => response.data)
  .catch((error) => {
    // Processing of error here.
    Drupal.logJavascriptError('product-detail', error);
  });

export default getProductDetail;
