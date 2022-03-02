import Axios from 'axios';
import { getbazaarVoiceSettings } from './request';
import dispatchCustomEvent from '../../../../../js/utilities/events';
import { getStorageInfo, setStorageInfo } from '../storage';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../js/utilities/showRemoveFullScreenLoader';

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
 * @param {*} contentLocale
 *
 * @return
 *   Array of source and target lang.
 */
export function getTranslateLang(tranlateStatus, contentLocale) {
  const lang = {
    soruceLang: 'en',
    targetLang: 'ar',
  };

  if (contentLocale !== 'en') {
    lang.soruceLang = 'ar';
    lang.targetLang = 'en';
  }

  if (tranlateStatus) {
    lang.soruceLang = 'ar';
    lang.targetLang = 'en';
  }

  return lang;
}

/**
 * Replace special chars with encoded value.
 *
 * @param {*} str
 */
function encodeSpecialChars(str) {
  // Added encoded value for below to fix google translation issues.
  // Below were not supported by encodeURI().
  const symbols = {
    '@': '%40',
    '&amp;': '%26',
    '*': '%2A',
    '+': '%2B',
    '/': '%2F',
    '&lt;': '%3C',
    '&gt;': '%3E',
  };

  return str.replace(/([@*+/]|&(amp|lt|gt);)/g, (m) => symbols[m]);
}

/**
 * Store the reviews/comments data to local storage.
 *
 * @param {*} tranlateStatus
 * @param {*} id
 * @param {*} storeData
 * @param {*} contentType
 */
export function storeTranlatedData(tranlateStatus, id, storeData, contentType) {
  const data = {};
  Object.entries(storeData).forEach(([key, value]) => {
    data[key] = value;
  });

  if (contentType === 'comment') {
    setStorageInfo(data, `comment_${id}_${tranlateStatus}`);
  } else {
    setStorageInfo(data, `review_${id}_${tranlateStatus}`);
  }
}

/**
 * Render the translated/original reviews content.
 *
 * @param {*} reviewId
 * @param {*} tranlateStatus
 * @param {*} contentLocale
 * @param {*} contentType
 */
export function renderTranslatedReview(reviewId, tranlateStatus, contentLocale, contentType) {
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
  storeTranlatedData(true, reviewId, reviewData, contentType);

  const lang = getTranslateLang(tranlateStatus, contentLocale);

  let params = `&q=${encodeSpecialChars(encodeURI(reviewData.title))}`;
  params += `&q=${encodeURI(reviewData.date)}`;
  params += `&q=${encodeSpecialChars(encodeURI(reviewData.text))}`;

  showFullScreenLoader();
  // Get the translated contents for reviews.
  const request = getTranslations(params, lang.soruceLang, lang.targetLang);

  if (request instanceof Promise) {
    request
      .then((result) => {
        removeFullScreenLoader();
        if (result.status === 200) {
          Object.entries(result.data).forEach(([key, value]) => {
            if (key === 'data') {
              document.getElementById(`${reviewId}-review-title`).innerHTML = value.translations[0].translatedText;
              document.getElementById(`${reviewId}-review-date`).innerHTML = value.translations[1].translatedText;
              document.getElementById(`${reviewId}-review-text`).innerHTML = value.translations[2].translatedText;

              const translatedReviewData = {
                title: value.translations[0].translatedText,
                date: value.translations[1].translatedText,
                text: value.translations[2].translatedText,
              };
              // Keep review translations in local storage.
              storeTranlatedData(tranlateStatus, reviewId, translatedReviewData, contentType);
            }
          });
        }
        return null;
      })
      .catch((error) => error);
  }
}

/**
 * Render the translated/original reviews content.
 *
 * @param {*} reviewId
 * @param {*} tranlateStatus
 * @param {*} contentLocale
 * @param {*} contentType
 */
export function renderTranslatedComment(commentId, tranlateStatus, contentLocale, contentType) {
  const data = getStorageInfo(`comment_${commentId}_${tranlateStatus}`);
  if (data !== null) {
    document.getElementById(`${commentId}-comment-user-date`).innerHTML = data.date;
    document.getElementById(`${commentId}-comment-description-text`).innerHTML = data.text;

    return;
  }

  const commentData = {
    date: document.getElementById(`${commentId}-comment-user-date`).innerHTML,
    text: document.getElementById(`${commentId}-comment-description-text`).innerHTML,
  };

  // Keep original content in local storage.
  storeTranlatedData(true, commentId, commentData, contentType);

  const lang = getTranslateLang(tranlateStatus, contentLocale);

  let params = `&q=${encodeURI(commentData.date)}`;
  params += `&q=${encodeSpecialChars(encodeURI(commentData.text))}`;

  showFullScreenLoader();
  // Get the translated contents for reviews.
  const request = getTranslations(params, lang.soruceLang, lang.targetLang);

  if (request instanceof Promise) {
    request
      .then((result) => {
        removeFullScreenLoader();
        if (result.status === 200) {
          Object.entries(result.data).forEach(([key, value]) => {
            if (key === 'data') {
              document.getElementById(`${commentId}-comment-user-date`).innerHTML = value.translations[0].translatedText;
              document.getElementById(`${commentId}-comment-description-text`).innerHTML = value.translations[1].translatedText;

              const translatedCommentData = {
                date: value.translations[0].translatedText,
                text: value.translations[1].translatedText,
              };
              // Keep review translations in local storage.
              storeTranlatedData(tranlateStatus, commentId, translatedCommentData, contentType);
            }
          });
        }
        return null;
      })
      .catch((error) => error);
  }
}

export default {
  renderTranslatedReview,
  renderTranslatedComment,
};
