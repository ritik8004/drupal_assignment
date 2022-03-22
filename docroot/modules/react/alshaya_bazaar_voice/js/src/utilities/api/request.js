import Axios from 'axios';
import dispatchCustomEvent from '../../../../../js/utilities/events';

window.alshayaBazaarVoice = window.alshayaBazaarVoice || {};

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

function getbazaarVoiceSettings(productId = undefined) {
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

// @todo Find a better way to do this in V3.
window.alshayaBazaarVoice.getbazaarVoiceSettings = getbazaarVoiceSettings;

function getUserBazaarVoiceSettings() {
  const settings = [];
  if (drupalSettings.userInfo) {
    settings.reviews = drupalSettings.userInfo;
  }
  return settings;
}

function fetchAPIData(apiUri, params, context = '') {
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

// @todo Find a better way to do this in V3.
window.alshayaBazaarVoice.fetchAPIData = fetchAPIData;

function postAPIData(apiUri, params, productId = undefined) {
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

function postAPIPhoto(apiUri, params) {
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

function getLanguageCode() {
  return drupalSettings.path.currentLanguage;
}

export async function getUserDetails(productId = undefined) {
  const settings = {};

  if (productId !== '' && typeof drupalSettings.bazaarvoiceUserDetails !== 'undefined') {
    settings.user = drupalSettings.bazaarvoiceUserDetails;
    settings.productReview = await window.alshayaBazaarVoice.getProductReviewForCurrrentUser(
      productId,
    );
  }

  return settings;
}

function doRequest(url) {
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

function postRequest(url, data) {
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

export {
  getLanguageCode,
  doRequest,
  postRequest,
  fetchAPIData,
  postAPIData,
  postAPIPhoto,
  getbazaarVoiceSettings,
  getUserBazaarVoiceSettings,
};
