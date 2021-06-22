import Axios from 'axios';
import dispatchCustomEvent from '../../../../../js/utilities/events';

export function getLanguageCode() {
  return drupalSettings.path.currentLanguage;
}

export function getbazaarVoiceSettings(productId = undefined) {
  const settings = [];
  if (productId !== undefined) {
    settings.productid = productId;
    settings.reviews = drupalSettings.productInfo[productId].alshaya_bazaar_voice;
  } else {
    Object.entries(drupalSettings.productInfo).forEach(([key]) => {
      settings.productid = key;
      settings.reviews = drupalSettings.productInfo[key].alshaya_bazaar_voice;
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

export function getUserDetails(productId = undefined) {
  const settings = [];
  settings.user = drupalSettings.bazaarvoiceUserDetails;
  if (productId !== undefined) {
    settings.productReview = drupalSettings.productInfo[productId].productReview;
  } else {
    settings.productReview = drupalSettings.bazaarvoiceUserDetails.productReview;
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
};
