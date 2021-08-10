import Axios from 'axios';
import { getbazaarVoiceSettings, getLanguageCode } from './request';
import dispatchCustomEvent from '../../../../../js/utilities/events';
import { getStorageInfo, setStorageInfo } from '../storage';

/**
 * Translate reviews content using google translation api.
 *
 * @param {*} params
 * @param {*} fromLang
 * @param {*} toLang
 *
 * @return
 *   Response array with translated contents.
 */
export function getTranslations(params, fromLang, toLang) {
  const bazaarVoiceSettings = getbazaarVoiceSettings();
  const endpoint = bazaarVoiceSettings.reviews.bazaar_voice.google_api_endpoint;
  const apiKey = `key=${bazaarVoiceSettings.reviews.bazaar_voice.google_api_key}`;

  let url = `${endpoint}?${apiKey}`;
  url += params;
  url += `&source=${fromLang}`;
  url += `&target=${toLang}`;

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

/**
 * Get the srouce and target language based on tranlation status.
 *
 * @param {*} tranlateStatus
 *
 * @return
 *   Array of source and target lang.
 */
export function getTranslateLang(tranlateStatus) {
  const lang = {
    soruceLang: 'en',
    targetLang: 'ar',
  };

  if (getLanguageCode() !== 'en') {
    lang.targetLang = 'en';
    lang.soruceLang = 'ar';
  }

  if (tranlateStatus) {
    lang.soruceLang = 'ar';
    lang.targetLang = 'en';
  }

  return lang;
}

/**
 * Store the review data in local storage.
 *
 * @param {*} tranlateStatus
 * @param {*} reviewId
 * @param {*} reviewData
 */
export function setReviewData(tranlateStatus, reviewId, reviewData) {
  const data = {};
  Object.entries(reviewData).forEach(([key, value]) => {
    data[key] = value;
  });

  setStorageInfo(data, `review_${reviewId}_${tranlateStatus}`);
}

/**
 * Render the translated/original reviews content.
 *
 * @param {*} reviewId
 * @param {*} tranlateStatus
 */
export function renderTranslatedContent(reviewId, tranlateStatus) {
  const data = getStorageInfo(`review_${reviewId}_${tranlateStatus}`);
  if (data !== null) {
    document.getElementById(`${reviewId}-review-title`).innerHTML = data.title;
    document.getElementById(`${reviewId}-review-date`).innerHTML = data.date;
    document.getElementById(`${reviewId}-review-text`).innerHTML = data.text;

    return;
  }

  const reviewData = {
    title: document.getElementById(`${reviewId}-review-title`).innerHTML,
    date: document.getElementById(`${reviewId}-review-date`).innerHTML,
    text: document.getElementById(`${reviewId}-review-text`).innerHTML,
  };

  // Keep original content in local storage.
  setReviewData(true, reviewId, reviewData);

  const lang = getTranslateLang(tranlateStatus);

  let params = `&q=${encodeURI(reviewData.title)}`;
  params += `&q=${encodeURI(reviewData.date)}`;
  params += `&q=${encodeURI(reviewData.text)}`;

  // Get the translated contents for reviews.
  const request = getTranslations(params, lang.soruceLang, lang.targetLang);

  if (request instanceof Promise) {
    request
      .then((result) => {
        if (result.status === 200) {
          Object.entries(result.data).forEach(([key, value]) => {
            if (key === 'data') {
              document.getElementById(`${reviewId}-review-title`).innerHTML = value.translations[0].translatedText;
              document.getElementById(`${reviewId}-review-date`).innerHTML = value.translations[1].translatedText;
              document.getElementById(`${reviewId}-review-text`).innerHTML = value.translations[2].translatedText;
            }

            const translatedReviewData = {
              title: value.translations[0].translatedText,
              date: value.translations[1].translatedText,
              text: value.translations[2].translatedText,
            };
            // Keep review translations in local storage.
            setReviewData(tranlateStatus, reviewId, translatedReviewData);
          });
        }
        return null;
      })
      .catch((error) => error);
  }
}

export default {
  renderTranslatedContent,
};
