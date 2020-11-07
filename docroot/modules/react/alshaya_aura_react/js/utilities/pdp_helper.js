import { postAPIData } from './api/fetchApiData';
import dispatchCustomEvent from '../../../js/utilities/events';

/**
 * Helper function to get product points.
 */
function getProductPoints(productDetails, cardNumber) {
  let stateValues = {};
  const { currency_code: currencyCode } = drupalSettings.alshaya_spc.currency_config;
  const data = {
    cardNumber,
    currencyCode,
    products: productDetails,
  };

  const apiUrl = 'post/loyalty-club/get-product-points';
  const apiData = postAPIData(apiUrl, data);

  if (apiData instanceof Promise) {
    apiData.then((result) => {
      if (result.data !== undefined && result.data.error === undefined) {
        if (result.data.status) {
          stateValues = {
            productPoints: result.data.apcPoints || 0,
          };
        }
      }
      stateValues.wait = false;
      dispatchCustomEvent('productPointsFetched', { stateValues });
    });
  }
}

/**
 * Helper function to check if product is buyable or not.
 */
function isProductBuyable() {
  // @TODO: Check if product is buyable/ add to cart is enabled.
  return true;
}

export {
  getProductPoints,
  isProductBuyable,
};
