import Axios from 'axios';
import dispatchCustomEvent from '../../../../../js/utilities/events';
import { callMagentoApiSynchronous } from '../../../../../js/utilities/requestHelper';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import logger from '../../../../../js/utilities/logger';

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
  return window.alshayaBazaarVoice.getbazaarVoiceSettings(productId);
}

function getUserBazaarVoiceSettings() {
  return window.alshayaBazaarVoice.getUserBazaarVoiceSettings();
}

async function fetchAPIData(apiUri, params, context = '') {
  const bazaarVoiceSettings = context === 'user'
    ? getUserBazaarVoiceSettings()
    : getbazaarVoiceSettings();
  const url = `${getBvUrl(bazaarVoiceSettings) + apiUri}?${getApiVersion(
    bazaarVoiceSettings,
  )}${getPassKey(bazaarVoiceSettings)}${getLocale(
    bazaarVoiceSettings,
  )}${params}`;

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
  const url = `${getBvUrl(bazaarVoiceSettings) + apiUri}?${getApiVersion(
    bazaarVoiceSettings,
  )}${getPassKey(bazaarVoiceSettings)}${getLocale(bazaarVoiceSettings)}`;

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
  const url = `${getBvUrl(bazaarVoiceSettings) + apiUri}?${getApiVersion(
    bazaarVoiceSettings,
  )}${getPassKey(bazaarVoiceSettings)}${getLocale(
    bazaarVoiceSettings,
  )}${params}`;

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

  if (
    productId !== ''
    && typeof drupalSettings.bazaarvoiceUserDetails !== 'undefined'
  ) {
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

/**
 * Get Bazaarvoice config from MDC.
 *
 * @returns {array|null}
 *   Bazaarvoice object or null.
 */
function getBazaarVoiceSettingsFromMdc() {
  if (Drupal.getItemFromLocalStorage('bazaarVoiceSettings')) {
    return Drupal.getItemFromLocalStorage('bazaarVoiceSettings');
  }

  const url = '/V1/bv/configs';

  const response = callMagentoApiSynchronous(url);
  if (hasValue(response.responseText)) {
    logger.notice('Error while trying to get bazaar voice common settings. Url: @url Message: @message', {
      '@url': url,
      '@message': response.responseText,
    });
    return null;
  }

  const data = response[0];
  Drupal.addItemInLocalStorage(
    'bazaarVoiceSettings',
    data,
    parseInt(drupalSettings.alshaya_bazaar_voice.bazaar_voice.bazaarvoice_settings_expiry, 10),
  );
  // Magento passes required config object inside an array.
  return data;
}

window.alshayaBazaarVoice.getBazaarVoiceSettingsFromCommerceBackend = getBazaarVoiceSettingsFromMdc;

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
