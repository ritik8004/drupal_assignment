import Axios from 'axios';
import dispatchCustomEvent from '../../../../../js/utilities/events';
import BVStaticStorage from '../bvStaticStorage';
import hasValue from '../../../../../js/utilities/conditionsUtility';

function getBvUrl(bazaarVoiceSettings) {
  return bazaarVoiceSettings.reviews.bazaar_voice.endpoint;
}

function getApiVersion(bazaarVoiceSettings) {
  return `apiversion=${bazaarVoiceSettings.reviews.bazaar_voice.api_version}`;
}

function getPassKey(bazaarVoiceSettings) {
  return `&passkey=${bazaarVoiceSettings.reviews.bazaar_voice.passkey}`;
}

function getLocale(bazaarVoiceSettings) {
  return `&locale=${bazaarVoiceSettings.reviews.bazaar_voice.locale}`;
}

export function getbazaarVoiceSettings(productId = undefined) {
  const settings = [];
  let productInfo = window.commerceBackend.getProductData(productId);

  if (typeof productId !== 'undefined' && productInfo !== null) {
    settings.productid = productId;
    settings.reviews = productInfo.alshaya_bazaar_voice;
  } else {
    productInfo = window.commerceBackend.getProductData(null, 'productInfo');
    Object.entries(productInfo).forEach(([key]) => {
      settings.productid = key;
      settings.reviews = productInfo[key].alshaya_bazaar_voice;
    });
  }
  return settings;
}

export function getUserBazaarVoiceSettings() {
  const settings = [];
  if (drupalSettings.userInfo) {
    settings.reviews = drupalSettings.userInfo;
  }
  return settings;
}

export function fetchAPIData(apiUri, params, context = '') {
  const bazaarVoiceSettings = context === 'user' ? getUserBazaarVoiceSettings() : getbazaarVoiceSettings();
  const url = `${getBvUrl(bazaarVoiceSettings) + apiUri}?${getApiVersion(bazaarVoiceSettings)}${getPassKey(bazaarVoiceSettings)}${getLocale(bazaarVoiceSettings)}${params}`;

  return Axios.get(url)
    .then((response) => {
      dispatchCustomEvent('showMessage', { data: response });
      return response;
    })
    .catch((error) => {
      dispatchCustomEvent('showMessage', { data: error });
      return error;
    });
}

export function postAPIData(apiUri, params, productId = undefined) {
  const bazaarVoiceSettings = getbazaarVoiceSettings(productId);
  const url = `${getBvUrl(bazaarVoiceSettings) + apiUri}?${getApiVersion(bazaarVoiceSettings)}${getPassKey(bazaarVoiceSettings)}${getLocale(bazaarVoiceSettings)}`;

  return Axios.post(url, params, {
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
  })
    .then((response) => {
      dispatchCustomEvent('showMessage', { data: response });
      return response;
    })
    .catch((error) => {
      dispatchCustomEvent('showMessage', { data: error });
      return error;
    });
}

export function postAPIPhoto(apiUri, params) {
  const bazaarVoiceSettings = getbazaarVoiceSettings();
  const url = `${getBvUrl(bazaarVoiceSettings) + apiUri}?${getApiVersion(bazaarVoiceSettings)}${getPassKey(bazaarVoiceSettings)}${getLocale(bazaarVoiceSettings)}${params}`;

  return Axios.post(url)
    .then((response) => {
      dispatchCustomEvent('showMessage', { data: response });
      return response;
    })
    .catch((error) => {
      dispatchCustomEvent('showMessage', { data: error });
      return error;
    });
}

export function getLanguageCode() {
  return drupalSettings.path.currentLanguage;
}

/**
 * Returns a review for the user for the current/mentioned product.
 *
 * (optional) @param {string} productIdentifier
 *   The sku value for the product.
 *
 * @returns {Object}
 *   The product review data.
 */
export async function getProductReviewForCurrrentUser(productIdentifier) {
  const bazaarVoiceSettings = getbazaarVoiceSettings();
  const productId = typeof productIdentifier !== 'undefined' ? productIdentifier : bazaarVoiceSettings.productid;
  const userId = drupalSettings.user.uid;
  const staticStorageKey = `${userId}_${productId}`;
  let productReviewData = BVStaticStorage.get(staticStorageKey);

  if (productReviewData) {
    return JSON.parse(productReviewData);
  }
  if (productReviewData === 0) {
    return null;
  }

  // Get review data from BazaarVoice based on available parameters.
  const apiUri = '/data/reviews.json';
  const params = `&include=Authors,Products&filter=AuthorId:${userId}&filter=productid:${productId}&stats=${bazaarVoiceSettings.reviews.bazaar_voice.stats}`;
  const result = await fetchAPIData(apiUri, params);

  if (!hasValue(result.error) && hasValue(result.data)) {
    if (result.data.Results.length > 0) {
      const products = result.data.Includes.Products;
      Object.keys(products).forEach((sku) => {
        if (sku === productId) {
          productReviewData = {
            review_data: products[sku],
            user_rating: products[sku].Rating,
          };
        }
      });
    }
  }

  // In case there are no reviews, store 0 instead of null in order to
  // differentiate between empty storage and 0 reviews.
  const staticData = !productReviewData ? 0 : JSON.stringify(productReviewData);
  BVStaticStorage.set(staticStorageKey, staticData);

  return productReviewData;
}

export async function getUserDetails(productId = undefined) {
  const settings = {};

  if (typeof drupalSettings.bazaarvoiceUserDetails !== 'undefined') {
    settings.user = drupalSettings.bazaarvoiceUserDetails;
    settings.productReview = await getProductReviewForCurrrentUser(productId);
  }

  return settings;
}

export function doRequest(url) {
  return Axios.get(url)
    .then((response) => {
      dispatchCustomEvent('showMessage', { data: response });
      return response;
    })
    .catch((error) => {
      dispatchCustomEvent('showMessage', { data: error });
      return error;
    });
}

export function postRequest(url, data) {
  return Axios.post(url, data)
    .then((response) => {
      dispatchCustomEvent('showMessage', { data: response });
      return response;
    })
    .catch((error) => {
      dispatchCustomEvent('showMessage', { data: error });
      return error;
    });
}

export default {
  getLanguageCode,
  doRequest,
  postRequest,
  fetchAPIData,
  postAPIData,
  postAPIPhoto,
};
