import { postAPIData } from './api/fetchApiData';
import dispatchCustomEvent from '../../../js/utilities/events';

/**
 * Helper function to get PDP layout.
 */
function getPDPLayout() {
  let layout = 'pdp';

  const bodyElement = document.querySelector('body');

  if (bodyElement.classList.contains('magazine-layout')) {
    layout = 'pdp-magazine'
  } else if (bodyElement.classList.contains('new-pdp-magazine-layout')) {
    layout = 'pdp-magazine_v2'
  }

  return layout;
}

/**
 * Helper function to get product price from drupalSettings.
 */
function getProductPrice(productKey, parentSku, variantSku) {
  let price = '';

  if (typeof drupalSettings[productKey] !== 'undefined'
    && typeof drupalSettings[productKey][parentSku] !== 'undefined'
    && typeof drupalSettings[productKey][parentSku].variants !== 'undefined'
    && ({}).hasOwnProperty.call(drupalSettings[productKey][parentSku].variants, variantSku)) {
    price = drupalSettings[productKey][parentSku].variants[variantSku].priceRaw || '';
  }

  return price;
}

/**
 * Helper function to get selected product details.
 */
function getSelectedProductDetails() {
  const productDetails = [];
  const layout = getPDPLayout();

  if (layout === 'pdp' || layout === 'pdp-magazine') {
    const selectedVariant = document.querySelector('.sku-base-form input.selected-variant-sku');
    const article = selectedVariant ? selectedVariant.closest('article.entity--type-node') : '';

    if (!article) {
      return [];
    }

    const viewMode = article.dataset.vmode || '';
    const productKey = (viewMode === 'matchback') ? 'matchback' : 'productInfo';
    const variantSku = selectedVariant.value;
    const parentSku = article.getAttribute('data-sku') || '';
    const price = getProductPrice(productKey, parentSku, variantSku);
    const quantity = article.querySelector('.sku-base-form .edit-quantity')
      ? article.querySelector('.sku-base-form .edit-quantity').value
      : 1;

    productDetails.push({
      code: variantSku,
      quantity,
      amount: price * quantity,
    });

    return productDetails;
  }

  if (layout === 'pdp-magazine_v2') {
    const skuBaseForm = document.querySelector('.sku-base-form');
    const pdpLayout = skuBaseForm ? skuBaseForm.closest('#pdp-layout') : '';

    if (!pdpLayout) {
      return [];
    }

    const viewMode = pdpLayout.dataset.vmode || '';
    const productKey = (viewMode === 'matchback') ? 'matchback' : 'productInfo';
    const variantSku = skuBaseForm.getAttribute('variantselected');
    const parentSku = pdpLayout.dataset.sku || '';
    const price = getProductPrice(productKey, parentSku, variantSku);

    const quantity = pdpLayout.querySelector('.magv2-qty-input')
      ? pdpLayout.querySelector('.magv2-qty-input').value
      : 1;

    productDetails.push({
      code: variantSku,
      quantity,
      amount: price * quantity,
    });

    return productDetails;
  }

  return productDetails;
}

/**
 * Helper function to get product points.
 */
function getProductPoints(cardNumber) {
  let stateValues = {};

  const productDetails = getSelectedProductDetails();
  if (productDetails.length === 0) {
    stateValues.wait = false;
    dispatchCustomEvent('productPointsFetched', { stateValues });
    return;
  }
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
