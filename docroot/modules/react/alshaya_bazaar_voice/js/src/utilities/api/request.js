import Axios from 'axios';

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
  let userDetails = [];
  if (productId !== undefined) {
    userDetails = drupalSettings.productInfo[productId].user_details.user;
  } else {
    userDetails = drupalSettings.bazaar_voice.user;
  }
  return userDetails;
}

export function doRequest(url) {
  return Axios.get(url)
    .then((response) => {
      const event = new CustomEvent('showMessage', {
        bubbles: true,
        detail: {
          data: response,
        },
      });
      document.dispatchEvent(event);
      return response;
    })
    .catch((error) => {
      const event = new CustomEvent('showMessage', {
        bubbles: true,
        detail: {
          data: error,
        },
      });
      document.dispatchEvent(event);
      return error;
    });
}

export function postRequest(url, data) {
  return Axios.post(url, data)
    .then((response) => {
      const event = new CustomEvent('showMessage', {
        bubbles: true,
        detail: {
          data: response,
        },
      });
      document.dispatchEvent(event);
      return response;
    })
    .catch((error) => {
      const event = new CustomEvent('showMessage', {
        bubbles: true,
        detail: {
          data: error,
        },
      });
      document.dispatchEvent(event);
      return error;
    });
}

export default {
  getLanguageCode,
  doRequest,
  postRequest,
};
