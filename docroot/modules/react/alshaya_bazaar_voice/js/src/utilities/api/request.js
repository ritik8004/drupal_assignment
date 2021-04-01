import Axios from 'axios';

export function getLanguageCode() {
  return drupalSettings.path.currentLanguage;
}

export function getbazaarVoiceSettings($context = 'pdp') {
  const settings = [];
  if ($context === 'pdp') {
    Object.entries(drupalSettings.productInfo).forEach(([key]) => {
      settings.productid = key;
      settings.reviews = drupalSettings.productInfo[key].alshaya_bazaar_voice;
    });
  }
  if ($context === 'user') {
    settings.reviews = drupalSettings.userInfo;
  }

  return settings;
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
